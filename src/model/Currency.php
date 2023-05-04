<?php

namespace model;

use betterphp\utils\Entity;

require_once dirname(__DIR__) . '/../betterphp/utils/Entity.php';

class Currency extends Entity
{

    /** @SQL varchar(100) */
    private string $name;

    /** @SQL varchar(4)*/
    private string $code;

    public function __construct(int $id, string $name, string $code)
    {
        parent::__construct($id);
        $this->name = $name;
        $this->code = $code;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code
        ];
    }
}