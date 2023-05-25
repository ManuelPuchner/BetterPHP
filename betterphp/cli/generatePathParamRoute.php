<?php

use betterphp\utils\attributes\PathParam;
use betterphp\utils\attributes\Route;

/**
 * @throws ReflectionException
 */
function generatePathParamRoute(string $filePath, ReflectionMethod $reflection, string $httpMethod, &$endpoints, string $path): void
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

    $pathParams = array_map(function ($paramName) {
        return [
            "name" => $paramName,
            "in" => "path"
        ];
    }, $pathParams);
    $endpoints[$path][strtolower($httpMethod)] = [
        "parameters" => $pathParams,
        "responses" => [
            "200" => [
                "description" => "OK"
            ]
        ]
    ];


    $path = getMethodAttribute($reflection, Route::class)->newInstance()->getPath();
    addToHtaccess($reflection, $path);
}
