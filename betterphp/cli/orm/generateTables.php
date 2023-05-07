<?php

use betterphp\cli\Color;
use betterphp\Orm;

require_once dirname(__DIR__) . '/utilfunctions.php';
require_once dirname(__DIR__) . '/Color.php';
require_once dirname(__DIR__) . '/../Orm/Entity.php';
require_once dirname(__DIR__) . '/../Orm/Column.php';
require_once dirname(__DIR__) . '/../Orm/PrimaryKey.php';
require_once dirname(__DIR__) . '/../Orm/AutoIncrement.php';

require_once dirname(__DIR__) . '/../utils/attributes/Service.php';


$SRC_DIR = dirname(__DIR__) . '/../../src';
$CREATE_SQL_FILE = dirname(__DIR__) . '/../../dist/sql/create.sql';


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

            echo Color::GREEN . "Generating table for $className" . Color::RESET . PHP_EOL;


            $tableName = getClassAttribute($reflection, Orm\Entity::class)->newInstance()->getTableName();

            $properties = $reflection->getProperties();

            $columns = [];

            foreach ($properties as $property) {
                $columnAttribute = getPropertyAttribute($property, Orm\Column::class);

                if($columnAttribute) {
                    $columnName = $columnAttribute->newInstance()->getName();
                    echo "\tFound column " . Color::get($columnName, Color::PURPLE) . PHP_EOL;
                    if ($columnName) {
                        $columnName = $property->getName();
                        $columnType = getTypeFromProperty($property);
                        $columnConstraint = getColumnConstraintFromProperty($property);

                        if($columnType === 'INT' && str_contains($columnConstraint, 'AUTO_INCREMENT')) {
                            $columnConstraint = str_replace('AUTO_INCREMENT', '', $columnConstraint);
                            $columnConstraint = trim($columnConstraint);
                            $columnType = 'bigserial';
                        }

                        echo "\t\tType: " . Color::get($columnType, Color::GREEN) . PHP_EOL;

                        if($columnConstraint) {
                            echo "\t\tConstraint: " . Color::get($columnConstraint, Color::GREEN) . PHP_EOL;
                        } else {
                            echo "\t\tConstraint: " . Color::get('None', Color::YELLOW) . PHP_EOL;
                        }


                        $columns[] = [
                            'name' => $columnName,
                            'type' => $columnType,
                            'constraint' => $columnConstraint
                        ];
                    }
                }
            }

            $sql = "DROP TABLE IF EXISTS $tableName;\n\n";

            $sql .= 'CREATE TABLE IF NOT EXISTS ' . $tableName . ' (' . "\n";

            for ($i = 0; $i < count($columns); $i++) {
                $column = $columns[$i];

                $sql .= "\t" . $column['name'] . ' ' . $column['type'] . ' ' . $column['constraint'];

                if ($i < count($columns) - 1) {
                    $sql .= ',';
                    $sql .= "\n";
                }
            }

            $sql .= "\n);";


            $oldContent = @file_get_contents($CREATE_SQL_FILE);

            if (file_exists($CREATE_SQL_FILE)) {
                @unlink($CREATE_SQL_FILE);
            }

            @mkdir(dirname($CREATE_SQL_FILE), 0777, true);

            $data = $oldContent . PHP_EOL . $sql . PHP_EOL;

            @file_put_contents($CREATE_SQL_FILE, $data, FILE_APPEND);

        }

    } catch (ReflectionException $e) {
        echo Color::RED . "Error: " . $e->getMessage() . Color::RESET . PHP_EOL;
    }
}


/**
 * @throws ReflectionException
 */
function getTypeFromProperty(ReflectionProperty $reflectionProperty): string
{
    $setType = getPropertyAttribute($reflectionProperty, Orm\Column::class)->newInstance()->getType();
    if($setType) {
        return $setType;
    } else {
        return mapSqlType($reflectionProperty->getType());
    }
}

function getColumnConstraintFromProperty(ReflectionProperty $property): string
{
    $constraints = [];
    $propertyAttributes = $property->getAttributes();
    foreach ($propertyAttributes as $propertyAttribute) {
        $attribute = $propertyAttribute->newInstance();
        if ($attribute instanceof Orm\PrimaryKey) {
            $constraints[] = 'PRIMARY KEY';
        } else if ($attribute instanceof Orm\AutoIncrement) {
            $constraints[] = 'AUTO_INCREMENT';
        }
    }
    return implode(' ', $constraints);
}

function mapSqlType(string $type): string
{
    return match ($type) {
        'int' => 'INT',
        'float' => 'FLOAT',
        'bool' => 'BOOLEAN',
        'DateTime' => 'DATETIME',
        default => 'VARCHAR(255)',
    };
}