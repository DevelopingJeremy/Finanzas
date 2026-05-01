<?php
/**
 * Model: Usuario
 * Gestiona el acceso y creación de usuarios en la BD
 */
class Usuario {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Busca un usuario por email */
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([trim($email)]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /** Busca un usuario por ID */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, nombre, email, creado_en FROM usuarios WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /** Crea un nuevo usuario con contraseña hasheada */
    public function create(string $nombre, string $email, string $password): bool {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare(
            "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)"
        );
        return $stmt->execute([trim($nombre), trim($email), $hash]);
    }

    /** Verifica si el email ya está registrado */
    public function emailExists(string $email): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
        $stmt->execute([trim($email)]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /** Verifica la contraseña contra el hash */
    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}
