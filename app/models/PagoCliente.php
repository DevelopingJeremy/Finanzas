<?php
/** Model: PagoCliente */
class PagoCliente {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getByCliente(int $clienteId): array {
        $stmt = $this->db->prepare("SELECT * FROM pagos_clientes WHERE cliente_id=? ORDER BY fecha DESC");
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll();
    }

    public function getAll(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.nombre AS cliente_nombre, n.nombre AS negocio_nombre
             FROM pagos_clientes p
             JOIN clientes c ON p.cliente_id = c.id
             LEFT JOIN negocios n ON p.negocio_id = n.id
             WHERE p.usuario_id=?
             ORDER BY p.fecha DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id, int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM pagos_clientes WHERE id=? AND usuario_id=?");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("INSERT INTO pagos_clientes (cliente_id, usuario_id, negocio_id, monto, fecha, estado, descripcion) VALUES (?,?,?,?,?,?,?)");
        return $stmt->execute([$data['cliente_id'], $data['usuario_id'], $data['negocio_id'] ?: null, $data['monto'], $data['fecha'], $data['estado'], $data['descripcion']]);
    }

    public function marcarPagado(int $id, int $userId): bool {
        $stmt = $this->db->prepare("UPDATE pagos_clientes SET estado='pagado' WHERE id=? AND usuario_id=?");
        return $stmt->execute([$id, $userId]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM pagos_clientes WHERE id=? AND usuario_id=?");
        return $stmt->execute([$id, $userId]);
    }

    public function getLastInsertId(): string { return $this->db->lastInsertId(); }
}
