<?php
function addToHtaccess(ReflectionMethod $reflection, string $uri): void
{
    $htaccessFile = fopen( dirname(__DIR__). '/../dist/.htaccess', 'a');
    $params = getCallableParams($reflection);
    $htaccessContentPart = "RewriteRule ^";
    $tmpuri = preg_replace('/{(\w+)}/', '(\w+)', substr('/api' . $uri, 1));
    $htaccessContentPart .= $tmpuri;
    $htaccessContentPart .= "$ ";
    $htaccessContentPart .= substr( '/api/' . $uri, 0) . "/index.php?";
    for ($i = 0; $i < sizeof($params); $i++) {
        $htaccessContentPart .= $params[$i] . "=\$" . ($i + 1) . "&";
    }
    $htaccessContentPart = rtrim($htaccessContentPart, '&');
    $htaccessContentPart .= ' [L]' . PHP_EOL;

    fwrite($htaccessFile, $htaccessContentPart);
    fclose($htaccessFile);
}