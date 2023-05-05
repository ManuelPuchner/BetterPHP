<?php

namespace model;

use betterphp\utils\Entity;

require_once dirname(__DIR__) . '/../betterphp/utils/Entity.php';

/**  */
class Portfolio extends Entity
{



    public function getId(): int
    {
        return $this->id;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id
        ];
    }
}