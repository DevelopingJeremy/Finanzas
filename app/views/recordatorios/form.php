<div class="page-header">
    <div><h1>🔔 <?= $accion ?> Recordatorio</h1></div>
    <a href="/finanzas/public/?c=recordatorios&a=index" class="btn btn-secondary">← Volver</a>
</div>

<div class="card" style="max-width:650px;">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">❌ <?= htmlspecialchars($errors[0]) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" class="form-control"
                       value="<?= htmlspecialchars($recordatorio['nombre'] ?? '') ?>" placeholder="Ej: Alquiler, Cuota tarjeta" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="tipo">Tipo *</label>
                <select id="tipo" name="tipo" class="form-control" required>
                    <option value="pagar"  <?= ($recordatorio['tipo']??'') === 'pagar'  ? 'selected':'' ?>>💳 Pagar</option>
                    <option value="cobrar" <?= ($recordatorio['tipo']??'') === 'cobrar' ? 'selected':'' ?>>💰 Cobrar</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="monto">Monto (₡) *</label>
                <input type="number" id="monto" name="monto" class="form-control" step="0.01" min="0"
                       value="<?= htmlspecialchars($recordatorio['monto'] ?? '') ?>" placeholder="0.00" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="fecha_vencimiento">Fecha Vencimiento *</label>
                <input type="datetime-local" id="fecha_vencimiento" name="fecha_vencimiento" class="form-control"
                       value="<?= htmlspecialchars(isset($recordatorio['fecha_vencimiento']) ? date('Y-m-d\TH:i', strtotime($recordatorio['fecha_vencimiento'])) : '') ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="frecuencia">Frecuencia</label>
                <select id="frecuencia" name="frecuencia" class="form-control">
                    <option value="ninguna"  <?= ($recordatorio['frecuencia']??'ninguna') === 'ninguna'  ? 'selected':'' ?>>Sin repetición</option>
                    <option value="diario"   <?= ($recordatorio['frecuencia']??'') === 'diario'   ? 'selected':'' ?>>Diario</option>
                    <option value="semanal"  <?= ($recordatorio['frecuencia']??'') === 'semanal'  ? 'selected':'' ?>>Semanal</option>
                    <option value="mensual"  <?= ($recordatorio['frecuencia']??'') === 'mensual'  ? 'selected':'' ?>>Mensual</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="negocio_id">Negocio</label>
                <select id="negocio_id" name="negocio_id" class="form-control">
                    <option value="">— Sin negocio —</option>
                    <?php foreach ($negocios as $n): ?>
                    <option value="<?= $n['id'] ?>" <?= ($recordatorio['negocio_id']??'') == $n['id'] ? 'selected':'' ?>><?= htmlspecialchars($n['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="categoria_id">Categoría</label>
                <select id="categoria_id" name="categoria_id" class="form-control">
                    <option value="">— Sin categoría —</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($recordatorio['categoria_id']??'') == $cat['id'] ? 'selected':'' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="cuenta_id">Cuenta</label>
                <select id="cuenta_id" name="cuenta_id" class="form-control">
                    <option value="">— Sin cuenta —</option>
                    <?php foreach ($cuentas as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($recordatorio['cuenta_id']??'') == $c['id'] ? 'selected':'' ?>><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div style="display:flex;gap:.75rem;margin-top:1rem;">
            <button type="submit" class="btn btn-primary"><?= $accion ?> Recordatorio</button>
            <a href="/finanzas/public/?c=recordatorios&a=index" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
