<?php


use betterphp\cli\Color;
use betterphp\cli\RouteType;
use betterphp\utils\BodyParam;
use betterphp\utils\Controller;
use betterphp\Orm;
use betterphp\utils\GET;
use betterphp\utils\PathParam;
use betterphp\utils\QueryParam;
use betterphp\utils\POST;
use betterphp\utils\Route;
use betterphp\utils\Service;
use betterphp\utils\Getter;
use betterphp\utils\Setter;


require_once dirname(__DIR__) . '/Orm/Entity.php';
require_once dirname(__DIR__) . '/Orm/Column.php';
require_once dirname(__DIR__) . '/Orm/PrimaryKey.php';
require_once dirname(__DIR__) . '/Orm/AutoIncrement.php';
require_once dirname(__DIR__) . '/utils/Inject.php';
require_once dirname(__DIR__) . '/utils/Controller.php';
require_once dirname(__DIR__) . '/utils/Service.php';
require_once dirname(__DIR__) . '/utils/GET.php';
require_once dirname(__DIR__) . '/utils/POST.php';
require_once dirname(__DIR__) . '/utils/Getter.php';
require_once dirname(__DIR__) . '/utils/Setter.php';
require_once dirname(__DIR__) . '/utils/Route.php';
require_once dirname(__DIR__) . '/cli/Color.php';
require_once dirname(__DIR__) . '/cli/RouteType.php';
require_once dirname(__DIR__) . '/utils/PathParam.php';
require_once dirname(__DIR__) . '/utils/QueryParam.php';
require_once dirname(__DIR__) . '/utils/BodyParam.php';


require_once dirname(__DIR__) . '/cli/htaccess.php';
require_once dirname(__DIR__) . '/cli/utilfunctions.php';
require_once dirname(__DIR__) . '/cli/generateQueryParamRoute.php';
require_once dirname(__DIR__) . '/cli/generateNormalRoute.php';
require_once dirname(__DIR__) . '/cli/generatePathParamRoute.php';
require_once dirname(__DIR__) . '/cli/generateBodyParamRoute.php';

function deleteDirRecursively(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    foreach (scandir($dir) as $filename) {
        if ($filename[0] === '.') continue;
        $filePath = $dir . '/' . $filename;
        if (is_dir($filePath)) {
            deleteDirRecursively($filePath);
        } else {
            unlink($filePath);
        }
    }

    rmdir($dir);
}

function scanAllDir($dir): array
{
    $result = [];
    foreach(scandir($dir) as $filename) {
        if ($filename[0] === '.') continue;
        $filePath = $dir . '/' . $filename;
        if (is_dir($filePath)) {
            foreach (scanAllDir($filePath) as $childFilename) {
                $result[] = $filename . '/' . $childFilename;
            }
        } else {
            $result[] = $filename;
        }
    }
    return $result;
}

$SRC_DIR = dirname(__DIR__) . '/../src';
$API_DIR = dirname(__DIR__) . '/../dist/api';

deleteDirRecursively($API_DIR);


@mkdir(dirname(__DIR__). '/../dist/', 0777, true);

$htaccessFile = fopen( dirname(__DIR__). '/../dist/.htaccess', 'w');
fwrite($htaccessFile, "RewriteEngine On" . PHP_EOL);
fclose($htaccessFile);


# scan src dir for entities
$allSrcFiles = scanAllDir($SRC_DIR);

foreach ($allSrcFiles as $srcFile) {
    require_once $SRC_DIR . "/" . $srcFile;
    try {
        $className = str_replace('/', '\\', $srcFile);
        $className = str_replace('.php', '', $className);
        $reflection = new ReflectionClass($className);

        $attr = $reflection->getAttributes();

        if (count($attr) === 0) continue;

        if (getClassAttribute($reflection, Orm\Entity::class)) {
            handleModel($reflection);
        } else if (getClassAttribute($reflection, Controller::class)) {
            handleController($reflection);
        } else if (getClassAttribute($reflection, Service::class)) {
            handleService($reflection);
        }


    } catch (ReflectionException $e) {
        echo $e->getMessage() . PHP_EOL;
    } catch (Exception $e) {
    }

}


function handleModel(ReflectionClass $reflection): void
{
    echo Color::get("Handling model: ", Color::CYAN) . $reflection->getName() . PHP_EOL;
}

function handleController(ReflectionClass $reflection): void
{
    echo Color::get("Handling controller: ", Color::CYAN) . $reflection->getName() . PHP_EOL;
}

/**
 * @throws ReflectionException
 * @throws Exception
 */
function handleService(ReflectionClass $reflection): void
{
    echo Color::get("Handling service: ", Color::CYAN) . $reflection->getName() . PHP_EOL;

    $methods = $reflection->getMethods();

    echo "\tGenerating routes:" . PHP_EOL;

    foreach ($methods as $method) {
        $route = getMethodAttribute($method, Route::class);
        if($route) {
            $path = $route->newInstance()->getPath();
            $httpMethod = getHttpMethod($method);

            generateRoute($path, $httpMethod, $method);
        }
    }
}


/**
 * @throws Exception
 */
function generateRoute($path, $httpMethod, ReflectionMethod $reflection): void
{
    $API_DIR = dirname(__DIR__) . '/../dist/api';

    echo "\t\t" . Color::get($httpMethod, Color::GREEN) . ' ' . $path . PHP_EOL;
    $route_dir =  $API_DIR . $path;

    @mkdir($route_dir, 0777, true);

    $route_type = getRouteType($reflection);

    echo "\t\t\t" . Color::get("Route type: ", Color::CYAN) . $route_type->name . PHP_EOL;

    switch ($route_type) {
        case RouteType::NORMAL:
            generateNormalRoute($route_dir, $reflection, $httpMethod);
            break;
        case RouteType::PATH_PARAM:
            generatePathParamRoute($route_dir, $reflection, $httpMethod);
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
            generateQueryParamRoute($route_dir, $reflection, $httpMethod, $paramNames);
            break;
        case RouteType::BODY_PARAM:
            generateBodyParamRoute($route_dir, $reflection, $httpMethod);
            break;
    }
}