<?php
/** Model: Cuenta */
class Cuenta {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT c.*, n.nombre AS negocio_nombre
             FROM cuentas c
             LEFT JOIN negocios n ON c.negocio_id = n.id
             WHERE c.usuario_id = ? AND c.activo = 1
             ORDER BY c.nombre"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    public function findById(int $id, int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM cuentas WHERE id=? AND usuario_id=?");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch() ?: null;
    }
    public function create(array $data): bool {
        $stmt = $this->db->prepare("INSERT INTO cuentas (usuario_id, negocio_id, nombre, tipo, saldo, moneda) VALUES (?,?,?,?,?,?)");
        return $stmt->execute([$data['usuario_id'], $data['negocio_id'] ?: null, $data['nombre'], $data['tipo'], $data['saldo'], $data['moneda']]);
    }
    public function update(int $id, int $userId, array $data): bool {
        $stmt = $this->db->prepare("UPDATE cuentas SET negocio_id=?, nombre=?, tipo=?, moneda=?, activo=? WHERE id=? AND usuario_id=?");
        return $stmt->execute([$data['negocio_id'] ?: null, $data['nombre'], $data['tipo'], $data['moneda'], $data['activo'], $id, $userId]);
    }
    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("UPDATE cuentas SET activo=0 WHERE id=? AND usuario_id=?");
        return $stmt->execute([$id, $userId]);
    }
    /** Actualiza el saldo de una cuenta (suma o resta) */
    public function updateSaldo(int $id, float $delta): bool {
        $stmt = $this->db->prepare("UPDATE cuentas SET saldo = saldo + ? WHERE id=?");
        return $stmt->execute([$delta, $id]);
    }
    public function getLastInsertId(): string { return $this->db->lastInsertId(); }
}
