<?php


use betterphp\cli\Color;
use betterphp\cli\RouteType;
use betterphp\Orm;
use betterphp\utils\attributes\Controller;
use betterphp\utils\attributes\QueryParam;
use betterphp\utils\attributes\Route;
use betterphp\utils\attributes\Service;


require_once dirname(__DIR__) . '/Orm/Entity.php';
require_once dirname(__DIR__) . '/Orm/Column.php';
require_once dirname(__DIR__) . '/Orm/PrimaryKey.php';
require_once dirname(__DIR__) . '/Orm/AutoIncrement.php';
require_once dirname(__DIR__) . '/utils/attributes/Controller.php';
require_once dirname(__DIR__) . '/utils/attributes/Service.php';
require_once dirname(__DIR__) . '/utils/attributes/GET.php';
require_once dirname(__DIR__) . '/utils/attributes/POST.php';
require_once dirname(__DIR__) . '/utils/attributes/DELETE.php';
require_once dirname(__DIR__) . '/utils/attributes/PUT.php';
require_once dirname(__DIR__) . '/utils/attributes/Getter.php';
require_once dirname(__DIR__) . '/utils/attributes/Setter.php';
require_once dirname(__DIR__) . '/utils/attributes/Route.php';
require_once dirname(__DIR__) . '/cli/Color.php';
require_once dirname(__DIR__) . '/cli/RouteType.php';
require_once dirname(__DIR__) . '/utils/attributes/PathParam.php';
require_once dirname(__DIR__) . '/utils/attributes/QueryParam.php';
require_once dirname(__DIR__) . '/utils/attributes/BodyParam.php';


require_once dirname(__DIR__) . '/cli/htaccess.php';
require_once dirname(__DIR__) . '/cli/utilfunctions.php';
require_once dirname(__DIR__) . '/cli/generateQueryParamRoute.php';
require_once dirname(__DIR__) . '/cli/generateNormalRoute.php';
require_once dirname(__DIR__) . '/cli/generatePathParamRoute.php';
require_once dirname(__DIR__) . '/cli/generateBodyParamRoute.php';



$SRC_DIR = dirname(__DIR__) . '/../src';
$API_DIR = dirname(__DIR__) . '/../dist/api';

deleteDirRecursively($API_DIR);


@mkdir(dirname(__DIR__). '/../dist/', 0777, true);

