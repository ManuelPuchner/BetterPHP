<?php

namespace betterphp\utils;

use Attribute;
use PDO;

require_once __DIR__ . '/DBConnection.php';

#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
    private static ?Controller $instance = null;
    private static PDO $connection;

    public static function getInstance(): static
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
            self::$connection = DBConnection::getInstance()->getConnection();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return self::$connection;
    }

    private function __construct() {}
}