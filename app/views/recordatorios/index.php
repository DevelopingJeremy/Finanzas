<?php
function fmtR(float $n): string { return '₡' . number_format($n, 2, ',', '.'); }

$frecuenciaLabel = [
    'ninguna' => '—',
    'diario'  => '🔁 Diario',
    'semanal' => '🔁 Semanal',
    'mensual' => '🔁 Mensual',
];
$hoy = new DateTime();
?>
<div class="page-header">
    <div><h1>🔔 Recordatorios</h1><p>Pagos y cobros programados</p></div>
    <a href="/finanzas/public/?c=recordatorios&a=create" class="btn btn-primary">+ Nuevo</a>
</div>

<?php if (empty($recordatorios)): ?>
<div class="card"><div class="empty-state"><div class="empty-icon">🔔</div><h3>Sin recordatorios</h3><a href="/finanzas/public/?c=recordatorios&a=create" class="btn btn-primary mt-3">Crear Recordatorio</a></div></div>
<?php else: ?>
<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Monto</th>
                <th>Próx. Vencimiento</th>
                <th>Frecuencia</th>
                <th>Cuenta / Bolsillo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr></thead>
            <tbody>
            <?php foreach ($recordatorios as $r): ?>
            <?php
                $fecha   = new DateTime($r['fecha_vencimiento']);
                $vencido = $fecha < $hoy && $r['estado'] === 'pendiente';
                $esPagado = $r['estado'] === 'pagado';
            ?>
            <tr>
                <td class="td-bold">
                    <?= htmlspecialchars($r['nombre']) ?>
                    <?php if ($r['cliente_nombre']): ?>
                        <br><small class="text-muted">👤 <?= htmlspecialchars($r['cliente_nombre']) ?></small>
                    <?php endif; ?>
                </td>
                <td><span class="badge <?= $r['tipo']==='pagar' ? 'badge-red' : 'badge-green' ?>"><?= $r['tipo'] ?></span></td>
                <td class="font-bold"><?= fmtR((float)$r['monto']) ?></td>
                <td <?= $vencido ? 'style="color:var(--red);"' : '' ?>>
                    <?= $fecha->format('d/m/Y') ?>
                    <?php if ($vencido): ?><span class="badge badge-red ml-2">VENCIDO</span><?php endif; ?>
                </td>
                <td class="text-muted"><?= $frecuenciaLabel[$r['frecuencia'] ?? 'ninguna'] ?? $r['frecuencia'] ?></td>
                <td>
                    <?php if ($r['cuenta_nombre'] ?? null): ?>
                        <span style="font-size:.85em;"><?= htmlspecialchars($r['cuenta_nombre']) ?></span>
                        <?php if ($r['subcuenta_nombre'] ?? null): ?>
                            <br><small class="text-muted">🗂 <?= htmlspecialchars($r['subcuenta_nombre']) ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    $estadoMap = ['pendiente'=>'badge-yellow','pagado'=>'badge-green','vencido'=>'badge-red'];
                    ?>
                    <span class="badge <?= $estadoMap[$r['estado']] ?? 'badge-gray' ?>"><?= $r['estado'] ?></span>
                </td>
                <td>
                    <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                        <?php if ($r['estado'] === 'pendiente'): ?>
                        <a href="/finanzas/public/?c=recordatorios&a=marcarPagado&id=<?= $r['id'] ?>"
                           class="btn btn-secondary btn-sm"
                           onclick="return confirm('¿Confirmar pago/cobro? Se afectará el saldo de la cuenta.')">
                           ✓ <?= $r['tipo'] === 'pagar' ? 'Pagar' : 'Cobrar' ?>
                        </a>
                        <?php endif; ?>
                        <a href="/finanzas/public/?c=recordatorios&a=edit&id=<?= $r['id'] ?>" class="btn btn-secondary btn-sm">✏️</a>
                        <button onclick="confirmDelete('/finanzas/public/?c=recordatorios&a=delete&id=<?= $r['id'] ?>','<?= htmlspecialchars($r['nombre']) ?>')" class="btn btn-danger btn-sm">🗑️</button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
