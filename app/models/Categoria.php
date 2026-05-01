<?php
/** Model: Categoria */
class Categoria {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM categorias WHERE usuario_id=? OR es_default=1 ORDER BY tipo, nombre");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    public function getByTipo(int $userId, string $tipo): array {
        $stmt = $this->db->prepare("SELECT * FROM categorias WHERE (usuario_id=? OR es_default=1) AND tipo=? ORDER BY nombre");
        $stmt->execute([$userId, $tipo]);
        return $stmt->fetchAll();
    }
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM categorias WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    public function create(array $data): bool {
        $stmt = $this->db->prepare("INSERT INTO categorias (usuario_id, nombre, tipo, color, icono) VALUES (?,?,?,?,?)");
        return $stmt->execute([$data['usuario_id'], $data['nombre'], $data['tipo'], $data['color'], $data['icono']]);
    }
    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE categorias SET nombre=?, tipo=?, color=?, icono=? WHERE id=?");
        return $stmt->execute([$data['nombre'], $data['tipo'], $data['color'], $data['icono'], $id]);
    }
    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM categorias WHERE id=? AND usuario_id=? AND es_default=0");
        return $stmt->execute([$id, $userId]);
    }
    public function getLastInsertId(): string { return $this->db->lastInsertId(); }
}
