<?php
/**
 * Model: Recordatorio
 * 
 * Al marcar como pagado/cobrado:
 * 1. Crea una transacción automática con los datos del recordatorio
 * 2. Actualiza saldos de cuenta y subcuenta
 * 3. Si es recurrente, avanza la fecha de vencimiento
 * 4. Si no es recurrente, marca como pagado
 */

require_once BASE_PATH . '/app/helpers/timezone.php';

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
        $ahora = crNow();
        $limite = clone $ahora;
        $limite->modify("+{$dias} days");

        $stmt = $this->db->prepare(
            "SELECT * FROM recordatorios
             WHERE usuario_id=? AND estado='pendiente'
               AND fecha_vencimiento BETWEEN ? AND ?
             ORDER BY fecha_vencimiento ASC"
        );
        $stmt->execute([$userId, $ahora->format('Y-m-d H:i:s'), $limite->format('Y-m-d H:i:s')]);
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
     * Marca como pagado/cobrado con creación automática de transacción:
     * 
     * Paso 1: Crea transacción con los datos exactos del recordatorio
     * Paso 2: Actualiza saldos de cuenta y subcuenta
     * Paso 3: Si es cobro con cliente, registra pago_cliente
     * Paso 4: Si recurrente → avanza fecha; si no → marca pagado
     */
    public function marcarPagado(int $id, int $userId): bool {
        $rec = $this->findById($id, $userId);
        if (!$rec) return false;

        $monto = (float)$rec['monto'];
        // "pagar" = gasto (sale dinero), "cobrar" = ingreso (entra dinero)
        $tipoTransaccion = $rec['tipo'] === 'pagar' ? 'gasto' : 'ingreso';
        $delta = $rec['tipo'] === 'pagar' ? -$monto : $monto;
        $ahoraCR = crNowStr();

        // --- PASO 1: Crear transacción automática ---
        $stmtTrans = $this->db->prepare(
            "INSERT INTO transacciones
                (usuario_id, negocio_id, cuenta_id, tipo, monto, fecha,
                 descripcion, categoria_id, estado, es_recurrente, recordatorio_id)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)"
        );
        $stmtTrans->execute([
            $userId,
            $rec['negocio_id'] ?: null,
            $rec['cuenta_id']  ?: null,
            $tipoTransaccion,
            $monto,
            $rec['fecha_vencimiento'],  // Fecha de ejecución = fecha de vencimiento
            'Recordatorio: ' . $rec['nombre'],
            $rec['categoria_id'] ?: null,
            'completado',
            ($rec['frecuencia'] ?? 'ninguna') !== 'ninguna' ? 1 : 0,
            $id, // vincular con recordatorio
        ]);

        // --- PASO 2: Afectar saldo de cuenta principal ---
        if ($rec['cuenta_id']) {
            $this->db->prepare("UPDATE cuentas SET saldo = saldo + ? WHERE id=?")
                     ->execute([$delta, $rec['cuenta_id']]);
        }

        // --- PASO 2b: Afectar saldo de subcuenta/bolsillo ---
        if ($rec['subcuenta_id']) {
            $this->db->prepare("UPDATE subcuentas SET saldo = saldo + ? WHERE id=?")
                     ->execute([$delta, $rec['subcuenta_id']]);
        }

        // --- PASO 3: Si es COBRO y tiene cliente, registrar pago_cliente ---
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
                $ahoraCR,
                'pagado',
                "Cobro de recordatorio: " . $rec['nombre']
            ]);
        }

        // --- PASO 4: Calcular siguiente fecha si es recurrente ---
        $frecuencia = $rec['frecuencia'] ?? 'ninguna';
        if ($frecuencia !== 'ninguna') {
            $fechaActual = new DateTime($rec['fecha_vencimiento'], crTimezone());
            switch ($frecuencia) {
                case 'diario':   $fechaActual->modify('+1 day');   break;
                case 'semanal':  $fechaActual->modify('+1 week');  break;
                case 'mensual':  $fechaActual->modify('+1 month'); break;
                case 'anual':    $fechaActual->modify('+1 year');  break;
            }
            $nuevaFecha = $fechaActual->format('Y-m-d H:i:s');

            // Reabre el recordatorio con la siguiente fecha (sigue pendiente)
            $stmt = $this->db->prepare(
                "UPDATE recordatorios
                 SET estado='pendiente', fecha_vencimiento=?, ultima_ejecucion=?
                 WHERE id=? AND usuario_id=?"
            );
            return $stmt->execute([$nuevaFecha, $ahoraCR, $id, $userId]);
        }

        // --- PASO 5: Sin frecuencia: marcar pagado definitivamente ---
        $stmt = $this->db->prepare(
            "UPDATE recordatorios SET estado='pagado', ultima_ejecucion=? WHERE id=? AND usuario_id=?"
        );
        return $stmt->execute([$ahoraCR, $id, $userId]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM recordatorios WHERE id=? AND usuario_id=?");
        return $stmt->execute([$id, $userId]);
    }

    public function getLastInsertId(): string { return $this->db->lastInsertId(); }
}
