<?php

namespace model;

use betterphp\Orm;
use betterphp\Orm\Column;
use betterphp\Orm\Entity;

#[Entity('student')]
class Student {
    #[Column([
        'name' => 'id',
        'type' => 'BIGINT',
    ])]
    #[Orm\PrimaryKey]
    #[Orm\AutoIncrement]
    private int $id;


    #[Column([
        'name' => 'name',
        'type' => 'varchar(255)',
    ])]
    private string $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

}