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
                    'type' => parseDocComment($property->getDocComment(), '@SQL')
                );
            }

            $sql = "DROP TABLE IF EXISTS $tableName;\n\n";

            $sql .= 'CREATE TABLE IF NOT EXISTS ' . $tableName . ' (' . "\n";

            for ($i = 0; $i < count($columns); $i++) {
                $column = $columns[$i];

                $sql .= "\t" . $column['name'] . ' ' . $column['type'];

                if ($i < count($columns) - 1) {
                    $sql .= ',';
                    $sql .= "\n";
                }
            }



            $tableConstraints = parseTableConstraints($reflection->getDocComment());

            if(count($tableConstraints) > 0) {
                $sql .= ',';
                echo Color::get("\t\t" . 'Adding table constraints', Color::GREEN) . PHP_EOL;
                echo Color::get("\t\t\t" . 'Constraints: ' . implode(', ', $tableConstraints), Color::GREEN) . PHP_EOL;

                $sql .= "\n";

                for ($i = 0; $i < count($tableConstraints); $i++) {
                    $constraint = $tableConstraints[$i];

                    $sql .= "\t" . $constraint;

                    if ($i < count($tableConstraints) - 1) {
                        $sql .= ',';
                    }

                    $sql .= "\n";
                }
            }


            $sql .= "\n" . ');';





            $oldContent = @file_get_contents($CREATE_SQL_FILE);

            if(file_exists($CREATE_SQL_FILE)) {
                unlink($CREATE_SQL_FILE);
            }

            @mkdir(dirname($CREATE_SQL_FILE), 0777, true);

            $data = $oldContent . PHP_EOL . $sql . PHP_EOL;

            file_put_contents($CREATE_SQL_FILE, $data, FILE_APPEND);
        }

    } catch (ReflectionException $e) {
        die(Color::get($e->getMessage(), Color::RED) . PHP_EOL);
    }
}

function parseDocComment($docComment, $valueToSearch): string
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

        // check if the line starts with $valueToSearch
        if (str_starts_with($line, $valueToSearch)) {
            // remove the $valueToSearch from the line
            $line = substr($line, strlen($valueToSearch));

            // remove any leading spaces and asterisks
            $line = trim($line, " *\t");

            // add the line to the output
            $output[] = $line;
        }
    }

    return implode(' ', $output);
}


function parseTableConstraints(string $doccommentTableConstraints): array
{
    $doccommentTableConstraintsRows = explode("\n", $doccommentTableConstraints);

    $doccommentTableConstraintsRows = str_replace('/**', '', $doccommentTableConstraintsRows);
    $doccommentTableConstraintsRows = str_replace('*/', '', $doccommentTableConstraintsRows);
    $doccommentTableConstraintsRows = str_replace('*', '', $doccommentTableConstraintsRows);

    $doccommentTableConstraintsRows = array_map('trim', $doccommentTableConstraintsRows);

    $doccommentTableConstraintsRows = array_filter($doccommentTableConstraintsRows, function($row) {
        return !empty($row);
    });

    $doccommentTableConstraints = array();

    foreach ($doccommentTableConstraintsRows as $row) {
        $row = parseDocComment("/** " .$row." */", '@TABLE_CONSTRAINT');
        $doccommentTableConstraints[] = $row;
    }

    return $doccommentTableConstraints;
}
