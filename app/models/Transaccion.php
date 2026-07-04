<?php
/** Model: Transaccion */
class Transaccion {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll(int $userId, array $filters = []): array {
        $sql = "SELECT t.*, n.nombre AS negocio_nombre, c.nombre AS cuenta_nombre,
                       cat.nombre AS categoria_nombre, cd.nombre AS cuenta_destino_nombre
                FROM transacciones t
                LEFT JOIN negocios n     ON t.negocio_id = n.id
                LEFT JOIN cuentas c      ON t.cuenta_id  = c.id
                LEFT JOIN categorias cat ON t.categoria_id = cat.id
                LEFT JOIN cuentas cd     ON t.cuenta_destino_id = cd.id
                WHERE t.usuario_id = ?";
        $params = [$userId];

        if (!empty($filters['negocio_id'])) { $sql .= " AND t.negocio_id=?"; $params[] = $filters['negocio_id']; }
        if (!empty($filters['cuenta_id']))  { $sql .= " AND t.cuenta_id=?";  $params[] = $filters['cuenta_id']; }
        if (!empty($filters['tipo']))       { $sql .= " AND t.tipo=?";        $params[] = $filters['tipo']; }
        if (!empty($filters['fecha_desde'])) { $sql .= " AND DATE(t.fecha) >= ?"; $params[] = $filters['fecha_desde']; }
        if (!empty($filters['fecha_hasta'])) { $sql .= " AND DATE(t.fecha) <= ?"; $params[] = $filters['fecha_hasta']; }

        $sql .= " ORDER BY t.fecha DESC LIMIT 200";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id, int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM transacciones WHERE id=? AND usuario_id=?");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): string|false {
        $stmt = $this->db->prepare(
            "INSERT INTO transacciones (usuario_id, negocio_id, cuenta_id, tipo, monto, fecha, descripcion, categoria_id, cuenta_destino_id, estado)
             VALUES (?,?,?,?,?,?,?,?,?,?)"
        );
        $ok = $stmt->execute([
            $data['usuario_id'], $data['negocio_id'], $data['cuenta_id'],
            $data['tipo'], $data['monto'], $data['fecha'], $data['descripcion'],
            $data['categoria_id'] ?: null, $data['cuenta_destino_id'] ?: null,
            $data['estado'] ?? 'completado'
        ]);
        return $ok ? $this->db->lastInsertId() : false;
    }

    public function delete(int $id, int $userId): ?array {
        // Retorna la transacción antes de borrarla (para revertir saldos)
        $t = $this->findById($id, $userId);
        if (!$t) return null;
        $stmt = $this->db->prepare("DELETE FROM transacciones WHERE id=? AND usuario_id=?");
        $stmt->execute([$id, $userId]);
        return $t;
    }

    /** Totales por tipo para un usuario */
    public function getTotalesByTipo(int $userId, ?int $year = null, ?int $month = null): array {
        $sql = "SELECT tipo, SUM(monto) AS total FROM transacciones WHERE usuario_id=? AND estado='completado'";
        $params = [$userId];
        
        if ($year && $month) {
            require_once BASE_PATH . '/app/helpers/timezone.php';
            $range = crMonthRange($year, $month);
            $sql .= " AND fecha >= ? AND fecha <= ?";
            $params[] = $range['inicio'];
            $params[] = $range['fin'];
        }
        
        $sql .= " GROUP BY tipo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        $result = ['ingreso'=>0,'gasto'=>0,'transferencia'=>0];
        foreach ($rows as $r) $result[$r['tipo']] = (float)$r['total'];
        return $result;
    }

    /** Totales agrupados por negocio */
    public function getTotalesByNegocio(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT IFNULL(n.nombre, '🏠 Personal/Sin Negocio') AS nombre, t.tipo, SUM(t.monto) AS total
             FROM transacciones t
             LEFT JOIN negocios n ON t.negocio_id = n.id
             WHERE t.usuario_id=? AND t.estado='completado'
             GROUP BY t.negocio_id, t.tipo
             ORDER BY n.nombre"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getUltimas(int $userId, int $limit = 5): array {
        $stmt = $this->db->prepare(
            "SELECT t.*, c.nombre AS cuenta_nombre, n.nombre AS negocio_nombre
             FROM transacciones t
             LEFT JOIN cuentas c ON t.cuenta_id = c.id
             LEFT JOIN negocios n ON t.negocio_id = n.id
             WHERE t.usuario_id=?
             ORDER BY t.fecha DESC LIMIT ?"
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
}
