<?php

namespace controller;

use betterphp\utils\Controller;
use PDO;

require_once dirname(__DIR__) . '/../betterphp/utils/Controller.php';

class StudentController extends Controller
{
    public function getStudents(): array
    {
        $query = $this->getConnection()->prepare('SELECT * FROM student');
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}