<div class="page-header">
    <div>
        <h1>🏦 Cuentas</h1>
        <p>Gestiona tus cuentas bancarias, efectivo y ahorros</p>
    </div>
    <a href="/public/?c=cuentas&a=create" class="btn btn-primary">+ Nueva Cuenta</a>
</div>

<?php if (empty($cuentas)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">🏦</div>
            <h3>Sin cuentas creadas</h3><a href="/public/?c=cuentas&a=create" class="btn btn-primary mt-3">Crear Cuenta</a>
        </div>
    </div>
<?php else: ?>
    <div class="grid-auto">
        <?php
        $tipoIcons = ['banco' => '🏦', 'efectivo' => '💵', 'ahorro' => '💰'];
        foreach ($cuentas as $c):
            $saldo = (float) $c['saldo'];
            ?>
            <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.75rem;">
                    <div style="display:flex;align-items:center;gap:.6rem;">
                        <span style="font-size:1.5rem;"><?= $tipoIcons[$c['tipo']] ?? '💳' ?></span>
                        <div>
                            <div class="font-bold"><?= htmlspecialchars($c['nombre']) ?></div>
                            <div class="text-sm text-muted"><?= ucfirst($c['tipo']) ?> · <?= $c['moneda'] ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($c['negocio_nombre']): ?>
                    <div class="text-sm text-muted mb-2">🏢 <?= htmlspecialchars($c['negocio_nombre']) ?></div>
                <?php endif; ?>
                <div
                    style="font-size:1.5rem;font-weight:800;color:<?= $saldo >= 0 ? 'var(--green)' : 'var(--red)' ?>;margin:.5rem 0;">
                    ₡<?= number_format($saldo, 2, ',', '.') ?>
                </div>
                <div style="display:flex;gap:.5rem;margin-top:.75rem;">
                    <a href="/public/?c=cuentas&a=edit&id=<?= $c['id'] ?>" class="btn btn-secondary btn-sm">✏️ Editar</a>
                    <a href="/public/?c=subcuentas&a=index" class="btn btn-secondary btn-sm">👛 Bolsillos</a>
                    <button
                        onclick="confirmDelete('/public/?c=cuentas&a=delete&id=<?= $c['id'] ?>','<?= htmlspecialchars($c['nombre']) ?>')"
                        class="btn btn-danger btn-sm">🗑️</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>