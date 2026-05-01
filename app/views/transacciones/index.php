<?php
function fmtT(float $n): string
{
    return '₡' . number_format($n, 2, ',', '.');
}
?>
<div class="page-header">
    <div>
        <h1>💸 Transacciones</h1>
        <p>Registro completo de movimientos</p>
    </div>
    <a href="/public/?c=transacciones&a=create" class="btn btn-primary">+ Nueva</a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <form method="GET" action="/public/" style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;">
        <input type="hidden" name="c" value="transacciones">
        <input type="hidden" name="a" value="index">
        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">Negocio</label>
            <select name="negocio_id" class="form-control">
                <option value="">Todos</option>
                <?php foreach ($negocios as $n): ?>
                    <option value="<?= $n['id'] ?>" <?= ($filters['negocio_id'] ?? '') == $n['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($n['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">Cuenta</label>
            <select name="cuenta_id" class="form-control">
                <option value="">Todas</option>
                <?php foreach ($cuentas as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filters['cuenta_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:120px;">
            <label class="form-label">Tipo</label>
            <select name="tipo" class="form-control">
                <option value="">Todos</option>
                <option value="ingreso" <?= ($filters['tipo'] ?? '') === 'ingreso' ? 'selected' : '' ?>>Ingreso</option>
                <option value="gasto" <?= ($filters['tipo'] ?? '') === 'gasto' ? 'selected' : '' ?>>Gasto</option>
                <option value="transferencia" <?= ($filters['tipo'] ?? '') === 'transferencia' ? 'selected' : '' ?>>
                    Transferencia</option>
            </select>
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">Desde</label>
            <input type="date" name="fecha_desde" class="form-control"
                value="<?= htmlspecialchars($filters['fecha_desde'] ?? '') ?>">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">Hasta</label>
            <input type="date" name="fecha_hasta" class="form-control"
                value="<?= htmlspecialchars($filters['fecha_hasta'] ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-primary" style="margin-bottom:0;">Filtrar</button>
        <a href="/public/?c=transacciones&a=index" class="btn btn-secondary" style="margin-bottom:0;">Limpiar</a>
    </form>
</div>

<?php if (empty($transacciones)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">💸</div>
            <h3>Sin transacciones</h3><a href="/public/?c=transacciones&a=create" class="btn btn-primary mt-3">Crear
                Transacción</a>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Negocio</th>
                        <th>Cuenta</th>
                        <th>Categoría</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transacciones as $t): ?>
                        <?php
                        $badges = ['ingreso' => 'badge-green', 'gasto' => 'badge-red', 'transferencia' => 'badge-blue'];
                        $amtClass = $t['tipo'] === 'ingreso' ? 'amount-income' : ($t['tipo'] === 'gasto' ? 'amount-expense' : 'amount-transfer');
                        ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($t['fecha'])) ?></td>
                            <td class="td-bold"><?= htmlspecialchars($t['descripcion'] ?: '—') ?></td>
                            <td class="text-muted"><?= htmlspecialchars($t['negocio_nombre'] ?? '—') ?></td>
                            <td class="text-muted"><?= htmlspecialchars($t['cuenta_nombre'] ?? '—') ?></td>
                            <td class="text-muted"><?= htmlspecialchars($t['categoria_nombre'] ?? '—') ?></td>
                            <td><span class="badge <?= $badges[$t['tipo']] ?>"><?= $t['tipo'] ?></span></td>
                            <td class="<?= $amtClass ?>"><?= fmtT((float) $t['monto']) ?></td>
                            <td>
                                <button
                                    onclick="confirmDelete('/public/?c=transacciones&a=delete&id=<?= $t['id'] ?>','<?= htmlspecialchars($t['descripcion'] ?: 'esta transacción') ?>')"
                                    class="btn btn-danger btn-sm">🗑️</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>