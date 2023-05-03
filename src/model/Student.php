<?php
namespace src\model;
use utils\Entity;

class Student extends Entity
{

    public function __construct(int $id)
    {
        parent::__construct($id);
    }

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