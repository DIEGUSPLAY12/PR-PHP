<?php

namespace App\DB;

use PDO;
use PDOException;

final class Database
{
    private static $instance = null;
    private $connection = null;

    private function __construct()
    {
        $host = 'db';
        $dbname = 'mydatabase';
        $user = 'myuser';
        $password = 'mypassword';

        try {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            $this->connection = new PDO($dsn, $user, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    private function __clone()
    {
    }
}
