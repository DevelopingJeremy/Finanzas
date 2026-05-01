<?php
/**
 * BaseController - Clase base para todos los controladores
 * Provee métodos comunes: render, redirect, flash messages, auth check
 */
class BaseController {

    /**
     * Renderiza una vista envuelta en el layout principal
     * @param string $view  Ruta relativa dentro de app/views/ (ej: 'negocios/index')
     * @param array  $data  Variables a extraer en la vista
     * @param string $pageTitle Título de la página
     */
    protected function render(string $view, array $data = [], string $pageTitle = 'Finanzas'): void {
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
}
