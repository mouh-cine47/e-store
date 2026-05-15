<?php

class Database
{
    private static $pdo = null;

    public static function connection()
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $dbname = getenv('DB_NAME') ?: 'inventory_db';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASS');

        if ($password === false) {
            $password = '';
        }

        $dsn = 'mysql:host=' . $host . ';dbname=' . $dbname . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        self::$pdo = new PDO($dsn, $username, $password, $options);
        return self::$pdo;
    }
}
