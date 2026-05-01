<?php
/** Model: Negocio */
class Negocio {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM negocios WHERE usuario_id = ? ORDER BY nombre");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    public function findById(int $id, int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM negocios WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch() ?: null;
    }
    public function create(array $data): bool {
        $stmt = $this->db->prepare("INSERT INTO negocios (usuario_id, nombre, tipo, descripcion) VALUES (?,?,?,?)");
        return $stmt->execute([$data['usuario_id'], $data['nombre'], $data['tipo'], $data['descripcion']]);
    }
    public function update(int $id, int $userId, array $data): bool {
        $stmt = $this->db->prepare("UPDATE negocios SET nombre=?, tipo=?, descripcion=?, activo=? WHERE id=? AND usuario_id=?");
        return $stmt->execute([$data['nombre'], $data['tipo'], $data['descripcion'], $data['activo'], $id, $userId]);
    }
    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM negocios WHERE id=? AND usuario_id=?");
        return $stmt->execute([$id, $userId]);
    }
    public function getLastInsertId(): string { return $this->db->lastInsertId(); }
}
