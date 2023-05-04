<?php

namespace betterphp\utils;

use PgSql\Connection;

class DBConnection
{
    private static ?DBConnection $instance = null;
    private Connection $connection;

    public static function getInstance(): DBConnection
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // get connection string from .env file
        $envFilePath = dirname(__DIR__) . '/../src/.env';

        $env = parse_ini_file($envFilePath);

        $this->connection = pg_connect(
            "host=" . $env['DB_HOST'] .
            " port=" . $env['DB_PORT'] .
            " dbname=" . $env['DB_NAME'] .
            " user=" . $env['DB_USER'] .
            " password=" . $env['DB_PASS']
        );
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }
}