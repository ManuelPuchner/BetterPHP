<?php

namespace controller;

use betterphp\utils\Controller;
use PDO;
use service\Inject;

#[Controller]
class StudentController
{
    #[Inject]
    private PDO $connection;

    public function getStudents(): array
    {
        $sql = 'SELECT * FROM students';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}