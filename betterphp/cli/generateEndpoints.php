<?php

use betterphp\utils\Route;

$routes = Route::getRoutes();
$ROOT = dirname(__DIR__);
$baseApi = $ROOT . '/../dist/api';

foreach ($routes as $route) {
    $uri = $route['uri'];

    /** @var callable $callback */
    $callback = $route['callback'];
    $method = $route['method'];


    // remove last part of uri
    $uri = explode('/', $uri);

    $filePath = $baseApi . implode('/', $uri) . '/' . 'index.php';


    if(!file_exists($filePath)) {
        @mkdir($baseApi . implode('/', $uri), 0777, true);
    }

    $params = getCallableParams($callback);

    if(getParamsCountFromRequestURI($route['uri']) > 0) {
        // generate .generated.php

        $generatedFilePath = $baseApi . implode('/', $uri) . '/' . 'index.generated.php';

        $oldContent = @file_get_contents($generatedFilePath);

        if($oldContent !== false) {
            // remove <?php tag
            $oldContent = str_replace("<?php", "", $oldContent);
            $requires = '';
        } else {
            $oldContent = '';
            $requires = getRequires();
        }

        $generateFile = fopen($generatedFilePath, 'w');

        $content = "<?php\n\n";
        $content .= $oldContent;
        $content .= PHP_EOL;
        $content .= $requires;
        $content .= PHP_EOL;
        $content .= "if(\$_SERVER['REQUEST_METHOD'] === '" . $method . "') {" . PHP_EOL;
        $uriString = $route['uri'];
        foreach ($params as $param) {
            $content .= '$' . $param . " = \$_GET['" . $param . "'];" . PHP_EOL;
        }
        $content .= '$callback' . "=  " . callableToString($callback) . PHP_EOL;
        $content .= PHP_EOL;
        $content .= "/** @var Response \$response */" . PHP_EOL;
        $content .= '$response' . "=\$callback(";
        foreach ($params as $param) {
            $content .= '$' . $param . ', ';
        }
        $content = rtrim($content, ', ');
        $content .= ");" . PHP_EOL;
        $content .= "\$response->send();";
        $content .= PHP_EOL;
        $content .= "}";

        fwrite($generateFile, $content);

    } else {

        $oldContent = @file_get_contents($filePath);

        if($oldContent !== false) {
            // remove <?php tag
            $oldContent = str_replace("<?php", "", $oldContent);
            $requires = '';
        } else {
            $oldContent = '';
            $requires = getRequires();
        }


        $file = fopen($filePath, 'w');

        $content = "<?php\n\n";
        $content .= $oldContent;
        $content .= PHP_EOL;
        $content .= $requires;
        $content .= PHP_EOL;
        $content .= "if(\$_SERVER['REQUEST_METHOD'] === '" . $method . "') {" . PHP_EOL;
        if($method === 'POST') {
            $content .= "\$json=file_get_contents('php://input');" . PHP_EOL;
            $content .= "\$body=json_decode(\$json, true);" . PHP_EOL;
        }
        $uriString = $route['uri'];
        $content .= '$callback' . "=  " . callableToString($callback) . PHP_EOL;
        $content .= PHP_EOL;
        $content .= "/** @var Response \$response */" . PHP_EOL;
        if($method === 'POST') {
            $content .= '$response' . "=\$callback(\$body);" . PHP_EOL;
        } else {
            $content .= '$response' . "=\$callback();" . PHP_EOL;
        }
        $content .= "\$response->send();";
        $content .= PHP_EOL;
        $content .= "}";
        fwrite($file, $content);
    }
}

function callableToString(callable $callable) : string {
    try {
        $reflection = new ReflectionFunction($callable);
        $startLine = $reflection->getStartLine();
        $endLine = $reflection->getEndLine();
        $length = $endLine - $startLine - 1;
        $source = file($reflection->getFileName());
        $body = implode("", array_slice($source, $startLine, $length));
        $methodSignature = '(';
        $parameters = $reflection->getParameters();
        foreach ($parameters as $parameter) {
            $methodSignature .= '$' . $parameter->getName();
            if ($parameter->isDefaultValueAvailable()) {
                $methodSignature .= ' = ' . $parameter->getDefaultValue();
            }
            $methodSignature .= ', ';
        }
        $methodSignature = rtrim($methodSignature, ', ') . ')';
        return 'function ' . $methodSignature . ' {' . $body . '};';
    } catch (ReflectionException $e) {
        return '';
    }
}

function getCallableParams(callable $callable) : array {
    try {
        $reflection = new ReflectionFunction($callable);
        $parameters = $reflection->getParameters();
        $params = [];
        foreach ($parameters as $parameter) {
            $params[] = $parameter->getName();
        }
        return $params;
    } catch (ReflectionException $e) {
        return [];
    }
}

function getParamsCountFromRequestURI(string $uri) : int {
    $uri = explode('/', $uri);
    $count = 0;
    foreach ($uri as $part) {
        if(strpos($part, '{') !== false) {
            $count++;
        }
    }
    return $count;
}

function getRequires(): string
{
    return "
use betterphp\utils\Response;
use controller\CurrencyController;
use betterphp\utils\DBConnection;
use model\Currency;

require_once '" . dirname(__DIR__ ) . "/../dist/controller/CurrencyController.php';
require_once '" . dirname(__DIR__) . "/utils/Response.php';
require_once '" . dirname(__DIR__) . "/utils/DBConnection.php';
require_once '" . dirname(__DIR__) . "/../dist/model/Currency.php';

    " . PHP_EOL;
}