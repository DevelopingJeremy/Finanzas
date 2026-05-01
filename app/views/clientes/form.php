<div class="page-header">
    <div>
        <h1>👤 <?= $accion ?> Cliente</h1>
    </div>
    <a href="/public/?c=clientes&a=index" class="btn btn-secondary">← Volver</a>
</div>

<div class="card" style="max-width:600px;">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">❌ <?= htmlspecialchars($errors[0]) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" class="form-control"
                    value="<?= htmlspecialchars($cliente['nombre'] ?? '') ?>" placeholder="Nombre del cliente" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="negocio_id">Negocio</label>
                <select id="negocio_id" name="negocio_id" class="form-control">
                    <option value="">— Sin negocio —</option>
                    <?php foreach ($negocios as $n): ?>
                        <option value="<?= $n['id'] ?>" <?= ($cliente['negocio_id'] ?? '') == $n['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($n['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" class="form-control"
                    value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>" placeholder="+506 8888-8888">
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                    value="<?= htmlspecialchars($cliente['email'] ?? '') ?>" placeholder="cliente@email.com">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label" for="notas">Notas</label>
            <textarea id="notas" name="notas" class="form-control"
                placeholder="Observaciones del cliente..."><?= htmlspecialchars($cliente['notas'] ?? '') ?></textarea>
        </div>
        <div style="display:flex;gap:.75rem;margin-top:1rem;">
            <button type="submit" class="btn btn-primary"><?= $accion ?> Cliente</button>
            <a href="/public/?c=clientes&a=index" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>