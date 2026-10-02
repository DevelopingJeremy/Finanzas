<div class="page-header">
    <div>
        <h1>💸 <?= !empty($transaccion['id']) ? 'Editar' : 'Nueva' ?> Transacción</h1>
    </div>
    <a href="/public/?c=transacciones&a=index" class="btn btn-secondary">← Volver</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">❌ <?= htmlspecialchars($errors[0]) ?></div>
<?php endif; ?>

<?php
$currentTipo = $_POST['tipo'] ?? ($_GET['tipo'] ?? ($transaccion['tipo'] ?? 'gasto'));
?>

<form method="POST" action="/public/?c=transacciones&a=create" id="form-transaccion">
    <div class="grid-2">
        <!-- Columna izquierda -->
        <div>
            <div class="card">
                <div class="card-header"><span class="card-title">Datos de la Transacción</span></div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="tipo">Tipo *</label>
                        <select id="tipo" name="tipo" class="form-control" onchange="onTipoChange(this)" required>
                            <option value="gasto" <?= $currentTipo === 'gasto' ? 'selected' : '' ?>>📉 Gasto</option>
                            <option value="ingreso" <?= $currentTipo === 'ingreso' ? 'selected' : '' ?>>📈 Ingreso</option>
                            <option value="transferencia" <?= $currentTipo === 'transferencia' ? 'selected' : '' ?>>🔄 Transferencia</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="monto">Monto (₡) *</label>
                        <input type="number" id="monto" name="monto" class="form-control" step="0.01" min="0.01"
                            value="<?= htmlspecialchars($_POST['monto'] ?? '') ?>"
                            placeholder="0.00" oninput="updateDistTotal(); updateTransferSummary();" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="fecha">Fecha *</label>
                        <input type="datetime-local" id="fecha" name="fecha" class="form-control"
                            value="<?= htmlspecialchars($_POST['fecha'] ?? date('Y-m-d\TH:i')) ?>" required>
                    </div>
                    <div class="form-group" id="categoria-wrap">
                        <label class="form-label" for="categoria_id">Categoría</label>
                        <select id="categoria_id" name="categoria_id" class="form-control">
                            <option value="">— Sin categoría —</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($_POST['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['icono'] . ' ' . $cat['nombre']) ?> (<?= $cat['tipo'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="negocio_id">Negocio (Opcional)</label>
                        <select id="negocio_id" name="negocio_id" class="form-control">
                            <option value="">— Ninguno / Personal —</option>
                            <?php foreach ($negocios as $n): ?>
                                <option value="<?= $n['id'] ?>" <?= ($_POST['negocio_id'] ?? '') == $n['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($n['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="cuenta_id" id="lbl_cuenta_origen">Cuenta Origen *</label>
                        <select id="cuenta_id" name="cuenta_id" class="form-control" onchange="onCuentaChange(this)" required>
                            <option value="">— Seleccionar —</option>
                            <?php foreach ($cuentas as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($_POST['cuenta_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nombre']) ?> (₡<?= number_format((float) $c['saldo'], 0, ',', '.') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Bolsillo Origen / Bolsillo Directo -->
                <div class="form-group" id="subcuenta-origen-wrap">
                    <label class="form-label" for="subcuenta_id" id="lbl_subcuenta_origen">Bolsillo (Opcional)</label>
                    <select id="subcuenta_id" name="subcuenta_id" class="form-control" onchange="updateTransferSummary()">
                        <option value="">— Saldo Principal (Cuenta Normal) —</option>
                    </select>
                </div>

                <!-- Bloque Destino (Solo Transferencias) -->
                <div id="transferencia-destino-block" style="display:none; background: var(--bg-hover, rgba(255,255,255,0.03)); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px dashed var(--border);">
                    <div style="font-weight: 600; margin-bottom: 0.75rem; color: var(--blue, #3b82f6);">🎯 Destino de la Transferencia</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="cuenta_destino_id">Cuenta Destino *</label>
                            <select id="cuenta_destino_id" name="cuenta_destino_id" class="form-control" onchange="onCuentaDestinoChange(this)">
                                <option value="">— Seleccionar —</option>
                                <?php foreach ($cuentas as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= ($_POST['cuenta_destino_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['nombre']) ?> (₡<?= number_format((float) $c['saldo'], 0, ',', '.') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="subcuenta_destino_id">Bolsillo Destino</label>
                            <select id="subcuenta_destino_id" name="subcuenta_destino_id" class="form-control" onchange="updateTransferSummary()">
                                <option value="">— Saldo Principal (Cuenta Normal) —</option>
                            </select>
                        </div>
                    </div>
                    <div id="transfer-summary-note" class="text-sm text-muted" style="margin-top: 0.25rem;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" class="form-control" rows="3"
                        placeholder="Detalle de la transacción..."><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">💾 Guardar Transacción</button>
            </div>
        </div>

        <!-- Columna derecha: Distribución Múltiple (Opcional para Ingresos/Gastos) -->
        <div id="distribucion-section">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">👛 División en Múltiples Bolsillos</span>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addDistRow()">+ Agregar</button>
                </div>
                <p class="text-muted text-sm mb-3">Opcional: Si deseas dividir el monto entre varios bolsillos a la vez.</p>
                <div id="dist-container"></div>
                <div id="dist-total-indicator" class="text-muted text-sm"></div>
            </div>
        </div>
    </div>
</form>

<script>
    // Inicialización de datos de subcuentas precargadas
    window.ALL_SUBCUENTAS = <?= json_encode($subcuentas ?? []) ?>;
    window.PRE_ORIGEN_SUBCUENTA = "<?= htmlspecialchars($_POST['subcuenta_id'] ?? '') ?>";
    window.PRE_DESTINO_SUBCUENTA = "<?= htmlspecialchars($_POST['subcuenta_destino_id'] ?? '') ?>";
</script>