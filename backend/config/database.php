<?php

namespace Config;

use Exception;
use PDO;
use PDOException;

class Database
{
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $port;
    private $charset;
    private $pdo;

    public function __construct()
    {
        // Charge les variables d'env depuis .env
        $this->loadEnv();

        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->dbname = $_ENV['DB_NAME'] ?? '';
        $this->username = $_ENV['DB_USER'] ?? '';
        $this->password = $_ENV['DB_PASS'] ?? '';
        $this->port = $_ENV['DB_PORT'] ?? '3306';
        $this->charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
    }

    /**
     * Lit le fichier .env et remplit $_ENV
     */
    private function loadEnv()
    {
        $envFile = __DIR__ . '/../.env';

        if (!file_exists($envFile)) {
            throw new Exception("Le fichier .env n'existe pas");
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue; // Ignore les commentaires
            }

            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }

    /**
     * Crée une connexion PDO si pas déjà connectée
     * @return PDO
     */
    public function connect()
    {
        if ($this->pdo === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbname};port={$this->port};charset={$this->charset}";

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];

                $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            } catch (PDOException $e) {
                throw new Exception("Erreur de connexion à la base de données : " . $e->getMessage());
            }
        }

        return $this->pdo;
    }

    /**
     * Récupère l'instance PDO (alias de connect)
     * @return PDO
     */
    public function getPdo()
    {
        return $this->connect();
    }

    /**
     * Coupe la connexion à la base
     */
    public function disconnect()
    {
        $this->pdo = null;
    }
}
