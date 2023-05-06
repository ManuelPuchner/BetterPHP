<?php

namespace service;

use betterphp\utils\GET;
use betterphp\utils\HttpMethod;
use betterphp\utils\POST;
use betterphp\utils\Route;
use betterphp\utils\Service;
use controller\StudentController;

#[Service]
class StudentService {

    #[Inject]
    private StudentController $studentController;

    #[Route('/currency')]
    #[GET]
    public function getCurrency(): array {
        return [
            'currency' => 'USD',
            'value' => 1.0
        ];
    }
}