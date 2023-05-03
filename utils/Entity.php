<?php

namespace utils;

use JsonSerializable;


abstract class Entity implements JsonSerializable
{
    protected int $id;

    public function __construct(int $id) {
        $this->id = $id;
    }

    abstract public function getId(): int;
}