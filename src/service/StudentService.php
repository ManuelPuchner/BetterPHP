<?php

namespace service;

use betterphp\utils\BodyParam;
use betterphp\utils\GET;
use betterphp\utils\Inject;
use betterphp\utils\PathParam;
use betterphp\utils\POST;
use betterphp\utils\QueryParam;
use betterphp\utils\Response;
use betterphp\utils\Route;
use betterphp\utils\Service;
use controller\StudentController;

#[Service]
class StudentService {

    #[Route('/student')]
    #[GET]
    public function getStudents(): Response {
        return Response::ok('Hello World', [
            'name' => 'John Doe',
            'age' => 20
        ]);
    }

    #[Route('/student')]
    #[POST]
    public function createStudent(#[BodyParam] $test): Response {
        return Response::ok('Hello World', [
            'test' => $test
        ]);
    }

    #[Route('/student/{id}')]
    #[GET]
    public function getStudentById(#[PathParam] int $id): Response {
        return Response::ok('Hello World', ['id' => $id]);
    }

    #[Route('/student/test')]
    #[GET]
    public function getStudentTest(#[QueryParam] string $test, #[QueryParam] string $hello): Response {
        return Response::ok('Hello World', [
            'test' => $test,
            'hello' => $hello
        ]);
    }
}