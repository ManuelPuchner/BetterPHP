<?php

namespace service;

use betterphp\utils\GET;
use betterphp\utils\Inject;
use betterphp\utils\POST;
use betterphp\utils\Route;
use betterphp\utils\Service;
use controller\StudentController;

#[Service]
class StudentService {

    #[Route('/student')]
    #[GET]
    public function getCurrency(): array {
        return [
            'currency' => 'USD',
            'value' => 1.0
        ];
    }

    #[Route('/student')]
    #[POST]
    public function createStudent(): array {
        return [
            'currency' => 'USD',
            'value' => 1.0
        ];
    }
}