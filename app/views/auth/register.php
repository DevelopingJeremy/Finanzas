<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta | FinanzasApp</title>
    <link rel="stylesheet" href="/public/assets/css/app.css">
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="logo-icon">💰</div>
                <h1>Crear Cuenta</h1>
                <p>Comienza a gestionar tus finanzas hoy</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    ❌ <?= htmlspecialchars($errors[0]) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/public/?c=auth&a=register" novalidate>
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Tu nombre"
                        value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="tu@email.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="password">Contraseña</label>
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="Mínimo 8 caracteres" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password_confirm">Confirmar</label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                            placeholder="Repite la contraseña" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-full"
                    style="margin-top:0.5rem;justify-content:center;padding:.75rem;">
                    Crear Cuenta →
                </button>
            </form>

            <p class="text-center text-muted mt-3" style="font-size:0.85rem;">
                ¿Ya tienes cuenta?
                <a href="/public/?c=auth&a=login" class="text-green font-600">Inicia sesión</a>
            </p>
        </div>
    </div>
    <script src="/public/assets/js/app.js"></script>
</body>

</html>