<?php

use betterphp\utils\PathParam;
use betterphp\utils\Route;

/**
 * @throws ReflectionException
 */
function generatePathParamRoute(string $filePath, ReflectionMethod $reflection, string $httpMethod): void
{
    $pathParams = [];
    $params = $reflection->getParameters();
    foreach ($params as $param) {
        $paramAttributes = $param->getAttributes();
        foreach ($paramAttributes as $paramAttribute) {
            $paramAttributeClass = $paramAttribute->newInstance();
            if ($paramAttributeClass::class === PathParam::class) {
                $pathParams[] = $param->getName();
            }
        }
    }
    generateQueryParamRoute($filePath, $reflection, $httpMethod, $pathParams);

    $path = getMethodAttribute($reflection, Route::class)->newInstance()->getPath();
    addToHtaccess($reflection, $path);
}
