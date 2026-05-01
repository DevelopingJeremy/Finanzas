<?php
require_once BASE_PATH . '/app/models/Usuario.php';

/**
 * AuthController - Maneja login, registro y logout
 */
class AuthController extends BaseController {

    private Usuario $model;

    public function __construct() {
        $this->model = new Usuario();
    }

    /** GET/POST: Formulario de login */
    public function login(): void {
        // Si ya está autenticado, ir al dashboard
        if (isset($_SESSION['usuario_id'])) {
            $this->redirect('/?c=dashboard&a=index');
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $errors[] = 'Email y contraseña son requeridos.';
            } else {
                $usuario = $this->model->findByEmail($email);

                if ($usuario && $this->model->verifyPassword($password, $usuario['password'])) {
                    // Iniciar sesión
                    $_SESSION['usuario_id']     = $usuario['id'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre'];
                    $_SESSION['usuario_email']  = $usuario['email'];

                    // Regenerar ID de sesión para prevenir session fixation
                    session_regenerate_id(true);

                    $this->redirect('/?c=dashboard&a=index');
                } else {
                    $errors[] = 'Email o contraseña incorrectos.';
                }
            }
        }

        $this->renderPlain('auth/login', ['errors' => $errors]);
    }

    /** GET/POST: Formulario de registro */
    public function register(): void {
        if (isset($_SESSION['usuario_id'])) {
            $this->redirect('/?c=dashboard&a=index');
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre   = trim($_POST['nombre']   ?? '');
            $email    = trim($_POST['email']    ?? '');
            $password = $_POST['password']      ?? '';
            $confirm  = $_POST['password_confirm'] ?? '';

            // Validaciones
            if (empty($nombre))    $errors[] = 'El nombre es requerido.';
            if (empty($email))     $errors[] = 'El email es requerido.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';
            if (strlen($password) < 8) $errors[] = 'La contraseña debe tener mínimo 8 caracteres.';
            if ($password !== $confirm) $errors[] = 'Las contraseñas no coinciden.';

            if (empty($errors) && $this->model->emailExists($email)) {
                $errors[] = 'Este email ya está registrado.';
            }

            if (empty($errors)) {
                if ($this->model->create($nombre, $email, $password)) {
                    $this->flash('success', '¡Cuenta creada exitosamente! Inicia sesión.');
                    $this->redirect('/?c=auth&a=login');
                } else {
                    $errors[] = 'Error al crear la cuenta. Intente de nuevo.';
                }
            }
        }

        $this->renderPlain('auth/register', ['errors' => $errors]);
    }

    /** Logout: Destruir sesión y redirigir */
    public function logout(): void {
        $_SESSION = [];
        session_destroy();
        header('Location: /finanzas/public/?c=auth&a=login');
        exit;
    }
}
