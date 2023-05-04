<?php

use betterphp\cmd\Color;

$CONTROLLER_DIR = dirname(__DIR__) . '/../src/controller';
$CONTROLLER_DEST_DIR = dirname(__DIR__) . '/../dist/controller';

$MODEL_DIR = dirname(__DIR__) . '/../src/model';
$MODEL_DEST_DIR = dirname(__DIR__) . '/../dist/model';

// copy over controller and model files

echo Color::get('Copying over controller and model files', Color::GREEN) . PHP_EOL;

copyFiles($CONTROLLER_DIR, $CONTROLLER_DEST_DIR);

copyFiles($MODEL_DIR, $MODEL_DEST_DIR);


function copyFiles($from, $to): void
{
    $files = scandir($from);
    $files = array_diff($files, ['.', '..']);

    foreach ($files as $file) {
        echo Color::get("\t" . 'Copying over ' . $file, Color::GREEN) . PHP_EOL;

        $copyFrom = $from . '/' . $file;
        $copyTo = $to . '/' . $file;

        @mkdir($to, 0777, true);

        copy($copyFrom, $copyTo);
    }
}
