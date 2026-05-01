<div class="page-header">
    <div><h1>🏷️ Categorías</h1><p>Clasifica tus ingresos y gastos</p></div>
    <a href="/finanzas/public/?c=categorias&a=create" class="btn btn-primary">+ Nueva Categoría</a>
</div>

<?php
$ingresos = array_filter($categorias, fn($c) => $c['tipo'] === 'ingreso');
$gastos   = array_filter($categorias, fn($c) => $c['tipo'] === 'gasto');
?>

<div class="grid-2">
    <!-- Ingresos -->
    <div class="card">
        <div class="card-header"><span class="card-title">📈 Ingresos</span></div>
        <?php if (empty($ingresos)): ?>
            <div class="empty-state" style="padding:1.5rem;"><h3>Sin categorías de ingreso</h3></div>
        <?php else: ?>
            <?php foreach ($ingresos as $cat): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.55rem 0;border-bottom:1px solid var(--border);">
                <div style="display:flex;align-items:center;gap:.6rem;">
                    <span style="font-size:1.2rem;"><?= htmlspecialchars($cat['icono'] ?? '💰') ?></span>
                    <div>
                        <div class="font-600" style="font-size:.875rem;"><?= htmlspecialchars($cat['nombre']) ?></div>
                        <?php if ($cat['es_default']): ?><span class="badge badge-gray">Default</span><?php endif; ?>
                    </div>
                </div>
                <div style="display:flex;gap:.4rem;align-items:center;">
                    <span style="width:14px;height:14px;border-radius:50%;background:<?= htmlspecialchars($cat['color'] ?? '#10b981') ?>;display:inline-block;"></span>
                    <?php if (!$cat['es_default']): ?>
                    <a href="/finanzas/public/?c=categorias&a=edit&id=<?= $cat['id'] ?>" class="btn btn-secondary btn-sm">✏️</a>
                    <button onclick="confirmDelete('/finanzas/public/?c=categorias&a=delete&id=<?= $cat['id'] ?>','<?= htmlspecialchars($cat['nombre']) ?>')" class="btn btn-danger btn-sm">🗑️</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Gastos -->
    <div class="card">
        <div class="card-header"><span class="card-title">📉 Gastos</span></div>
        <?php if (empty($gastos)): ?>
            <div class="empty-state" style="padding:1.5rem;"><h3>Sin categorías de gasto</h3></div>
        <?php else: ?>
            <?php foreach ($gastos as $cat): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.55rem 0;border-bottom:1px solid var(--border);">
                <div style="display:flex;align-items:center;gap:.6rem;">
                    <span style="font-size:1.2rem;"><?= htmlspecialchars($cat['icono'] ?? '💸') ?></span>
                    <div>
                        <div class="font-600" style="font-size:.875rem;"><?= htmlspecialchars($cat['nombre']) ?></div>
                        <?php if ($cat['es_default']): ?><span class="badge badge-gray">Default</span><?php endif; ?>
                    </div>
                </div>
                <div style="display:flex;gap:.4rem;align-items:center;">
                    <span style="width:14px;height:14px;border-radius:50%;background:<?= htmlspecialchars($cat['color'] ?? '#ef4444') ?>;display:inline-block;"></span>
                    <?php if (!$cat['es_default']): ?>
                    <a href="/finanzas/public/?c=categorias&a=edit&id=<?= $cat['id'] ?>" class="btn btn-secondary btn-sm">✏️</a>
                    <button onclick="confirmDelete('/finanzas/public/?c=categorias&a=delete&id=<?= $cat['id'] ?>','<?= htmlspecialchars($cat['nombre']) ?>')" class="btn btn-danger btn-sm">🗑️</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
