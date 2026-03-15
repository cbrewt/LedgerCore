<?php

namespace Core;

use PDO;
use PDOException;
use InvalidArgumentException;

class Database
{
    private static $instance = null;
    private $connection;
    private $statement;

    private function __construct(string $dsn, ?string $username = null, ?string $password = null)
    {
        try {
            $options = [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ];

            if (str_starts_with($dsn, 'sqlite:')) {
                $this->connection = new PDO($dsn, null, null, $options);
            } else {
                $this->connection = new PDO($dsn, $username, $password, $options);
            }
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            $dsn = getenv('DB_DSN');
            $username = getenv('DB_USER') !== false ? (string) getenv('DB_USER') : 'root';
            $password = getenv('DB_PASS') !== false ? (string) getenv('DB_PASS') : 'navajo88';

            if ($dsn === false || $dsn === '') {
                $host = getenv('DB_HOST') !== false ? (string) getenv('DB_HOST') : '127.0.0.1';
                $dbName = getenv('DB_NAME') !== false ? (string) getenv('DB_NAME') : 'rpfinance';
                $charset = getenv('DB_CHARSET') !== false ? (string) getenv('DB_CHARSET') : 'utf8';
                $dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";
            }

            self::$instance = new self($dsn, $username, $password);
        }

        return self::$instance;
    }

    public function query($query, $params = [])
    {
        if (empty($query)) {
            throw new InvalidArgumentException('Query cannot be empty.');
        }

        try {
            error_log("Generated Query: $query");
            error_log("Query Parameters: " . json_encode($params));

            $this->statement = $this->connection->prepare($query);

            foreach ($params as $key => $value) {
                if (is_int($key)) {
                    $parameter = $key + 1;
                } else {
                    $parameter = str_starts_with($key, ':') ? $key : ':' . $key;
                }

                if (is_int($value) || (is_string($value) && ctype_digit($value))) {
                    $this->statement->bindValue($parameter, (int) $value, PDO::PARAM_INT);
                } elseif (is_bool($value)) {
                    $this->statement->bindValue($parameter, $value ? 1 : 0, PDO::PARAM_INT);
                } elseif ($value === null) {
                    $this->statement->bindValue($parameter, null, PDO::PARAM_NULL);
                } else {
                    $this->statement->bindValue($parameter, $value, PDO::PARAM_STR);
                }
            }

            $this->statement->execute();

            error_log("Query executed successfully.");
            return $this;
        } catch (PDOException $e) {
            error_log('SQL Error: ' . $e->getMessage());
            error_log('Query: ' . $query);

            if (!empty($params)) {
                error_log('Parameters: ' . json_encode($params));
            }

            throw $e;
        }
    }

    public function prepare($query)
    {
        return $this->connection->prepare($query);
    }

    public function get()
    {
        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find()
    {
        try {
            return $this->statement->fetch();
        } catch (PDOException $e) {
            error_log('Error fetching single result: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findOrFail()
    {
        $result = $this->find();

        if (!$result) {
            throw new \Exception('No record found');
        }

        return $result;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function fetchColumn($query, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetchColumn();

            return $result !== false ? (float) $result : 0.00;
        } catch (PDOException $e) {
            error_log('Error fetching column: ' . $e->getMessage());
            throw $e;
        }
    }
}