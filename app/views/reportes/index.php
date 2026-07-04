<?php
// Helper: formatear moneda
if (!function_exists('fmt')) {
    function fmt(float $n): string {
        return '₡' . number_format($n, 2, ',', '.');
    }
}

$mesesNombres = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
$mesActivoNombre = $mesesNombres[$month] ?? '';

// Helpers for comparison badges
function renderBadge($variacion) {
    if ($variacion['direccion'] === 'subio') {
        return '<span class="comparison-badge badge-red">↑ ' . $variacion['porcentaje'] . '%</span>';
    } elseif ($variacion['direccion'] === 'bajo') {
        return '<span class="comparison-badge badge-green">↓ ' . $variacion['porcentaje'] . '%</span>';
    } else {
        return '<span class="comparison-badge badge-gray">~ 0%</span>';
    }
}
function renderBadgeIngresos($variacion) {
    if ($variacion['direccion'] === 'subio') {
        return '<span class="comparison-badge badge-green">↑ ' . $variacion['porcentaje'] . '%</span>';
    } elseif ($variacion['direccion'] === 'bajo') {
        return '<span class="comparison-badge badge-red">↓ ' . $variacion['porcentaje'] . '%</span>';
    } else {
        return '<span class="comparison-badge badge-gray">~ 0%</span>';
    }
}
?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div>
        <h1>📈 Reportes Financieros</h1>
        <p>Análisis del mes de <?= $mesActivoNombre ?> <?= $year ?></p>
    </div>
    <div style="display:flex;align-items:center;gap:1rem;">
        <form method="GET" action="/public/index.php" class="month-selector" style="display:flex;align-items:center;gap:0.5rem;">
            <input type="hidden" name="c" value="reportes">
            <input type="hidden" name="a" value="index">
            <select name="mes" class="form-control" style="width:auto;padding:0.25rem 0.5rem;" onchange="this.form.submit()">
                <?php foreach($mesesNombres as $num => $nom): ?>
                    <option value="<?= $num ?>" <?= $num == $month ? 'selected' : '' ?>><?= $nom ?></option>
                <?php endforeach; ?>
            </select>
            <select name="anio" class="form-control" style="width:auto;padding:0.25rem 0.5rem;" onchange="this.form.submit()">
                <?php for($y = 2020; $y <= 2030; $y++): ?>
                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </form>
    </div>
</div>

<!-- KPIs con comparativa -->
<div class="stats-grid report-section">
    <div class="stat-card">
        <div class="stat-icon">📈</div>
        <div class="stat-label">Ingresos</div>
        <div class="stat-value green"><?= fmt($totales['ingreso']) ?></div>
        <div class="stat-sub">
            <?= renderBadgeIngresos($comparativa['variacion']['ingresos']) ?> vs mes anterior
        </div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon">📉</div>
        <div class="stat-label">Gastos</div>
        <div class="stat-value red"><?= fmt($totales['gasto']) ?></div>
        <div class="stat-sub">
            <?= renderBadge($comparativa['variacion']['gastos']) ?> vs mes anterior
        </div>
    </div>
    <div class="stat-card <?= $totales['balance'] >= 0 ? '' : 'red' ?>">
        <div class="stat-icon">⚖️</div>
        <div class="stat-label">Balance</div>
        <div class="stat-value <?= $totales['balance'] >= 0 ? 'green' : 'red' ?>"><?= fmt($totales['balance']) ?></div>
        <div class="stat-sub">
            <?= renderBadgeIngresos($comparativa['variacion']['balance']) ?> vs mes anterior
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🧾</div>
        <div class="stat-label">Transacciones</div>
        <div class="stat-value"><?= $comparativa['actual']['transacciones'] ?></div>
        <div class="stat-sub">
            <?= renderBadgeIngresos($comparativa['variacion']['transacciones']) ?> vs mes anterior
        </div>
    </div>
</div>

