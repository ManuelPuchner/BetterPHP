<?php

namespace betterphp\utils;

use JsonSerializable;


abstract class Entity implements JsonSerializable
{
    /** @SQL bigserial NOT NULL PRIMARY KEY*/
    protected int $id;

    public function __construct(int $id) {
        $this->id = $id;
    }

    abstract public function getId(): int;

    abstract public function jsonSerialize(): array;
}