<?php
/** Model: Recordatorio */
class Recordatorio {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT r.*, n.nombre AS negocio_nombre, cat.nombre AS categoria_nombre
             FROM recordatorios r
             LEFT JOIN negocios n ON r.negocio_id = n.id
             LEFT JOIN categorias cat ON r.categoria_id = cat.id
             WHERE r.usuario_id = ?
             ORDER BY r.fecha_vencimiento ASC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getProximos(int $userId, int $dias = 7): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM recordatorios
             WHERE usuario_id=? AND estado='pendiente'
               AND fecha_vencimiento BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY)
             ORDER BY fecha_vencimiento ASC"
        );
        $stmt->execute([$userId, $dias]);
        return $stmt->fetchAll();
    }

    public function findById(int $id, int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM recordatorios WHERE id=? AND usuario_id=?");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO recordatorios (usuario_id, negocio_id, tipo, nombre, monto, fecha_vencimiento, frecuencia, categoria_id, cuenta_id)
             VALUES (?,?,?,?,?,?,?,?,?)"
        );
        return $stmt->execute([
            $data['usuario_id'], $data['negocio_id'] ?: null, $data['tipo'],
            $data['nombre'], $data['monto'], $data['fecha_vencimiento'],
            $data['frecuencia'] ?? 'ninguna',
            $data['categoria_id'] ?: null, $data['cuenta_id'] ?: null
        ]);
    }

    public function update(int $id, int $userId, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE recordatorios SET negocio_id=?, tipo=?, nombre=?, monto=?, fecha_vencimiento=?, frecuencia=?, categoria_id=?, cuenta_id=? WHERE id=? AND usuario_id=?"
        );
        return $stmt->execute([
            $data['negocio_id'] ?: null, $data['tipo'], $data['nombre'], $data['monto'],
            $data['fecha_vencimiento'], $data['frecuencia'], $data['categoria_id'] ?: null,
            $data['cuenta_id'] ?: null, $id, $userId
        ]);
    }

    public function marcarPagado(int $id, int $userId): bool {
        $stmt = $this->db->prepare("UPDATE recordatorios SET estado='pagado', ultima_ejecucion=NOW() WHERE id=? AND usuario_id=?");
        return $stmt->execute([$id, $userId]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM recordatorios WHERE id=? AND usuario_id=?");
        return $stmt->execute([$id, $userId]);
    }

    public function getLastInsertId(): string { return $this->db->lastInsertId(); }
}
