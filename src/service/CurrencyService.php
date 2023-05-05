<?php

namespace service;

use betterphp\utils\Response;
use betterphp\utils\Route;
use controller\CurrencyController;
use model\Currency;


Route::get('/test', function () {
    $data = CurrencyController::getInstance()->getCurrencies();
    return Response::ok('Hello World', $data);
});

Route::post('/test', function (array $body) {

    $data = CurrencyController::getInstance()->addCurrency(new Currency(0, $body['name'], $body['code']));

    return Response::ok('Hello World', $data);
});

Route::get('/test/{id}', function (int $id) {
        $data = CurrencyController::getInstance()->getById($id);
        return Response::ok('Hello World', $data);
});
