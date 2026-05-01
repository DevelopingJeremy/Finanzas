<div class="page-header">
    <div>
        <h1>👥 Clientes</h1>
        <p>Gestiona tus clientes y pagos</p>
    </div>
    <a href="/public/?c=clientes&a=create" class="btn btn-primary">+ Nuevo Cliente</a>
</div>

<?php if (empty($clientes)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">👥</div>
            <h3>Sin clientes aún</h3><a href="/public/?c=clientes&a=create" class="btn btn-primary mt-3">Agregar Cliente</a>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Negocio</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Deuda Pendiente</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cl): ?>
                        <tr>
                            <td class="td-bold">👤 <?= htmlspecialchars($cl['nombre']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($cl['negocio_nombre'] ?? '—') ?></td>
                            <td class="text-muted"><?= htmlspecialchars($cl['telefono'] ?: '—') ?></td>
                            <td class="text-muted"><?= htmlspecialchars($cl['email'] ?: '—') ?></td>
                            <td>
                                <?php $deuda = (float) ($cl['deuda_pendiente'] ?? 0); ?>
                                <span class="<?= $deuda > 0 ? 'amount-expense font-bold' : 'text-muted' ?>">
                                    <?= $deuda > 0 ? '₡' . number_format($deuda, 2, ',', '.') : '₡0,00' ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                                    <a href="/public/?c=clientes&a=pagos&id=<?= $cl['id'] ?>"
                                        class="btn btn-secondary btn-sm">💰 Pagos</a>
                                    <a href="/public/?c=clientes&a=edit&id=<?= $cl['id'] ?>"
                                        class="btn btn-secondary btn-sm">✏️</a>
                                    <button
                                        onclick="confirmDelete('/public/?c=clientes&a=delete&id=<?= $cl['id'] ?>','<?= htmlspecialchars($cl['nombre']) ?>')"
                                        class="btn btn-danger btn-sm">🗑️</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>