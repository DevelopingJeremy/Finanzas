<?php
/**
 * Front Controller - Punto de entrada principal
 * Enruta todas las peticiones al controlador y acción correctos
 */

// Definir rutas base
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', '/public');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar clase Database
require_once BASE_PATH . '/config/database.php';

// Cargar BaseController
require_once BASE_PATH . '/app/controllers/BaseController.php';

// Obtener controlador y acción (sanitizados)
$controller = isset($_GET['c']) ? preg_replace('/[^a-zA-Z]/', '', $_GET['c']) : 'dashboard';
$action     = isset($_GET['a']) ? preg_replace('/[^a-zA-Z_]/', '', $_GET['a']) : 'index';

// Controladores públicos (no requieren login)
$publicControllers = ['auth'];

// Verificar autenticación
if (!in_array($controller, $publicControllers) && !isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/?c=auth&a=login');
    exit;
}

// Mapeo controlador => clase
$controllerMap = [
    'dashboard'     => 'DashboardController',
    'auth'          => 'AuthController',
    'negocios'      => 'NegocioController',
    'cuentas'       => 'CuentaController',
    'subcuentas'    => 'SubcuentaController',
    'transacciones' => 'TransaccionController',
    'categorias'    => 'CategoriaController',
    'recordatorios' => 'RecordatorioController',
    'clientes'      => 'ClienteController',
];

if (!isset($controllerMap[$controller])) {
    http_response_code(404);
    require_once BASE_PATH . '/app/views/errors/404.php';
    exit;
}

$controllerClass = $controllerMap[$controller];
$controllerFile  = BASE_PATH . '/app/controllers/' . $controllerClass . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(404);
    require_once BASE_PATH . '/app/views/errors/404.php';
    exit;
}

require_once $controllerFile;

$ctrl = new $controllerClass();

if (!method_exists($ctrl, $action)) {
    http_response_code(404);
    require_once BASE_PATH . '/app/views/errors/404.php';
    exit;
}

$ctrl->$action();
