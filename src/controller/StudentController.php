<?php

namespace controller;

use betterphp\utils\Controller;
use betterphp\utils\DBConnection;
use betterphp\utils\Inject;


#[Controller]
class StudentController
{
    #[Inject]
    private DBConnection $connection;

    public function getStudents(): array
    {
        $sql = 'SELECT * FROM students';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}