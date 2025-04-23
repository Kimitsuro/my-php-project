<?php
namespace App\Core;

use PDO;
use PDOException;

class Database
{
    protected $pdo;

    public function __construct(array $config)
    {
        try {
            $this->pdo = new \PDO(
                "pgsql:host={$config['host']};dbname={$config['dbname']}", 
                $config['user'], 
                $config['password']
            );
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Ошибка подключения к базе данных: " . $e->getMessage());
        }
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    public function getConnection(): \PDO
    {
        return $this->pdo;
    }
}