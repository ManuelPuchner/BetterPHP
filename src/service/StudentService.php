<?php

use utils\Route;
use utils\Response;

Route::get('/students/{id}/{test}', function (int $id, string $test) {
    return Response::ok('Hello World', array($id, $test));
});

Route::get('/students/{id}', function (int $id) {
    return Response::ok('Hello World 2', array($id));
});


Route::get('/students', function () {
    return Response::ok('Hello World');
});


Route::get('/students/hello/{hello}', function (string $hello) {
    return Response::ok('Hello World', $hello . ' moin');
});