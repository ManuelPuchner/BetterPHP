<?php

namespace betterphp\utils;

use PgSql\Connection;

require_once __DIR__ . '/DBConnection.php';

abstract class Controller
{
    private static ?Controller $instance = null;
    private static Connection $connection;

    public static function getInstance(): Controller
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
            self::$connection = DBConnection::getInstance()->getConnection();
        }
        return self::$instance;
    }

    public function getConnection(): Connection
    {
        return self::$connection;
    }

    private function __construct() {}
}