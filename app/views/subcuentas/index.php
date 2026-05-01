<div class="page-header">
    <div><h1>👛 Bolsillos (Subcuentas)</h1><p>Distribuye el dinero dentro de tus cuentas</p></div>
    <a href="/finanzas/public/?c=subcuentas&a=create" class="btn btn-primary">+ Nuevo Bolsillo</a>
</div>

<?php if (empty($subcuentas)): ?>
<div class="card"><div class="empty-state"><div class="empty-icon">👛</div><h3>Sin bolsillos creados</h3><p>Crea bolsillos para dividir el dinero de tus cuentas.</p><a href="/finanzas/public/?c=subcuentas&a=create" class="btn btn-primary mt-3">Crear Bolsillo</a></div></div>
<?php else: ?>
<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr><th>Bolsillo</th><th>Cuenta</th><th>Descripción</th><th>Saldo</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($subcuentas as $s): ?>
            <tr>
                <td class="td-bold">👛 <?= htmlspecialchars($s['nombre']) ?></td>
                <td>🏦 <?= htmlspecialchars($s['cuenta_nombre']) ?></td>
                <td class="text-muted"><?= htmlspecialchars($s['descripcion'] ?: '—') ?></td>
                <td class="<?= (float)$s['saldo'] >= 0 ? 'amount-income' : 'amount-expense' ?>">
                    ₡<?= number_format((float)$s['saldo'], 2, ',', '.') ?>
                </td>
                <td>
                    <div style="display:flex;gap:.4rem;">
                        <a href="/finanzas/public/?c=subcuentas&a=edit&id=<?= $s['id'] ?>" class="btn btn-secondary btn-sm">✏️</a>
                        <button onclick="confirmDelete('/finanzas/public/?c=subcuentas&a=delete&id=<?= $s['id'] ?>','<?= htmlspecialchars($s['nombre']) ?>')" class="btn btn-danger btn-sm">🗑️</button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
