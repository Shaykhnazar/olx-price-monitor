<?php

class Database
{
    private $pdo;

    public function __construct($dbConfig)
    {
        $dsn = 'mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['dbname'];
        $this->pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass']);
    }

    public function getConnection()
    {
        return $this->pdo;
    }
}
