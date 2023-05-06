<?php


use betterphp\utils\Response;
use betterphp\utils\Route;
use controller\PortfolioController;

Route::get('/portfolio', function () {
    $data = PortfolioController::getInstance()->getPortfolios();
    return Response::ok('Hello World', $data);
});

