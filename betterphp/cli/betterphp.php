<?php


use betterphp\cli\Color;
use betterphp\utils\Controller;
use betterphp\Orm;
use betterphp\utils\GET;
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
    }

}


function handleModel(ReflectionClass $reflection) {

}

function handleController(ReflectionClass $reflection) {

}

/**
 * @throws ReflectionException
 */
function handleService(ReflectionClass $reflection) {
    echo "Handling service: " . $reflection->getName() . PHP_EOL;

    $methods = $reflection->getMethods();

    echo "Generating routes:" . PHP_EOL;

    foreach ($methods as $method) {
        $route = getMethodAttribute($method, Route::class);
        if($route) {
            $path = $route->newInstance()->getPath();
            $httpMethod = getHttpMethod($method);

            generateRoute($path, $httpMethod, $method);
        }
    }
}

function generateRoute($path, $httpMethod, ReflectionMethod $reflection)
{

    echo "\t" . Color::get($httpMethod, Color::GREEN) . ' ' . $path . PHP_EOL;
}

function getHttpMethod(ReflectionMethod $reflection): string {
    $attributes = $reflection->getAttributes();
    foreach ($attributes as $attribute) {
        $attributeClass = $attribute->newInstance();
        if ($attributeClass::class === GET::class) {
            return 'GET';
        } else if ($attributeClass::class === POST::class) {
            return 'POST';
        }
    }
    return '';

}


/**
 * @throws ReflectionException
 */
function getClassAttribute(ReflectionClass $reflection, $attributeClass): ReflectionAttribute|false {
    $attributes = $reflection->getAttributes();
    $classToFind = new ReflectionClass($attributeClass);
    foreach ($attributes as $attribute) {
        $attributeClass = $attribute->newInstance();
        if ($attributeClass::class === $classToFind->getName()) {
            return $attribute;
        }
    }
    return false;
}

/**
 * @throws ReflectionException
 */
function getMethodAttribute(ReflectionMethod $reflection, string $attributeClass): ReflectionAttribute|false {
    $attributes = $reflection->getAttributes();
    $classToFind = new ReflectionClass($attributeClass);
    foreach ($attributes as $attribute) {
        $attributeClass = $attribute->newInstance();
        if ($attributeClass::class === $classToFind->getName()) {
            return $attribute;
        }
    }
    return false;
}