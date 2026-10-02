<?php
/**
 * BaseController - Clase base para todos los controladores
 * Provee métodos comunes: render, redirect, flash messages, auth check, mes activo
 */

// Incluir helper de zona horaria
require_once BASE_PATH . '/app/helpers/timezone.php';

class BaseController {

    /**
     * Renderiza una vista envuelta en el layout principal
     * @param string $view  Ruta relativa dentro de app/views/ (ej: 'negocios/index')
     * @param array  $data  Variables a extraer en la vista
     * @param string $pageTitle Título de la página
     */
    protected function render(string $view, array $data = [], string $pageTitle = 'Finanzas'): void {
        // Inyectar mes activo en todas las vistas
        $data['mesActivo'] = $this->getMesActivo();
        extract($data);
        require_once BASE_PATH . '/app/views/layouts/header.php';
        require_once BASE_PATH . '/app/views/layouts/sidebar.php';
        require_once BASE_PATH . '/app/views/' . $view . '.php';
        require_once BASE_PATH . '/app/views/layouts/footer.php';
    }

    /**
     * Renderiza una vista SIN layout (para auth pages)
     */
    protected function renderPlain(string $view, array $data = []): void {
        extract($data);
        require_once BASE_PATH . '/app/views/' . $view . '.php';
    }

    /** Redirige a una URL relativa al BASE_URL */
    protected function redirect(string $path): void {
        header('Location: ' . BASE_URL . $path);
        exit;
    }

    /** Guarda un mensaje flash en la sesión */
    protected function flash(string $type, string $message): void {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /** Recupera y elimina el mensaje flash */
    protected function getFlash(): ?array {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    /** Verifica que el usuario esté autenticado */
    protected function requireAuth(): void {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/?c=auth&a=login');
        }
    }

    /** ID del usuario en sesión */
    protected function userId(): int {
        return (int)($_SESSION['usuario_id'] ?? 0);
    }

    /** Sanitiza una cadena de texto */
    protected function clean(string $str): string {
        return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
    }

    /** Valida que campos requeridos no estén vacíos */
    protected function required(array $fields, array $data): array {
        $errors = [];
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $errors[] = "El campo '{$field}' es requerido.";
            }
        }
        return $errors;
    }

    // =========================================================================
    // MES ACTIVO (Global)
    // =========================================================================

    /**
     * Obtiene el mes activo desde GET params o sesión.
     * Si se reciben ?mes=X&anio=Y, actualiza la sesión.
     * Si no hay nada, usa el mes/año actual de Costa Rica.
     * @return array ['month' => int, 'year' => int]
     */
    protected function getMesActivo(): array {
        // Si vienen params GET, actualizar sesión
        if (isset($_GET['mes']) && isset($_GET['anio'])) {
            $month = max(1, min(12, (int)$_GET['mes']));
            $year  = max(2020, min(2099, (int)$_GET['anio']));
            $_SESSION['mes_activo'] = ['month' => $month, 'year' => $year];
        }

        // Si no hay sesión, usar mes actual de Costa Rica
        if (!isset($_SESSION['mes_activo'])) {
            $_SESSION['mes_activo'] = [
                'month' => crMonth(),
                'year'  => crYear(),
            ];
        }

        return $_SESSION['mes_activo'];
    }

    /**
     * Establece el mes activo programáticamente
     */
    protected function setMesActivo(int $year, int $month): void {
        $_SESSION['mes_activo'] = ['month' => $month, 'year' => $year];
    }
}
