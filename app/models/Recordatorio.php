<?php
/** Model: Recordatorio */
class Recordatorio {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT r.*, n.nombre AS negocio_nombre, cat.nombre AS categoria_nombre,
                    c.nombre AS cuenta_nombre, sc.nombre AS subcuenta_nombre,
                    cl.nombre AS cliente_nombre
             FROM recordatorios r
             LEFT JOIN negocios n ON r.negocio_id = n.id
             LEFT JOIN categorias cat ON r.categoria_id = cat.id
             LEFT JOIN cuentas c ON r.cuenta_id = c.id
             LEFT JOIN subcuentas sc ON r.subcuenta_id = sc.id
             LEFT JOIN clientes cl ON r.cliente_id = cl.id
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
            "INSERT INTO recordatorios
                (usuario_id, negocio_id, tipo, nombre, monto, fecha_vencimiento,
                 frecuencia, categoria_id, cuenta_id, subcuenta_id, cliente_id)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)"
        );
        return $stmt->execute([
            $data['usuario_id'],
            $data['negocio_id']   ?: null,
            $data['tipo'],
            $data['nombre'],
            $data['monto'],
            $data['fecha_vencimiento'],
            $data['frecuencia']   ?? 'ninguna',
            $data['categoria_id'] ?: null,
            $data['cuenta_id']    ?: null,
            $data['subcuenta_id'] ?: null,
            $data['cliente_id']   ?: null,
        ]);
    }

    public function update(int $id, int $userId, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE recordatorios
             SET negocio_id=?, tipo=?, nombre=?, monto=?, fecha_vencimiento=?,
                 frecuencia=?, categoria_id=?, cuenta_id=?, subcuenta_id=?, cliente_id=?
             WHERE id=? AND usuario_id=?"
        );
        return $stmt->execute([
            $data['negocio_id']   ?: null,
            $data['tipo'],
            $data['nombre'],
            $data['monto'],
            $data['fecha_vencimiento'],
            $data['frecuencia'],
            $data['categoria_id'] ?: null,
            $data['cuenta_id']    ?: null,
            $data['subcuenta_id'] ?: null,
            $data['cliente_id']   ?: null,
            $id,
            $userId,
        ]);
    }

    /**
     * Marca como pagado/cobrado:
     * - Actualiza el saldo de cuenta (y subcuenta si aplica)
     * - Si es un cobro y tiene cliente, lo registra en pagos_clientes
     * - Si tiene frecuencia, REABRE el recordatorio con la siguiente fecha
     *   en lugar de dejarlo en estado "pagado"
     */
    public function marcarPagado(int $id, int $userId): bool {
        $rec = $this->findById($id, $userId);
        if (!$rec) return false;

        $monto   = (float)$rec['monto'];
        // "pagar" sale de la cuenta (negativo), "cobrar" entra (positivo)
        $delta   = $rec['tipo'] === 'pagar' ? -$monto : $monto;

        // --- 1. Afectar saldo de cuenta principal ---
        if ($rec['cuenta_id']) {
            $this->db->prepare("UPDATE cuentas SET saldo = saldo + ? WHERE id=?")
                     ->execute([$delta, $rec['cuenta_id']]);
        }

        // --- 2. Afectar saldo de subcuenta/bolsillo ---
        if ($rec['subcuenta_id']) {
            $this->db->prepare("UPDATE subcuentas SET saldo = saldo + ? WHERE id=?")
                     ->execute([$delta, $rec['subcuenta_id']]);
        }

        // --- 3. Si es COBRO y tiene cliente, registrar en historial de pagos del cliente ---
        if ($rec['tipo'] === 'cobrar' && $rec['cliente_id']) {
            $stmtPago = $this->db->prepare(
                "INSERT INTO pagos_clientes (cliente_id, usuario_id, negocio_id, monto, fecha, estado, descripcion)
                 VALUES (?,?,?,?,?,?,?)"
            );
            $stmtPago->execute([
                $rec['cliente_id'],
                $userId,
                $rec['negocio_id'],
                $monto,
                date('Y-m-d H:i:s'),
                'pagado',
                "Cobro de recordatorio: " . $rec['nombre']
            ]);
        }

        // --- 4. Calcular siguiente fecha si es recurrente ---
        $frecuencia = $rec['frecuencia'] ?? 'ninguna';
        if ($frecuencia !== 'ninguna') {
            $fechaActual = new DateTime($rec['fecha_vencimiento']);
            switch ($frecuencia) {
                case 'diario':   $fechaActual->modify('+1 day');   break;
                case 'semanal':  $fechaActual->modify('+1 week');  break;
                case 'mensual':  $fechaActual->modify('+1 month'); break;
            }
            $nuevaFecha = $fechaActual->format('Y-m-d H:i:s');

            // Reabre el recordatorio con la siguiente fecha (sigue pendiente)
            $stmt = $this->db->prepare(
                "UPDATE recordatorios
                 SET estado='pendiente', fecha_vencimiento=?, ultima_ejecucion=NOW()
                 WHERE id=? AND usuario_id=?"
            );
            return $stmt->execute([$nuevaFecha, $id, $userId]);
        }

        // --- 5. Sin frecuencia: marcar pagado definitivamente ---
        $stmt = $this->db->prepare(
            "UPDATE recordatorios SET estado='pagado', ultima_ejecucion=NOW() WHERE id=? AND usuario_id=?"
        );
        return $stmt->execute([$id, $userId]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM recordatorios WHERE id=? AND usuario_id=?");
        return $stmt->execute([$id, $userId]);
    }

    public function getLastInsertId(): string { return $this->db->lastInsertId(); }
}
