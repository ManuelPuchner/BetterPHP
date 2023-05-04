<?php

use betterphp\cmd\Color;
use betterphp\utils\Entity;

require_once __DIR__ . '/Color.php';

$MODEL_DIR = dirname(__DIR__) . '/../src/model';

$CREATE_SQL_FILE = dirname(__DIR__) . '/../dist/sql/create.sql';

// generate tables
$models = scandir($MODEL_DIR);
$models = array_diff($models, ['.', '..']);

echo Color::get('Generating tables', Color::GREEN) . PHP_EOL;

// get class reflection of models
foreach ($models as $model) {
    $modelPath = $MODEL_DIR . '/' . $model;

    if (is_dir($modelPath)) {
        continue;
    }

    $modelClass = 'model\\' . substr($model, 0, -4);

    require_once $modelPath;

    echo Color::get("\t" . 'Generating table for ' . $modelClass, Color::GREEN) . PHP_EOL;

    try {
        $reflection = new \ReflectionClass($modelClass);

        // check if modelclass is entity
        if(
            !$reflection->isSubclassOf(Entity::class) ||
            $reflection->isAbstract()
        ) {
            echo Color::get("\t" . 'Skipping ' . $modelClass, Color::YELLOW) . PHP_EOL;
            continue;
        } else {
            $tableName = strtolower($reflection->getShortName());

            $properties = $reflection->getProperties();

            $columns = [];

            foreach ($properties as $property) {
                $columns[] = array(
                    'name' => $property->getName(),
                    'type' => parseDocComment($property->getDocComment())
                );
            }


            $sql = 'CREATE TABLE IF NOT EXISTS ' . $tableName . ' (';

            foreach ($columns as $column) {
                $sql .= $column['name'] . ' ' . $column['type'] . ', ';
            }

            $sql = substr($sql, 0, -2);

            $sql .= ');';



            if(file_exists($CREATE_SQL_FILE)) {
                unlink($CREATE_SQL_FILE);
            }

            @mkdir(dirname($CREATE_SQL_FILE), 0777, true);

            file_put_contents($CREATE_SQL_FILE, $sql . PHP_EOL, FILE_APPEND);
        }

    } catch (ReflectionException $e) {
        die(Color::get($e->getMessage(), Color::RED) . PHP_EOL);
    }
}


function parseDocComment($docComment): string
{
    $output = array();

    // remove the opening "/**" and closing "*/" from the doccomment
    $docComment = substr($docComment, 3, -2);

    // split the doccomment into lines
    $lines = explode("\n", $docComment);

    // iterate through each line
    foreach ($lines as $line) {
        // remove any leading spaces and asterisks
        $line = trim($line, " *\t");

        // check if the line starts with "@SQL"
        if (str_starts_with($line, "@SQL")) {
            // extract the contents after "@SQL"
            $output[] = trim(substr($line, 4));
        }
    }

    return implode(' ', $output);
}
