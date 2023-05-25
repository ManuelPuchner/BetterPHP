<?php

namespace betterphp\utils;

use PDO;

require_once __DIR__ . '/DBConnection.php';

abstract class Controller
{
    private static array $instances = [];
    protected static PDO $connection;


    protected function __construct()
    {
        // Protected constructor to prevent direct instantiation
        self::$connection = DBConnection::getInstance()->getConnection();
    }

    public static function getInstance(): static
    {
        $className = static::class;
        if (!isset(self::$instances[$className])) {
            self::$instances[$className] = new static();
            self::$instances[$className]->initialize();
        }
        return self::$instances[$className];
    }

    protected function initialize(): void
    {
        // Override this method in subclass if needed
    }

    // Other common methods or properties for controllers


}