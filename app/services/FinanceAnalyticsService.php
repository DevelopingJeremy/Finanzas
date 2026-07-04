<?php
/**
 * FinanceAnalyticsService - Servicio centralizado de estadísticas financieras
 * 
 * Toda la aplicación debe consumir este servicio para obtener métricas,
 * en lugar de recalcular valores en cada controlador o pantalla.
 * 
 * Usa zona horaria America/Costa_Rica para todos los cálculos de fechas.
 */

require_once BASE_PATH . '/app/helpers/timezone.php';

class FinanceAnalyticsService {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // =========================================================================
    // SALDOS REALES (no dependen de mes)
    // =========================================================================

    /**
     * Saldo total real: suma de todas las cuentas activas
     */
    public function getSaldoTotal(int $userId): float {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(saldo), 0) AS total FROM cuentas WHERE usuario_id = ? AND activo = 1"
        );
        $stmt->execute([$userId]);
        return (float)$stmt->fetchColumn();
    }

    /**
     * Cuentas con sus bolsillos y el saldo disponible (saldo - sum bolsillos)
     */
    public function getCuentasConBolsillos(int $userId): array {
        // Obtener cuentas activas
        $stmt = $this->db->prepare(
            "SELECT c.*, n.nombre AS negocio_nombre
             FROM cuentas c
             LEFT JOIN negocios n ON c.negocio_id = n.id
             WHERE c.usuario_id = ? AND c.activo = 1
             ORDER BY c.nombre"
        );
        $stmt->execute([$userId]);
        $cuentas = $stmt->fetchAll();

        // Para cada cuenta, obtener sus bolsillos
        $stmtBolsillos = $this->db->prepare(
            "SELECT * FROM subcuentas WHERE cuenta_id = ? ORDER BY nombre"
        );

        foreach ($cuentas as &$cuenta) {
            $stmtBolsillos->execute([$cuenta['id']]);
            $bolsillos = $stmtBolsillos->fetchAll();
            $sumaBolsillos = array_sum(array_column($bolsillos, 'saldo'));
            $cuenta['bolsillos'] = $bolsillos;
            $cuenta['suma_bolsillos'] = $sumaBolsillos;
            $cuenta['disponible'] = (float)$cuenta['saldo'] - $sumaBolsillos;
        }

        return $cuentas;
    }

    // =========================================================================
    // TOTALES POR MES
    // =========================================================================

    /**
     * Totales de ingresos, egresos y balance de un mes específico
     */
    public function getTotalesMes(int $userId, int $year, int $month): array {
        $range = crMonthRange($year, $month);
        $stmt = $this->db->prepare(
            "SELECT tipo, COALESCE(SUM(monto), 0) AS total
             FROM transacciones
             WHERE usuario_id = ? AND estado = 'completado'
               AND fecha >= ? AND fecha <= ?
             GROUP BY tipo"
        );
        $stmt->execute([$userId, $range['inicio'], $range['fin']]);
        $rows = $stmt->fetchAll();

        $result = ['ingreso' => 0.0, 'gasto' => 0.0, 'transferencia' => 0.0];
        foreach ($rows as $r) {
            $result[$r['tipo']] = (float)$r['total'];
        }
        $result['balance'] = $result['ingreso'] - $result['gasto'];

        return $result;
    }

    // =========================================================================
    // POR CATEGORÍA
    // =========================================================================

    /**
     * Gastos agrupados por categoría para un mes
     */
    public function getGastosPorCategoria(int $userId, int $year, int $month): array {
        return $this->getTotalesPorCategoriaYTipo($userId, $year, $month, 'gasto');
    }

    /**
     * Ingresos agrupados por categoría para un mes
     */
    public function getIngresosPorCategoria(int $userId, int $year, int $month): array {
        return $this->getTotalesPorCategoriaYTipo($userId, $year, $month, 'ingreso');
    }

    private function getTotalesPorCategoriaYTipo(int $userId, int $year, int $month, string $tipo): array {
        $range = crMonthRange($year, $month);
        $stmt = $this->db->prepare(
            "SELECT COALESCE(cat.nombre, 'Sin categoría') AS categoria,
                    cat.color, cat.icono,
                    SUM(t.monto) AS total,
                    COUNT(t.id) AS cantidad
             FROM transacciones t
             LEFT JOIN categorias cat ON t.categoria_id = cat.id
             WHERE t.usuario_id = ? AND t.tipo = ? AND t.estado = 'completado'
               AND t.fecha >= ? AND t.fecha <= ?
             GROUP BY t.categoria_id
             ORDER BY total DESC"
        );
        $stmt->execute([$userId, $tipo, $range['inicio'], $range['fin']]);
        return $stmt->fetchAll();
    }

    // =========================================================================
    // POR EMPRESA/NEGOCIO
    // =========================================================================

    /**
     * Gastos agrupados por negocio/empresa para un mes
     */
    public function getGastosPorEmpresa(int $userId, int $year, int $month): array {
        return $this->getTotalesPorEmpresaYTipo($userId, $year, $month, 'gasto');
    }

    /**
     * Ingresos agrupados por negocio/empresa para un mes
     */
    public function getIngresosPorEmpresa(int $userId, int $year, int $month): array {
        return $this->getTotalesPorEmpresaYTipo($userId, $year, $month, 'ingreso');
    }

    private function getTotalesPorEmpresaYTipo(int $userId, int $year, int $month, string $tipo): array {
        $range = crMonthRange($year, $month);
        $stmt = $this->db->prepare(
            "SELECT COALESCE(n.nombre, 'Personal / Sin negocio') AS empresa,
                    SUM(t.monto) AS total,
                    COUNT(t.id) AS cantidad
             FROM transacciones t
             LEFT JOIN negocios n ON t.negocio_id = n.id
             WHERE t.usuario_id = ? AND t.tipo = ? AND t.estado = 'completado'
               AND t.fecha >= ? AND t.fecha <= ?
             GROUP BY t.negocio_id
             ORDER BY total DESC"
        );
        $stmt->execute([$userId, $tipo, $range['inicio'], $range['fin']]);
        return $stmt->fetchAll();
    }

    // =========================================================================
    // POR BOLSILLO (SUBCUENTA)
    // =========================================================================

    /**
     * Gastos agrupados por bolsillo para un mes
     */
    public function getGastosPorBolsillo(int $userId, int $year, int $month): array {
        return $this->getTotalesPorBolsilloYTipo($userId, $year, $month, 'gasto');
    }

    /**
     * Ingresos agrupados por bolsillo para un mes
     */
    public function getIngresosPorBolsillo(int $userId, int $year, int $month): array {
        return $this->getTotalesPorBolsilloYTipo($userId, $year, $month, 'ingreso');
    }

    private function getTotalesPorBolsilloYTipo(int $userId, int $year, int $month, string $tipo): array {
        $range = crMonthRange($year, $month);
        $stmt = $this->db->prepare(
            "SELECT COALESCE(sc.nombre, 'Sin bolsillo') AS bolsillo,
                    c.nombre AS cuenta_nombre,
                    SUM(d.monto) AS total,
                    COUNT(DISTINCT t.id) AS cantidad
             FROM transacciones t
             JOIN distribuciones d ON d.transaccion_id = t.id
             JOIN subcuentas sc ON d.subcuenta_id = sc.id
             JOIN cuentas c ON sc.cuenta_id = c.id
             WHERE t.usuario_id = ? AND t.tipo = ? AND t.estado = 'completado'
               AND t.fecha >= ? AND t.fecha <= ?
             GROUP BY d.subcuenta_id
             ORDER BY total DESC"
        );
        $stmt->execute([$userId, $tipo, $range['inicio'], $range['fin']]);
        return $stmt->fetchAll();
    }

    // =========================================================================
    // TOTALES POR CUENTA BANCARIA
    // =========================================================================

    /**
     * Ingresos y gastos por cuenta bancaria para un mes
     */
    public function getTotalesPorCuenta(int $userId, int $year, int $month): array {
        $range = crMonthRange($year, $month);
        $stmt = $this->db->prepare(
            "SELECT c.nombre AS cuenta_nombre, c.tipo AS cuenta_tipo, c.moneda,
                    SUM(CASE WHEN t.tipo = 'ingreso' THEN t.monto ELSE 0 END) AS ingresos,
                    SUM(CASE WHEN t.tipo = 'gasto'   THEN t.monto ELSE 0 END) AS gastos,
                    COUNT(t.id) AS cantidad
             FROM transacciones t
             JOIN cuentas c ON t.cuenta_id = c.id
             WHERE t.usuario_id = ? AND t.estado = 'completado'
               AND t.fecha >= ? AND t.fecha <= ?
             GROUP BY t.cuenta_id
             ORDER BY c.nombre"
        );
        $stmt->execute([$userId, $range['inicio'], $range['fin']]);
        return $stmt->fetchAll();
    }

    // =========================================================================
    // DISTRIBUCIÓN DEL DINERO
    // =========================================================================

    /**
     * Distribución actual del dinero entre cuentas y bolsillos (saldos actuales)
     */
    public function getDistribucionDinero(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT c.nombre AS cuenta, c.saldo AS saldo_cuenta,
                    COALESCE(SUM(sc.saldo), 0) AS en_bolsillos,
                    c.saldo - COALESCE(SUM(sc.saldo), 0) AS disponible,
                    COUNT(sc.id) AS num_bolsillos
             FROM cuentas c
             LEFT JOIN subcuentas sc ON sc.cuenta_id = c.id
             WHERE c.usuario_id = ? AND c.activo = 1
             GROUP BY c.id
             ORDER BY c.nombre"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // =========================================================================
    // CANTIDAD DE TRANSACCIONES
    // =========================================================================

    /**
     * Número total de transacciones en un mes
     */
    public function getCantidadTransacciones(int $userId, int $year, int $month): int {
        $range = crMonthRange($year, $month);
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM transacciones
             WHERE usuario_id = ? AND estado = 'completado'
               AND fecha >= ? AND fecha <= ?"
        );
        $stmt->execute([$userId, $range['inicio'], $range['fin']]);
        return (int)$stmt->fetchColumn();
    }

    // =========================================================================
    // COMPARACIÓN CON MES ANTERIOR
    // =========================================================================

    /**
     * Comparación de métricas entre el mes seleccionado y el mes anterior
     */
    public function getComparativaMesAnterior(int $userId, int $year, int $month): array {
        // Mes actual
        $actual = $this->getTotalesMes($userId, $year, $month);
        $cantActual = $this->getCantidadTransacciones($userId, $year, $month);

        // Mes anterior
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }
        $anterior = $this->getTotalesMes($userId, $prevYear, $prevMonth);
        $cantAnterior = $this->getCantidadTransacciones($userId, $prevYear, $prevMonth);

        return [
            'actual' => [
                'ingresos'       => $actual['ingreso'],
                'gastos'         => $actual['gasto'],
                'balance'        => $actual['balance'],
                'transacciones'  => $cantActual,
            ],
            'anterior' => [
                'ingresos'       => $anterior['ingreso'],
                'gastos'         => $anterior['gasto'],
                'balance'        => $anterior['balance'],
                'transacciones'  => $cantAnterior,
                'mes'            => nombreMes($prevMonth),
                'year'           => $prevYear,
            ],
            'variacion' => [
                'ingresos'      => $this->calcVariacion($anterior['ingreso'], $actual['ingreso']),
                'gastos'        => $this->calcVariacion($anterior['gasto'], $actual['gasto']),
                'balance'       => $this->calcVariacion($anterior['balance'], $actual['balance']),
                'transacciones' => $this->calcVariacion($cantAnterior, $cantActual),
            ],
        ];
    }

    /**
     * Calcula la variación porcentual entre dos valores
     */
    private function calcVariacion(float $anterior, float $actual): array {
        if ($anterior == 0 && $actual == 0) {
            return ['porcentaje' => 0, 'direccion' => 'igual'];
        }
        if ($anterior == 0) {
            return ['porcentaje' => 100, 'direccion' => 'subio'];
        }
        $porcentaje = (($actual - $anterior) / abs($anterior)) * 100;
        $direccion = $porcentaje > 0 ? 'subio' : ($porcentaje < 0 ? 'bajo' : 'igual');
        return ['porcentaje' => round(abs($porcentaje), 1), 'direccion' => $direccion];
    }

    // =========================================================================
    // ÚLTIMAS TRANSACCIONES
    // =========================================================================

    /**
     * Últimas transacciones de un período (o globales si no se pasa mes)
     */
    public function getUltimasTransacciones(int $userId, int $limit = 10, ?int $year = null, ?int $month = null): array {
        if ($year && $month) {
            $range = crMonthRange($year, $month);
            $stmt = $this->db->prepare(
                "SELECT t.*, c.nombre AS cuenta_nombre, n.nombre AS negocio_nombre,
                        cat.nombre AS categoria_nombre
                 FROM transacciones t
                 LEFT JOIN cuentas c ON t.cuenta_id = c.id
                 LEFT JOIN negocios n ON t.negocio_id = n.id
                 LEFT JOIN categorias cat ON t.categoria_id = cat.id
                 WHERE t.usuario_id = ?
                   AND t.fecha >= ? AND t.fecha <= ?
                 ORDER BY t.fecha DESC LIMIT ?"
            );
            $stmt->execute([$userId, $range['inicio'], $range['fin'], $limit]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT t.*, c.nombre AS cuenta_nombre, n.nombre AS negocio_nombre,
                        cat.nombre AS categoria_nombre
                 FROM transacciones t
                 LEFT JOIN cuentas c ON t.cuenta_id = c.id
                 LEFT JOIN negocios n ON t.negocio_id = n.id
                 LEFT JOIN categorias cat ON t.categoria_id = cat.id
                 WHERE t.usuario_id = ?
                 ORDER BY t.fecha DESC LIMIT ?"
            );
            $stmt->execute([$userId, $limit]);
        }
        return $stmt->fetchAll();
    }

    // =========================================================================
    // RESUMEN POR NEGOCIO (para dashboard)
    // =========================================================================

    /**
     * Totales por negocio para un mes
     */
    public function getTotalesPorNegocioMes(int $userId, int $year, int $month): array {
        $range = crMonthRange($year, $month);
        $stmt = $this->db->prepare(
            "SELECT COALESCE(n.nombre, '🏠 Personal/Sin Negocio') AS nombre,
                    t.tipo, SUM(t.monto) AS total
             FROM transacciones t
             LEFT JOIN negocios n ON t.negocio_id = n.id
             WHERE t.usuario_id = ? AND t.estado = 'completado'
               AND t.fecha >= ? AND t.fecha <= ?
             GROUP BY t.negocio_id, t.tipo
             ORDER BY n.nombre"
        );
        $stmt->execute([$userId, $range['inicio'], $range['fin']]);
        return $stmt->fetchAll();
    }

    // =========================================================================
    // RECORDATORIOS PRÓXIMOS (usando zona CR)
    // =========================================================================

    /**
     * Recordatorios pendientes dentro de los próximos N días (zona CR)
     */
    public function getRecordatoriosProximos(int $userId, int $dias = 7): array {
        $ahora = crNow();
        $limite = clone $ahora;
        $limite->modify("+{$dias} days");

        $stmt = $this->db->prepare(
            "SELECT * FROM recordatorios
             WHERE usuario_id = ? AND estado = 'pendiente'
               AND fecha_vencimiento BETWEEN ? AND ?
             ORDER BY fecha_vencimiento ASC"
        );
        $stmt->execute([$userId, $ahora->format('Y-m-d H:i:s'), $limite->format('Y-m-d H:i:s')]);
        return $stmt->fetchAll();
    }
}
