<div class="page-header">
    <div>
        <h1>🏢 Negocios</h1>
        <p>Administra tus negocios y entidades</p>
    </div>
    <a href="/public/?c=negocios&a=create" class="btn btn-primary">+ Nuevo Negocio</a>
</div>

<?php if (empty($negocios)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">🏢</div>
            <h3>Sin negocios creados</h3>
            <p>Crea tu primer negocio para comenzar a gestionar tus finanzas.</p>
            <a href="/public/?c=negocios&a=create" class="btn btn-primary mt-3">Crear Negocio</a>
        </div>
    </div>
<?php else: ?>
    <div class="grid-auto">
        <?php foreach ($negocios as $n): ?>
            <div class="card" style="position:relative;">
                <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem;">
                    <div
                        style="width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,var(--green),var(--blue));display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
                        <?= $n['tipo'] === 'personal' ? '👤' : '🏢' ?>
                    </div>
                    <div>
                        <div class="font-bold" style="font-size:1rem;"><?= htmlspecialchars($n['nombre']) ?></div>
                        <span
                            class="badge <?= $n['tipo'] === 'personal' ? 'badge-blue' : 'badge-purple' ?>"><?= $n['tipo'] ?></span>
                    </div>
                </div>
                <?php if ($n['descripcion']): ?>
                    <p class="text-muted text-sm mb-3"><?= htmlspecialchars($n['descripcion']) ?></p>
                <?php endif; ?>
                <div style="display:flex;gap:.5rem;margin-top:.75rem;">
                    <a href="/public/?c=negocios&a=edit&id=<?= $n['id'] ?>" class="btn btn-secondary btn-sm">✏️ Editar</a>
                    <button
                        onclick="confirmDelete('/public/?c=negocios&a=delete&id=<?= $n['id'] ?>','<?= htmlspecialchars($n['nombre']) ?>')"
                        class="btn btn-danger btn-sm">🗑️</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>