<div class="page-header">
    <div><h1>🏷️ <?= $accion ?> Categoría</h1></div>
    <a href="/finanzas/public/?c=categorias&a=index" class="btn btn-secondary">← Volver</a>
</div>

<div class="card" style="max-width:520px;">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">❌ <?= htmlspecialchars($errors[0]) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" class="form-control"
                       value="<?= htmlspecialchars($categoria['nombre'] ?? '') ?>" placeholder="Ej: Alimentación" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="tipo">Tipo *</label>
                <select id="tipo" name="tipo" class="form-control" required>
                    <option value="ingreso" <?= ($categoria['tipo']??'') === 'ingreso' ? 'selected':'' ?>>Ingreso</option>
                    <option value="gasto"   <?= ($categoria['tipo']??'') === 'gasto'   ? 'selected':'' ?>>Gasto</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="icono">Emoji / Icono</label>
                <input type="text" id="icono" name="icono" class="form-control"
                       value="<?= htmlspecialchars($categoria['icono'] ?? '💰') ?>" placeholder="💰">
                <div class="form-hint">Usa un emoji como icono</div>
            </div>
            <div class="form-group">
                <label class="form-label" for="color">Color</label>
                <input type="color" id="color" name="color" class="form-control" style="height:42px;padding:4px;"
                       value="<?= htmlspecialchars($categoria['color'] ?? '#10b981') ?>">
            </div>
        </div>
        <div style="display:flex;gap:.75rem;margin-top:1rem;">
            <button type="submit" class="btn btn-primary"><?= $accion ?> Categoría</button>
            <a href="/finanzas/public/?c=categorias&a=index" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
