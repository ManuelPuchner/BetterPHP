<?php

use utils\Route;

Route::get('/test', function () {
    return Response::ok('Hello World');
});