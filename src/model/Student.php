<?php

use betterphp\Orm;
use betterphp\Orm\Column;
use betterphp\Orm\Entity;

#[Entity('student')]
class Student {
    #[Column('id')]
    #[Orm\PrimaryKey]
    #[Orm\AutoIncrement]
    private int $id;


    #[Column('name')]
    private string $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

}