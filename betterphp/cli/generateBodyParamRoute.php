<?php

use betterphp\utils\attributes\BodyParam;

function generateBodyParamRoute($filePath, ReflectionMethod $reflection, string $httpMethod): void
{
    $_oldContent = @file_get_contents($filePath . '/' . 'index.php');

    $file = fopen($filePath . '/' . 'index.php', 'w');

    $httpMethod = getHttpMethod($reflection);


    if ($_oldContent) {
        $useStatements = getUseOldContent($_oldContent);
        $oldContent = getOldContentWithoutRequiresAndUses($_oldContent);
    }

    $pathOfClassOfMethod = dirname(__DIR__) . '/../src/' . str_replace('\\', '/', $reflection->getDeclaringClass()->getName()) . '.php';
    $_oldContentSrcFile = @file_get_contents($pathOfClassOfMethod);
    $_srcUseImports = getUseOldContent($_oldContentSrcFile);
    $useStatements = array_merge($useStatements ?? [], $_srcUseImports);

    $content = '<?php
session_start();
use betterphp\utils\Response;
use betterphp\utils\ApiException;
use betterphp\utils\Autoloader;' . PHP_EOL;

    foreach ($useStatements as $useStatement) {
        $content .= $useStatement . PHP_EOL;
    }


    $content .= '
require_once "/var/www/betterphp/utils/Autoloader.php";
Autoloader::load();' . PHP_EOL;

    if (isset($requireStatements)) {
        foreach ($requireStatements as $requireStatement) {
            $content .= $requireStatement . PHP_EOL;
        }
    }

    $params = $reflection->getParameters();

    $bodyParamVarName = null;

    foreach ($params as $param) {
        $paramAttributes = $param->getAttributes();
        foreach ($paramAttributes as $paramAttribute) {
            $paramAttributeClass = $paramAttribute->newInstance();
            if ($paramAttributeClass::class === BodyParam::class) {
                $bodyParamVarName = $param->getName();
                break 2;
            }
        }
    }

    $content .= '
if($_SERVER[\'REQUEST_METHOD\'] === \'' . $httpMethod . '\') {' . PHP_EOL;

    if(isProtectedRoute($reflection)) {
        $content .= '
    if(!isset($_SESSION[\'user\'])) {
        Response::error(HttpErrorCodes::HTTP_UNAUTHORIZED, "You are not logged in")->send();
    }
' . PHP_EOL;
    }

    $content .= "\t$" . $bodyParamVarName .' = json_decode(file_get_contents(\'php://input\'), true);' . PHP_EOL;

    $content .= "\t" . '$callback = ' . methodToString($reflection) . PHP_EOL;

    $content .= "\t" . '$response = $callback(';

    foreach ($params as $param) {
        $content .= '$' . $param->getName() . ', ';
    }
    $content = rtrim($content, ', ');

    $content .=');';

    $content .= '
     $response->send();
}

';

    if (isset($oldContent)) {
        $content .= $oldContent;
    }

    $content = removeDuplicateUses($content);

    fwrite($file, $content);
    fclose($file);
}