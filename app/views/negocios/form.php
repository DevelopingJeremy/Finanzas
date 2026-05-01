<div class="page-header">
    <div>
        <h1>🏢 <?= $accion ?> Negocio</h1>
    </div>
    <a href="/public/?c=negocios&a=index" class="btn btn-secondary">← Volver</a>
</div>

<div class="card" style="max-width:600px;">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">❌ <?= htmlspecialchars($errors[0]) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label class="form-label" for="nombre">Nombre del Negocio *</label>
            <input type="text" id="nombre" name="nombre" class="form-control"
                value="<?= htmlspecialchars($negocio['nombre'] ?? '') ?>" placeholder="Ej: Bullish, Intexa, Personal"
                required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="tipo">Tipo</label>
                <select id="tipo" name="tipo" class="form-control">
                    <option value="negocio" <?= ($negocio['tipo'] ?? '') === 'negocio' ? 'selected' : '' ?>>Negocio
                    </option>
                    <option value="personal" <?= ($negocio['tipo'] ?? '') === 'personal' ? 'selected' : '' ?>>Personal
                    </option>
                </select>
            </div>
            <?php if ($accion === 'Editar'): ?>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <label class="form-check" style="margin-top:.5rem;">
                        <input type="checkbox" name="activo" value="1" <?= !empty($negocio['activo']) ? 'checked' : '' ?>>
                        Activo
                    </label>
                </div>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label class="form-label" for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" class="form-control"
                placeholder="Descripción opcional..."><?= htmlspecialchars($negocio['descripcion'] ?? '') ?></textarea>
        </div>
        <div style="display:flex;gap:.75rem;margin-top:1rem;">
            <button type="submit" class="btn btn-primary"><?= $accion ?> Negocio</button>
            <a href="/public/?c=negocios&a=index" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>