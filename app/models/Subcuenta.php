<?php
/** Model: Subcuenta (Bolsillo) */
class Subcuenta {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT s.*, c.nombre AS cuenta_nombre
             FROM subcuentas s
             JOIN cuentas c ON s.cuenta_id = c.id
             WHERE c.usuario_id = ?
             ORDER BY c.nombre, s.nombre"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    public function getByCuenta(int $cuentaId): array {
        $stmt = $this->db->prepare("SELECT * FROM subcuentas WHERE cuenta_id=? ORDER BY nombre");
        $stmt->execute([$cuentaId]);
        return $stmt->fetchAll();
    }
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM subcuentas WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    public function create(array $data): bool {
        $stmt = $this->db->prepare("INSERT INTO subcuentas (cuenta_id, nombre, descripcion, saldo) VALUES (?,?,?,?)");
        return $stmt->execute([$data['cuenta_id'], $data['nombre'], $data['descripcion'], $data['saldo'] ?? 0]);
    }
    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE subcuentas SET cuenta_id=?, nombre=?, descripcion=? WHERE id=?");
        return $stmt->execute([$data['cuenta_id'], $data['nombre'], $data['descripcion'], $id]);
    }
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM subcuentas WHERE id=?");
        return $stmt->execute([$id]);
    }
    /** Actualiza el saldo de una subcuenta */
    public function updateSaldo(int $id, float $delta): bool {
        $stmt = $this->db->prepare("UPDATE subcuentas SET saldo = saldo + ? WHERE id=?");
        return $stmt->execute([$delta, $id]);
    }
    public function getLastInsertId(): string { return $this->db->lastInsertId(); }
}
