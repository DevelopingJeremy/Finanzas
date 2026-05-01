<div class="page-header">
    <div><h1>🏦 <?= $accion ?> Cuenta</h1></div>
    <a href="/finanzas/public/?c=cuentas&a=index" class="btn btn-secondary">← Volver</a>
</div>

<div class="card" style="max-width:600px;">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">❌ <?= htmlspecialchars($errors[0]) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label class="form-label" for="nombre">Nombre de la Cuenta *</label>
            <input type="text" id="nombre" name="nombre" class="form-control"
                   value="<?= htmlspecialchars($cuenta['nombre'] ?? '') ?>" placeholder="Ej: BAC Colones, Efectivo, BNCR" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="tipo">Tipo</label>
                <select id="tipo" name="tipo" class="form-control">
                    <option value="banco"   <?= ($cuenta['tipo']??'')  === 'banco'   ? 'selected':'' ?>>Banco</option>
                    <option value="efectivo"<?= ($cuenta['tipo']??'')  === 'efectivo' ? 'selected':'' ?>>Efectivo</option>
                    <option value="ahorro"  <?= ($cuenta['tipo']??'')  === 'ahorro'   ? 'selected':'' ?>>Ahorro</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="moneda">Moneda</label>
                <select id="moneda" name="moneda" class="form-control">
                    <option value="CRC" <?= ($cuenta['moneda']??'CRC') === 'CRC' ? 'selected':'' ?>>₡ Colones (CRC)</option>
                    <option value="USD" <?= ($cuenta['moneda']??'')    === 'USD' ? 'selected':'' ?>>$ Dólares (USD)</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="negocio_id">Negocio (opcional)</label>
                <select id="negocio_id" name="negocio_id" class="form-control">
                    <option value="">— Personal / Sin negocio —</option>
                    <?php foreach ($negocios as $n): ?>
                    <option value="<?= $n['id'] ?>" <?= ($cuenta['negocio_id']??'') == $n['id'] ? 'selected':'' ?>><?= htmlspecialchars($n['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($accion === 'Crear'): ?>
            <div class="form-group">
                <label class="form-label" for="saldo">Saldo Inicial (₡)</label>
                <input type="number" id="saldo" name="saldo" class="form-control" step="0.01" min="0"
                       value="<?= $cuenta['saldo'] ?? '0' ?>" placeholder="0.00">
            </div>
            <?php endif; ?>
        </div>
        <?php if ($accion === 'Editar'): ?>
        <div class="form-group">
            <label class="form-check"><input type="checkbox" name="activo" value="1" <?= !empty($cuenta['activo']) ? 'checked' : '' ?>> Cuenta activa</label>
        </div>
        <?php endif; ?>
        <div style="display:flex;gap:.75rem;margin-top:1rem;">
            <button type="submit" class="btn btn-primary"><?= $accion ?> Cuenta</button>
            <a href="/finanzas/public/?c=cuentas&a=index" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
