<?php

namespace betterphp\Orm;

use Attribute;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    private string $name;
    private ?string $type = null;

    public function __construct(array $args) {
        $this->name = $args['name'] ?? throw new InvalidArgumentException('Column name must be specified');
        $this->type = $args->type ?? null;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getType(): string | false {
        return $this->type ?? false;
    }
}