<?php

declare(strict_types=1);

/**
 * Core Database Wrapper Class
 * Utilizes PDO for secure, prepared SQL interactions with MySQL.
 * Supports both direct instantiation and Singleton pattern.
 */
class Database
{
    private string $host;
    private string $user;
    private string $pass;
    private string $dbName;
    private string $port;
    private string $charset;

    private ?PDO $dbh = null;
    private ?PDOStatement $stmt = null;
    private static ?Database $instance = null;

    public function __construct()
    {
        $this->host = DB_HOST;
        $this->user = DB_USER;
        $this->pass = DB_PASS;
        $this->dbName = DB_NAME;
        $this->port = defined('DB_PORT') ? DB_PORT : '3306';
        $this->charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

        // Build Data Source Name (DSN)
        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbName};charset={$this->charset}";

        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // In development, display error; in production, log it.
            die('Database Connection Failed: ' . $e->getMessage());
        }
    }

    /**
     * Singleton instance accessor
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Prepare an SQL statement
     */
    public function query(string $query): self
    {
        $this->stmt = $this->dbh->prepare($query);
        return $this;
    }

    /**
     * Bind parameters to prepared statement with automatic PDO type inference
     */
    public function bind(string|int $param, mixed $value, ?int $type = null): void
    {
        if (is_null($type)) {
            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
        }

        $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * Execute the prepared statement
     */
    public function execute(): bool
    {
        return $this->stmt->execute();
    }

    /**
     * Fetch all results as an array of associative arrays
     */
    public function resultSet(): array
    {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    /**
     * Fetch a single row as an associative array
     */
    public function single(): mixed
    {
        $this->execute();
        return $this->stmt->fetch();
    }

    /**
     * Return the number of affected rows
     */
    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * Retrieve the last inserted ID
     */
    public function lastInsertId(): string|false
    {
        return $this->dbh->lastInsertId();
    }
}
