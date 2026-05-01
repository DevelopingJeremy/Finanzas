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

    /** Cuentas activas de un negocio */
    public function getCuentas(int $negocioId): array {
        $stmt = $this->db->prepare(
            "SELECT c.*,
                    COALESCE(SUM(sc.saldo), 0) AS saldo_bolsillos,
                    COUNT(sc.id) AS num_bolsillos
             FROM cuentas c
             LEFT JOIN subcuentas sc ON sc.cuenta_id = c.id
             WHERE c.negocio_id = ? AND c.activo = 1
             GROUP BY c.id
             ORDER BY c.nombre"
        );
        $stmt->execute([$negocioId]);
        return $stmt->fetchAll();
    }

    /** Bolsillos de una cuenta específica */
    public function getBolsillos(int $cuentaId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM subcuentas WHERE cuenta_id = ? ORDER BY nombre"
        );
        $stmt->execute([$cuentaId]);
        return $stmt->fetchAll();
    }

    /** Transacciones recientes de una cuenta (últimas 50) */
    public function getTransaccionesCuenta(int $cuentaId, int $negocioId, int $limit = 50): array {
        $stmt = $this->db->prepare(
            "SELECT t.*, cat.nombre AS categoria_nombre, cat.color AS categoria_color,
                    cd.nombre AS cuenta_destino_nombre
             FROM transacciones t
             LEFT JOIN categorias cat ON t.categoria_id = cat.id
             LEFT JOIN cuentas cd ON t.cuenta_destino_id = cd.id
             WHERE t.cuenta_id = ? AND t.negocio_id = ?
             ORDER BY t.fecha DESC
             LIMIT ?"
        );
        $stmt->execute([$cuentaId, $negocioId, $limit]);
        return $stmt->fetchAll();
    }

    /** Totales de ingresos y gastos de una cuenta */
    public function getTotalesCuenta(int $cuentaId, int $negocioId): array {
        $stmt = $this->db->prepare(
            "SELECT tipo, SUM(monto) AS total
             FROM transacciones
             WHERE cuenta_id = ? AND negocio_id = ? AND estado = 'completado'
             GROUP BY tipo"
        );
        $stmt->execute([$cuentaId, $negocioId]);
        $rows = $stmt->fetchAll();
        $result = ['ingreso' => 0.0, 'gasto' => 0.0, 'transferencia' => 0.0];
        foreach ($rows as $r) $result[$r['tipo']] = (float)$r['total'];
        return $result;
    }

    /** Recordatorios pendientes del negocio */
    public function getRecordatoriosPendientes(int $negocioId, int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT r.*, c.nombre AS cuenta_nombre, cat.nombre AS categoria_nombre
             FROM recordatorios r
             LEFT JOIN cuentas c ON r.cuenta_id = c.id
             LEFT JOIN categorias cat ON r.categoria_id = cat.id
             WHERE r.negocio_id = ? AND r.usuario_id = ? AND r.estado = 'pendiente'
             ORDER BY r.fecha_vencimiento ASC
             LIMIT 20"
        );
        $stmt->execute([$negocioId, $userId]);
        return $stmt->fetchAll();
    }

    /** Totales globales del negocio (suma de todas las cuentas) */
    public function getResumenNegocio(int $negocioId, int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT
                SUM(CASE WHEN t.tipo='ingreso' THEN t.monto ELSE 0 END) AS total_ingresos,
                SUM(CASE WHEN t.tipo='gasto'   THEN t.monto ELSE 0 END) AS total_gastos,
                COUNT(t.id) AS num_transacciones
             FROM transacciones t
             WHERE t.negocio_id = ? AND t.usuario_id = ? AND t.estado = 'completado'"
        );
        $stmt->execute([$negocioId, $userId]);
        return $stmt->fetch() ?: ['total_ingresos' => 0, 'total_gastos' => 0, 'num_transacciones' => 0];
    }

    public function getLastInsertId(): string { return $this->db->lastInsertId(); }
}