$htaccessFile = fopen( dirname(__DIR__). '/../dist/.htaccess', 'w');
fwrite($htaccessFile, '
' . PHP_EOL . "RewriteEngine On" . PHP_EOL);
fclose($htaccessFile);



# scan src dir for entities
$allSrcFiles = scanAllDir($SRC_DIR);


$endpoints = [];
foreach ($allSrcFiles as $srcFile) {
    require_once $SRC_DIR . "/" . $srcFile;
    try {
        $className = str_replace('/', '\\', $srcFile);
        $className = str_replace('.php', '', $className);
        $reflection = new ReflectionClass($className);

        $attr = $reflection->getAttributes();

        if (count($attr) === 0) continue;

        if (getClassAttribute($reflection, Orm\Entity::class)) {
            handleModel($reflection, $SRC_DIR . '/' . $srcFile);
        } else if (getClassAttribute($reflection, Controller::class)) {
            handleController($reflection, $SRC_DIR . '/' . $srcFile);
        } else if (getClassAttribute($reflection, Service::class)) {
            /*
             * endpoints: [
             *  "/api/endpoint1": {
             *      "GET": {
             *        "params": []
             *      }
             *  }
             * ]
             */

            handleService($reflection, $endpoints);
        }


    } catch (ReflectionException $e) {
        echo $e->getMessage() . PHP_EOL;
    } catch (Exception $e) {
    }

}

// generate swagger
$swaggerFile = fopen(dirname(__DIR__) . '/../dist/swagger.json', 'w');

$endpoints = json_encode($endpoints, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
fwrite($swaggerFile, '
{
  "openapi": "3.0.0",
  "info": {
    "title": "BetterPHP API",
    "description": "BetterPHP API",
    "version": "1.0.0"
  },
  "servers": [
    {
      "url": "http://localhost:8080/api"
    }
  ],
  "paths": ' . $endpoints .
    '}' . PHP_EOL);


function handleModel(ReflectionClass $reflection, string $path): void
{
    echo Color::get("Handling model: ", Color::CYAN) . $reflection->getName() . PHP_EOL;

    @mkdir(dirname(__DIR__) . '/../dist/model/', 0777, true);
    @copy($path, dirname(__DIR__) . '/../dist/model/' . $reflection->getShortName() . '.php');
}

function handleController(ReflectionClass $reflection, string $path): void
{
    echo Color::get("Handling controller: ", Color::CYAN) . $reflection->getName() . PHP_EOL;

    @mkdir(dirname(__DIR__) . '/../dist/controller/', 0777, true);
    @copy($path, dirname(__DIR__) . '/../dist/controller/' . $reflection->getShortName() . '.php');
}


/**
 * @throws ReflectionException
 * @throws Exception
 */
function handleService(ReflectionClass $reflection, array &$endpoints): void
{
    echo Color::get("Handling service: ", Color::CYAN) . $reflection->getName() . PHP_EOL;

    $methods = $reflection->getMethods();

    echo "\tGenerating routes:" . PHP_EOL;

    foreach ($methods as $method) {
        $route = getMethodAttribute($method, Route::class);
        if($route) {
            $path = $route->newInstance()->getPath();

            $httpMethod = getHttpMethod($method);



            generateRoute($path, $httpMethod, $method, $endpoints);
        }
    }
}


/**
 * @throws Exception
 */
function generateRoute($path, $httpMethod, ReflectionMethod $reflection, &$endpoints): void
{
    $API_DIR = dirname(__DIR__) . '/../dist/api';

    echo "\t\t" . Color::get($httpMethod, Color::GREEN) . ' ' . $path . PHP_EOL;
    $route_dir =  $API_DIR . $path;

    @mkdir($route_dir, 0777, true);

    $route_type = getRouteType($reflection);

    echo "\t\t\t" . Color::get("Route type: ", Color::CYAN) . $route_type->name . PHP_EOL;

    switch ($route_type) {
        case RouteType::NORMAL:
            $endpoints[$path][strtolower($httpMethod)] = [
                "responses" => [
                    "200" => [
                        "description" => "OK"
                    ]
                ]
            ];
            generateNormalRoute($route_dir, $reflection, $httpMethod);
            break;
        case RouteType::PATH_PARAM:
            generatePathParamRoute($route_dir, $reflection, $httpMethod, $endpoints, $path);
            break;

        case RouteType::QUERY_PARAM:
            $params = $reflection->getParameters();
            array_filter($params, function ($param) {
                $attributes = $param->getAttributes();
                foreach ($attributes as $attribute) {
                    $attributeClass = $attribute->newInstance();
                    if ($attributeClass::class === QueryParam::class) {
                        return true;
                    }
                }
                return false;
            });
            $paramNames = [];
            foreach ($params as $param) {
                $paramNames[] = $param->getName();
            }
            $params = array_map(function ($paramName) {
                return [
                    "name" => $paramName,
                    "in" => "query"
                ];
            }, $paramNames);
            $endpoints[$path][strtolower($httpMethod)] = [
                "parameters" => $params,
                "responses" => [
                    "200" => [
                        "description" => "OK"
                    ]
                ]
            ];
            generateQueryParamRoute($route_dir, $reflection, $httpMethod, $paramNames);
            break;
        case RouteType::BODY_PARAM:
            $endpoints[$path][strtolower($httpMethod)] = [
                "parameters" => [
                    [
                        "name" => "body",
                        "in" => "body"
                    ]
                ],
                "responses" => [
                    "200" => [
                        "description" => "OK"
                    ]
                ]
            ];
            generateBodyParamRoute($route_dir, $reflection, $httpMethod);
            break;
    }
}

require_once dirname(__DIR__) . '/cli/copyEnvFile.php';