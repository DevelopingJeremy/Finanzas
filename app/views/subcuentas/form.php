<div class="page-header">
    <div><h1>👛 <?= $accion ?> Bolsillo</h1></div>
    <a href="/finanzas/public/?c=subcuentas&a=index" class="btn btn-secondary">← Volver</a>
</div>

<div class="card" style="max-width:580px;">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">❌ <?= htmlspecialchars($errors[0]) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label class="form-label" for="cuenta_id">Cuenta *</label>
            <select id="cuenta_id" name="cuenta_id" class="form-control" required>
                <option value="">— Seleccionar cuenta —</option>
                <?php foreach ($cuentas as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($subcuenta['cuenta_id']??'') == $c['id'] ? 'selected':'' ?>>
                    <?= htmlspecialchars($c['nombre']) ?> (<?= $c['tipo'] ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="nombre">Nombre del Bolsillo *</label>
                <input type="text" id="nombre" name="nombre" class="form-control"
                       value="<?= htmlspecialchars($subcuenta['nombre'] ?? '') ?>" placeholder="Ej: Ahorro, Gastos, Salario" required>
            </div>
            <?php if ($accion === 'Crear'): ?>
            <div class="form-group">
                <label class="form-label" for="saldo">Saldo Inicial (₡)</label>
                <input type="number" id="saldo" name="saldo" class="form-control" step="0.01" min="0"
                       value="<?= $subcuenta['saldo'] ?? '0' ?>" placeholder="0.00">
            </div>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label class="form-label" for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" class="form-control" placeholder="Ej: Para gastos del mes"><?= htmlspecialchars($subcuenta['descripcion'] ?? '') ?></textarea>
        </div>
        <div style="display:flex;gap:.75rem;margin-top:1rem;">
            <button type="submit" class="btn btn-primary"><?= $accion ?> Bolsillo</button>
            <a href="/finanzas/public/?c=subcuentas&a=index" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
