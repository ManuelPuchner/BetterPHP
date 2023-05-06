<?php

use betterphp\utils\Route;

$ROOT = dirname(__DIR__);
$baseApi = $ROOT . '/../dist/api';

function isDynamicRoute(string $uri) : bool {
    return preg_match('/{(\w+)}/', $uri) === 1;
}

$htaccessFile = fopen($baseApi . '/.htaccess', 'w');

$htaccessContent = "RewriteEngine On" . PHP_EOL;

$routes = Route::getRoutes();

foreach ($routes as $route) {
    $uri = $route['uri'];
    if(isDynamicRoute($uri)) {
        $params = getCallableParams($route['callback']);
        $htaccessContentPart = "RewriteRule ^";
        $uri = preg_replace('/{(\w+)}/', '(\w+)', substr($uri, 1));
        $htaccessContentPart .= $uri;
        $htaccessContentPart .= "$ ";
        $htaccessContentPart .= substr($route['uri'], 1) . "/index.generated.php?";
        for ($i = 0; $i < sizeof($params); $i++) {
            $htaccessContentPart .= $params[$i] . "=\$" . ($i + 1) . "&";
        }
        $htaccessContentPart = rtrim($htaccessContentPart, '&');
        $htaccessContentPart .= ' [L]' . PHP_EOL;

        $htaccessContent .= $htaccessContentPart;
    }

    echo PHP_EOL;
}

fwrite($htaccessFile, $htaccessContent);
fclose($htaccessFile);