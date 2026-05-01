<?php
/** Model: Cliente */
class Cliente {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT c.*, n.nombre AS negocio_nombre,
                    (SELECT SUM(p.monto) FROM pagos_clientes p WHERE p.cliente_id=c.id AND p.estado='pendiente') AS deuda_pendiente
             FROM clientes c
             LEFT JOIN negocios n ON c.negocio_id = n.id
             WHERE c.usuario_id = ?
             ORDER BY c.nombre"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id, int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE id=? AND usuario_id=?");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("INSERT INTO clientes (usuario_id, negocio_id, nombre, telefono, email, notas) VALUES (?,?,?,?,?,?)");
        return $stmt->execute([$data['usuario_id'], $data['negocio_id'] ?: null, $data['nombre'], $data['telefono'], $data['email'], $data['notas']]);
    }

    public function update(int $id, int $userId, array $data): bool {
        $stmt = $this->db->prepare("UPDATE clientes SET negocio_id=?, nombre=?, telefono=?, email=?, notas=? WHERE id=? AND usuario_id=?");
        return $stmt->execute([$data['negocio_id'] ?: null, $data['nombre'], $data['telefono'], $data['email'], $data['notas'], $id, $userId]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM clientes WHERE id=? AND usuario_id=?");
        return $stmt->execute([$id, $userId]);
    }

    public function getLastInsertId(): string { return $this->db->lastInsertId(); }
}
