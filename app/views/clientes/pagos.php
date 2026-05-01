<?php
function fmtP(float $n): string { return '₡' . number_format($n, 2, ',', '.'); }
?>
<div class="page-header">
    <div>
        <h1>💰 Pagos de <?= htmlspecialchars($cliente['nombre']) ?></h1>
        <p><a href="/finanzas/public/?c=clientes&a=index" class="text-muted">← Clientes</a></p>
    </div>
</div>

<div class="grid-2">
    <!-- Nuevo pago -->
    <div class="card">
        <div class="card-header"><span class="card-title">Registrar Pago</span></div>
        <form method="POST" action="/finanzas/public/?c=clientes&a=crearPago&cliente_id=<?= $cliente['id'] ?>">
            <div class="form-group">
                <label class="form-label" for="monto">Monto (₡) *</label>
                <input type="number" id="monto" name="monto" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="fecha">Fecha *</label>
                <input type="datetime-local" id="fecha" name="fecha" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="estado">Estado</label>
                <select id="estado" name="estado" class="form-control">
                    <option value="pendiente">Pendiente</option>
                    <option value="pagado">Pagado</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="negocio_id">Negocio</label>
                <select id="negocio_id" name="negocio_id" class="form-control">
                    <option value="">— Sin negocio —</option>
                    <?php foreach ($negocios as $n): ?>
                    <option value="<?= $n['id'] ?>"><?= htmlspecialchars($n['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" class="form-control" rows="2" placeholder="Concepto del pago..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">💾 Registrar Pago</button>
        </form>
    </div>

    <!-- Historial -->
    <div class="card">
        <div class="card-header"><span class="card-title">Historial de Pagos</span></div>
        <?php if (empty($pagos)): ?>
            <div class="empty-state" style="padding:1.5rem;"><h3>Sin pagos registrados</h3></div>
        <?php else: ?>
            <?php foreach ($pagos as $p): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.65rem 0;border-bottom:1px solid var(--border);">
                <div>
                    <div class="font-600" style="font-size:.875rem;"><?= fmtP((float)$p['monto']) ?></div>
                    <div class="text-sm text-muted"><?= date('d/m/Y', strtotime($p['fecha'])) ?> · <?= htmlspecialchars($p['descripcion'] ?: '—') ?></div>
                </div>
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <span class="badge <?= $p['estado']==='pagado' ? 'badge-green' : 'badge-yellow' ?>"><?= $p['estado'] ?></span>
                    <?php if ($p['estado'] === 'pendiente'): ?>
                    <a href="/finanzas/public/?c=clientes&a=marcarPago&id=<?= $p['id'] ?>&cliente_id=<?= $cliente['id'] ?>" class="btn btn-secondary btn-sm">✓</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
