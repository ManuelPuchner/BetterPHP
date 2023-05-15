<?php

namespace service;

use betterphp\utils\attributes\GET;
use betterphp\utils\attributes\Route;
use betterphp\utils\attributes\Service;
use betterphp\utils\Response;
use controller\StudentController;

#[Service]
class StudentService {

    #[Route('/student')]
    #[GET]
    public function getStudents(): Response {
        $students = StudentController::getInstance()->getStudents();
        return Response::ok('Hello World', $students);
    }

//    #[Route('/student')]
//    #[POST]
//    public function createStudent(#[BodyParam] $test): Response {
//        return Response::ok('Hello World', [
//            'test' => $test
//        ]);
//    }
//
//    #[Route('/student/{id}')]
//    #[GET]
//    public function getStudentById(#[PathParam] int $id): Response {
//        return Response::ok('Hello World', ['id' => $id]);
//    }
//
//    #[Route('/student/test')]
//    #[GET]
//    public function getStudentTest(#[QueryParam] string $test, #[QueryParam] string $hello): Response {
//        return Response::ok('Hello World', [
//            'test' => $test,
//            'hello' => $hello
//        ]);
//    }
}