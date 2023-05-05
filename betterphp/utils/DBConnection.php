<?php

namespace betterphp\utils;

use PDO;

class DBConnection
{
    private static ?DBConnection $instance = null;
    private PDO $connection;

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
        $envFilePath = $_SERVER["DOCUMENT_ROOT"]. '/.env';

        $env = parse_ini_file($envFilePath);

        $this->connection = new PDO(
            "pgsql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_NAME']}",
            $env['DB_USER'],
            $env['DB_PASS']
        );
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}