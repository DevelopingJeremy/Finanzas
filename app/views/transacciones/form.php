<div class="page-header">
    <div><h1>💸 Nueva Transacción</h1></div>
    <a href="/finanzas/public/?c=transacciones&a=index" class="btn btn-secondary">← Volver</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">❌ <?= htmlspecialchars($errors[0]) ?></div>
<?php endif; ?>

<form method="POST" action="/finanzas/public/?c=transacciones&a=create" id="form-transaccion">
<div class="grid-2">
    <!-- Columna izquierda -->
    <div>
        <div class="card">
            <div class="card-header"><span class="card-title">Datos de la Transacción</span></div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="tipo">Tipo *</label>
                    <select id="tipo" name="tipo" class="form-control" onchange="onTipoChange(this)" required>
                        <option value="ingreso">📈 Ingreso</option>
                        <option value="gasto">📉 Gasto</option>
                        <option value="transferencia">🔄 Transferencia</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="monto">Monto (₡) *</label>
                    <input type="number" id="monto" name="monto" class="form-control" step="0.01" min="0.01"
                           placeholder="0.00" oninput="updateDistTotal()" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="fecha">Fecha *</label>
                    <input type="datetime-local" id="fecha" name="fecha" class="form-control"
                           value="<?= date('Y-m-d\TH:i') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="categoria_id">Categoría</label>
                    <select id="categoria_id" name="categoria_id" class="form-control">
                        <option value="">— Sin categoría —</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['icono'].' '.$cat['nombre']) ?> (<?= $cat['tipo'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="negocio_id">Negocio *</label>
                    <select id="negocio_id" name="negocio_id" class="form-control" required>
                        <option value="">— Seleccionar —</option>
                        <?php foreach ($negocios as $n): ?>
                        <option value="<?= $n['id'] ?>"><?= htmlspecialchars($n['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="cuenta_id">Cuenta Origen *</label>
                    <select id="cuenta_id" name="cuenta_id" class="form-control" onchange="onCuentaChange(this)" required>
                        <option value="">— Seleccionar —</option>
                        <?php foreach ($cuentas as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?> (₡<?= number_format((float)$c['saldo'],0,',','.') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Solo para transferencias -->
            <div id="cuenta-destino-wrap" style="display:none;">
                <div class="form-group">
                    <label class="form-label" for="cuenta_destino_id">Cuenta Destino</label>
                    <select id="cuenta_destino_id" name="cuenta_destino_id" class="form-control">
                        <option value="">— Seleccionar —</option>
                        <?php foreach ($cuentas as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" class="form-control" rows="3" placeholder="Detalle de la transacción..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">💾 Guardar Transacción</button>
        </div>
    </div>

    <!-- Columna derecha: Distribución -->
    <div id="distribucion-section">
        <div class="card">
            <div class="card-header">
                <span class="card-title">👛 Distribución en Bolsillos</span>
                <button type="button" class="btn btn-secondary btn-sm" onclick="addDistRow()">+ Agregar</button>
            </div>
            <p class="text-muted text-sm mb-3">Opcional: divide el monto entre tus bolsillos (subcuentas).</p>
            <div id="dist-container"></div>
            <div id="dist-total-indicator" class="text-muted text-sm"></div>
        </div>
    </div>
</div>
</form>
