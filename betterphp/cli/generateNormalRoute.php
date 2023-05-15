<?php



function generateNormalRoute(string $filePath, ReflectionMethod $reflection, string $httpMethod): void
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
use betterphp\utils\Response;
use betterphp\utils\ApiException;
use betterphp\utils\Autoloader;' . PHP_EOL;

        foreach ($useStatements as $useStatement) {
            $content .= $useStatement . PHP_EOL;
        }

    $content .= '
require_once "/var/www/betterphp/utils/Autoloader.php";' . PHP_EOL;

    $content .= 'Autoloader::load();' . PHP_EOL;

    $content .= '

if($_SERVER[\'REQUEST_METHOD\'] === \'' . $httpMethod . '\') {
    $callback = ' . methodToString($reflection) . '
    
    $response = $callback();
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