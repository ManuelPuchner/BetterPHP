<?php

namespace betterphp\utils;

use Exception;

class ApiException extends Exception
{
    public function __construct(int $code, string $message)
    {
        parent::__construct($message, $code);
    }
}