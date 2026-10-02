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
    private string $username = 'root'; // MAMP
    // private string $username = 'root'; // XAMP
    private string $password = 'root';
    private string $charset  = 'utf8mb4';

    private function __construct() {
        // Configurar zona horaria de Costa Rica (UTC-6)
        date_default_timezone_set('America/Costa_Rica');

        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            $this->pdo->exec("SET time_zone = '-06:00'");
            $this->ensureSchema();
        } catch (PDOException $e) {
            error_log("DB Error: " . $e->getMessage());
            die(json_encode(['error' => 'Error de conexión a la base de datos.']));
        }
    }

    /** Migración automática para asegurar columnas necesarias */
    private function ensureSchema(): void {
        try {
            $cols = $this->pdo->query("SHOW COLUMNS FROM transacciones LIKE 'subcuenta_id'")->fetchAll();
            if (empty($cols)) {
                $this->pdo->exec("ALTER TABLE transacciones ADD COLUMN subcuenta_id INT NULL AFTER cuenta_id");
            }
            $colsDest = $this->pdo->query("SHOW COLUMNS FROM transacciones LIKE 'subcuenta_destino_id'")->fetchAll();
            if (empty($colsDest)) {
                $this->pdo->exec("ALTER TABLE transacciones ADD COLUMN subcuenta_destino_id INT NULL AFTER cuenta_destino_id");
            }
        } catch (PDOException $e) {
            // Continuar si la tabla aún no existe
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

