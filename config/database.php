<?php
/**
 * Clase Database - Conexión PDO con patrón Singleton
 * Garantiza una sola instancia de conexión en toda la app
 */
class Database {
    private static ?Database $instance = null;
    private PDO $pdo;

    private string $host     = 'localhost';
    private string $dbname   = 'finanzas_app';
    private string $username = 'root';
    private string $password = '';
    private string $charset  = 'utf8';

    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            error_log("DB Error: " . $e->getMessage());
            die(json_encode(['error' => 'Error de conexión a la base de datos.']));
        }
    }

    /** Obtiene la instancia única (Singleton) */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /** Retorna la conexión PDO */
    public function getConnection(): PDO {
        return $this->pdo;
    }

    private function __clone() {}
    public function __wakeup() {}
}
