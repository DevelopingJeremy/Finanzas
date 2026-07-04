<?php
// Helper: formatear moneda
function fmt(float $n): string
{
    return '₡' . number_format($n, 2, ',', '.');
}
?>
<?php
$mesesNombres = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
$mesActivoNombre = $mesesNombres[$month] ?? '';
?>
<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div>
        <h1>📊 Dashboard</h1>
        <p>Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? '') ?> — <?= date('d/m/Y') ?></p>
    </div>
    <div style="display:flex;align-items:center;gap:1rem;">
        <form method="GET" action="/public/index.php" class="month-selector" style="display:flex;align-items:center;gap:0.5rem;">
            <!-- Selectores de mes y año -->
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
        <a href="/public/?c=transacciones&a=create" class="btn btn-primary">+ Nueva Transacción</a>
    </div>
</div>

<!-- KPIs principales -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-label">Saldo Total</div>
        <div class="stat-value green"><?= fmt($saldoTotal) ?></div>
        <div class="stat-sub"><?= count($cuentas) ?> cuenta(s) activa(s)</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📈</div>
        <div class="stat-label">Ingresos</div>
        <div class="stat-value green"><?= fmt($totales['ingreso']) ?></div>
        <div class="stat-sub">Mes de <?= $mesActivoNombre ?> <?= $year ?></div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon">📉</div>
        <div class="stat-label">Gastos</div>
        <div class="stat-value red"><?= fmt($totales['gasto']) ?></div>
        <div class="stat-sub">Mes de <?= $mesActivoNombre ?> <?= $year ?></div>
    </div>
    <div class="stat-card <?= $balance >= 0 ? '' : 'red' ?>">
        <div class="stat-icon">⚖️</div>
        <div class="stat-label">Balance</div>
        <div class="stat-value <?= $balance >= 0 ? 'green' : 'red' ?>"><?= fmt($balance) ?></div>
        <div class="stat-sub">Mes de <?= $mesActivoNombre ?> <?= $year ?></div>
    </div>
</div>

<div class="grid-2">
    <!-- Saldos por cuenta -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">🏦 Cuentas</span>
            <a href="/public/?c=cuentas&a=index" class="btn btn-secondary btn-sm">Ver todas</a>
        </div>
        <?php if (empty($cuentas)): ?>
            <div class="empty-state">
                <div class="empty-icon">🏦</div>
                <h3>Sin cuentas creadas</h3>
            </div>
        <?php else: ?>
            <?php foreach ($cuentas as $c): ?>
                <div class="account-card" style="padding:1rem; border:1px solid var(--border); border-radius:8px; margin-bottom:1rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div class="font-600" style="font-size:1.1rem;"><?= htmlspecialchars($c['nombre']) ?></div>
                            <div class="text-muted text-sm"><?= htmlspecialchars($c['tipo']) ?> — <?= htmlspecialchars($c['moneda']) ?></div>
                        </div>
                        <div class="font-bold <?= $c['saldo'] >= 0 ? 'text-green' : 'text-red' ?>" style="font-size:1.1rem;">
                            <?= fmt((float) $c['saldo']) ?>
                        </div>
                    </div>
                    <?php if (!empty($c['bolsillos'])): ?>
                        <div class="account-pockets" style="margin-top:1rem; padding-top:1rem; border-top:1px dashed var(--border);">
                            <div class="text-sm font-600 text-muted mb-2">Bolsillos:</div>
                            <?php foreach ($c['bolsillos'] as $b): ?>
                                <div style="display:flex;justify-content:space-between; margin-bottom:0.25rem; font-size:0.9rem;">
                                    <span>• <?= htmlspecialchars($b['nombre']) ?></span>
                                    <span class="font-bold text-muted"><?= fmt((float) $b['saldo']) ?></span>
                                </div>
                            <?php endforeach; ?>
                            <div class="account-available" style="display:flex;justify-content:space-between; margin-top:0.5rem; font-size:0.95rem; font-weight:600;">
                                <span>Disponible:</span>
                                <span class="text-green"><?= fmt((float) $c['disponible']) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Próximos recordatorios -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">🔔 Próximos Vencimientos (7 días)</span>
            <a href="/public/?c=recordatorios&a=index" class="btn btn-secondary btn-sm">Ver todos</a>
        </div>
        <?php if (empty($proximos)): ?>
            <div class="empty-state">
                <div class="empty-icon">🎉</div>
                <h3>Sin vencimientos próximos</h3>
            </div>
        <?php else: ?>
            <?php foreach ($proximos as $r): ?>
                <div
                    style="display:flex;justify-content:space-between;align-items:center;padding:.6rem 0;border-bottom:1px solid var(--border);">
                    <div>
                        <div class="font-600" style="font-size:.875rem;"><?= htmlspecialchars($r['nombre']) ?></div>
                        <div class="text-sm">
                            <span
                                class="badge <?= $r['tipo'] === 'pagar' ? 'badge-red' : 'badge-green' ?>"><?= $r['tipo'] ?></span>
                            &nbsp;<?= date('d/m/Y', strtotime($r['fecha_vencimiento'])) ?>
                        </div>
                    </div>
                    <div>
                        <div class="font-bold"><?= fmt((float) $r['monto']) ?></div>
                        <a href="/public/?c=recordatorios&a=marcarPagado&id=<?= $r['id'] ?>" class="btn btn-sm btn-secondary">✓
                            Pagar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Últimas transacciones -->
<div class="card mt-4">
    <div class="card-header">
        <span class="card-title">💸 Últimas Transacciones</span>
        <a href="/public/?c=transacciones&a=index" class="btn btn-secondary btn-sm">Ver todas</a>
    </div>
    <?php if (empty($ultimas)): ?>
        <div class="empty-state">
            <div class="empty-icon">💸</div>
            <h3>Sin transacciones aún</h3>
            <a href="/public/?c=transacciones&a=create" class="btn btn-primary mt-3">Crear primera transacción</a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Cuenta</th>
                        <th>Negocio</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimas as $t): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($t['fecha'])) ?></td>
                            <td class="td-bold"><?= htmlspecialchars($t['descripcion'] ?: '—') ?></td>
                            <td><?= htmlspecialchars($t['cuenta_nombre'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($t['negocio_nombre'] ?? '—') ?></td>
                            <td>
                                <?php
                                $badges = ['ingreso' => 'badge-green', 'gasto' => 'badge-red', 'transferencia' => 'badge-blue'];
                                $badgeClass = $badges[$t['tipo']] ?? 'badge-gray';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= $t['tipo'] ?></span>
                            </td>
                            <td
                                class="<?= $t['tipo'] === 'ingreso' ? 'amount-income' : ($t['tipo'] === 'gasto' ? 'amount-expense' : 'amount-transfer') ?>">
                                <?= fmt((float) $t['monto']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Totales por Negocio -->
<?php if (!empty($porNegocio)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <span class="card-title">🏢 Resumen por Negocio</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Negocio</th>
                        <th>Tipo</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($porNegocio as $row): ?>
                        <tr>
                            <td class="td-bold"><?= htmlspecialchars($row['nombre']) ?></td>
                            <td><span
                                    class="badge <?= $row['tipo'] === 'ingreso' ? 'badge-green' : ($row['tipo'] === 'gasto' ? 'badge-red' : 'badge-blue') ?>"><?= $row['tipo'] ?></span>
                            </td>
                            <td class="font-bold"><?= fmt((float) $row['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>