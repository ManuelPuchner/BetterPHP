<?php

namespace betterphp\Orm;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Entity
{
    private string $tablename;

    public function __construct(string $tablename) {
        $this->tablename = $tablename;
    }


    public function getTablename(): string {
        return $this->tablename;
    }
}