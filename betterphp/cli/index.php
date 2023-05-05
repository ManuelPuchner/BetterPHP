<?php

use betterphp\cmd\Color;
use betterphp\utils\Route;

require_once dirname(__DIR__) . '/utils/Route.php';
require_once dirname(__DIR__) . '/cli/Color.php';


$CONTROLLER_DIR = dirname(__DIR__) . '/src/controller';
$CONTROLLER_DEST_DIR = dirname(__DIR__) . '/dist/controller';

$MODEL_DIR = dirname(__DIR__) . '/src/model';
$MODEL_DEST_DIR = dirname(__DIR__) . '/dist/model';

$UTILS_DIR = dirname(__DIR__) . '/utils';
$UTILS_DEST_DIR = dirname(__DIR__) . '/dist/utils';
$ROOT = dirname(__DIR__) . '/../';

function deleteAll(string $dir): void
{
    foreach(glob($dir . '/*') as $file) {
        if(is_dir($file))
            deleteAll($file);
        else
            unlink($file);
    }
    @rmdir($dir);
}




echo Color::get('ROOT: ', Color::GREEN);
echo $ROOT . PHP_EOL;

$baseApi = $ROOT . '/dist/api';

echo Color::get('BASE API DIRECTORY: ', Color::GREEN);
echo $baseApi . PHP_EOL;

if (file_exists($ROOT . '/dist')) {
    echo Color::get('BASE API DIRECTORY ALREADY EXISTS, REMOVING CONTENTS OF API DIRECTORY', Color::RED) . PHP_EOL;
    deleteAll($ROOT . '/dist/api');
    deleteAll($ROOT . '/dist/controller');
    deleteAll($ROOT . '/dist/model');
}

@mkdir($baseApi, 0777, true);

// get files in service folder
$fileNames = array_filter(scandir($ROOT . '/src/service'), function ($fileName) {
    return $fileName !== '.' && $fileName !== '..';
});

echo Color::get('SERVICE FILES: ', Color::GREEN) . PHP_EOL;
foreach ($fileNames as $fileName) {
    echo "\t" . $fileName . PHP_EOL;
}

// execute service files
foreach ($fileNames as $fileName) {
    $filePath = $ROOT . '/src/service/' . $fileName;
    require_once $filePath;
}

$routes = Route::getRoutes();



echo Color::get('ROUTES: ', Color::GREEN) . PHP_EOL;
foreach ($routes as $route) {
    echo "\t" . $route['method'] . ' ' . $route['uri'] . PHP_EOL;
}

require_once dirname(__DIR__) . '/cli/generateEndpoints.php';




// generate htaccess rewrite rules
require_once dirname(__DIR__) . '/cli/generateHtaccess.php';


// copy files
require_once dirname(__DIR__) . '/cli/copyFiles.php';

// copy env
require_once dirname(__DIR__) . '/cli/copyEnv.php';