<div class="report-grid grid-2" style="margin-top:2rem;">
    <!-- Gastos por Categoría -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📉 Gastos por Categoría</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Categoría</th><th>Monto</th><th>%</th></tr></thead>
                <tbody>
                    <?php if (empty($gastosCategoria)): ?>
                        <tr><td colspan="3" class="text-center text-muted">No hay gastos</td></tr>
                    <?php else: ?>
                        <?php foreach($gastosCategoria as $g): 
                            $pct = $totales['gasto'] > 0 ? round(($g['total'] / $totales['gasto']) * 100, 1) : 0;
                        ?>
                        <tr class="metric-row">
                            <td><?= $g['icono'] ?? '🏷️' ?> <?= htmlspecialchars($g['categoria']) ?> <span class="text-xs text-muted">(<?= $g['cantidad'] ?>)</span></td>
                            <td class="font-bold text-red"><?= fmt($g['total']) ?></td>
                            <td class="text-muted"><?= $pct ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ingresos por Categoría -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📈 Ingresos por Categoría</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Categoría</th><th>Monto</th><th>%</th></tr></thead>
                <tbody>
                    <?php if (empty($ingresosCategoria)): ?>
                        <tr><td colspan="3" class="text-center text-muted">No hay ingresos</td></tr>
                    <?php else: ?>
                        <?php foreach($ingresosCategoria as $i): 
                            $pct = $totales['ingreso'] > 0 ? round(($i['total'] / $totales['ingreso']) * 100, 1) : 0;
                        ?>
                        <tr class="metric-row">
                            <td><?= $i['icono'] ?? '🏷️' ?> <?= htmlspecialchars($i['categoria']) ?> <span class="text-xs text-muted">(<?= $i['cantidad'] ?>)</span></td>
                            <td class="font-bold text-green"><?= fmt($i['total']) ?></td>
                            <td class="text-muted"><?= $pct ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="report-grid grid-2" style="margin-top:2rem;">
    <!-- Gastos por Negocio -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">🏢 Gastos por Negocio</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Negocio</th><th>Monto</th></tr></thead>
                <tbody>
                    <?php if (empty($gastosEmpresa)): ?>
                        <tr><td colspan="2" class="text-center text-muted">No hay gastos</td></tr>
                    <?php else: ?>
                        <?php foreach($gastosEmpresa as $g): ?>
                        <tr class="metric-row">
                            <td><?= htmlspecialchars($g['empresa']) ?> <span class="text-xs text-muted">(<?= $g['cantidad'] ?>)</span></td>
                            <td class="font-bold text-red"><?= fmt($g['total']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ingresos por Negocio -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">🏢 Ingresos por Negocio</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Negocio</th><th>Monto</th></tr></thead>
                <tbody>
                    <?php if (empty($ingresosEmpresa)): ?>
                        <tr><td colspan="2" class="text-center text-muted">No hay ingresos</td></tr>
                    <?php else: ?>
                        <?php foreach($ingresosEmpresa as $i): ?>
                        <tr class="metric-row">
                            <td><?= htmlspecialchars($i['empresa']) ?> <span class="text-xs text-muted">(<?= $i['cantidad'] ?>)</span></td>
                            <td class="font-bold text-green"><?= fmt($i['total']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="report-grid grid-2" style="margin-top:2rem;">
    <!-- Gastos por Bolsillo -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">💼 Gastos por Bolsillo</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Bolsillo</th><th>Cuenta</th><th>Monto</th></tr></thead>
                <tbody>
                    <?php if (empty($gastosBolsillo)): ?>
                        <tr><td colspan="3" class="text-center text-muted">No hay gastos</td></tr>
                    <?php else: ?>
                        <?php foreach($gastosBolsillo as $g): ?>
                        <tr class="metric-row">
                            <td><?= htmlspecialchars($g['bolsillo']) ?></td>
                            <td class="text-muted text-sm"><?= htmlspecialchars($g['cuenta_nombre']) ?></td>
                            <td class="font-bold text-red"><?= fmt($g['total']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ingresos por Bolsillo -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">💼 Ingresos por Bolsillo</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Bolsillo</th><th>Cuenta</th><th>Monto</th></tr></thead>
                <tbody>
                    <?php if (empty($ingresosBolsillo)): ?>
                        <tr><td colspan="3" class="text-center text-muted">No hay ingresos</td></tr>
                    <?php else: ?>
                        <?php foreach($ingresosBolsillo as $i): ?>
                        <tr class="metric-row">
                            <td><?= htmlspecialchars($i['bolsillo']) ?></td>
                            <td class="text-muted text-sm"><?= htmlspecialchars($i['cuenta_nombre']) ?></td>
                            <td class="font-bold text-green"><?= fmt($i['total']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="report-grid grid-2" style="margin-top:2rem; margin-bottom:2rem;">
    <!-- Totales por Cuenta Bancaria -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">🏦 Movimientos por Cuenta</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Cuenta</th><th>Ingresos</th><th>Gastos</th></tr></thead>
                <tbody>
                    <?php if (empty($totalesCuenta)): ?>
                        <tr><td colspan="3" class="text-center text-muted">No hay movimientos</td></tr>
                    <?php else: ?>
                        <?php foreach($totalesCuenta as $c): ?>
                        <tr class="metric-row">
                            <td>
                                <?= htmlspecialchars($c['cuenta_nombre']) ?><br>
                                <span class="text-xs text-muted"><?= htmlspecialchars($c['cuenta_tipo']) ?></span>
                            </td>
                            <td class="font-bold text-green"><?= fmt($c['ingresos']) ?></td>
                            <td class="font-bold text-red"><?= fmt($c['gastos']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Distribución Actual de Dinero (Saldos) -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">💰 Distribución Actual del Dinero</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Cuenta</th><th>Total</th><th>Disponible</th><th>En Bolsillos</th></tr></thead>
                <tbody>
                    <?php if (empty($distribucion)): ?>
                        <tr><td colspan="4" class="text-center text-muted">No hay cuentas</td></tr>
                    <?php else: ?>
                        <?php foreach($distribucion as $d): ?>
                        <tr class="metric-row">
                            <td><?= htmlspecialchars($d['cuenta']) ?></td>
                            <td class="font-bold"><?= fmt($d['saldo_cuenta']) ?></td>
                            <td class="text-green font-bold"><?= fmt($d['disponible']) ?></td>
                            <td class="text-muted text-sm"><?= fmt($d['en_bolsillos']) ?> (<?= $d['num_bolsillos'] ?>)</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
