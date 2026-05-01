<?php
// Determinar página activa para el sidebar
$currentC = $_GET['c'] ?? 'dashboard';
$currentA = $_GET['a'] ?? 'index';

function isActive(string $ctrl): string
{
    global $currentC;
    return $currentC === $ctrl ? 'active' : '';
}
?>
<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <button class="sidebar-close" onclick="toggleSidebar()">✕</button>
    <div class="sidebar-brand">
        <div class="brand-icon">💰</div>
        <div>
            <div class="brand-name">GestiCash</div>
            <div class="brand-sub">Panel de control</div>
        </div>
    </div>

    <div class="sidebar-section">Principal</div>
    <ul class="sidebar-nav">
        <li>
            <a href="/public/?c=dashboard&a=index" class="<?= isActive('dashboard') ?>">
                <span class="nav-icon">📊</span> Dashboard
            </a>
        </li>
    </ul>

    <div class="sidebar-section">Estructura</div>
    <ul class="sidebar-nav">
        <li>
            <a href="/public/?c=negocios&a=index" class="<?= isActive('negocios') ?>">
                <span class="nav-icon">🏢</span> Negocios
            </a>
        </li>
        <li>
            <a href="/public/?c=cuentas&a=index" class="<?= isActive('cuentas') ?>">
                <span class="nav-icon">🏦</span> Cuentas
            </a>
        </li>
        <li>
            <a href="/public/?c=subcuentas&a=index" class="<?= isActive('subcuentas') ?>">
                <span class="nav-icon">👛</span> Bolsillos
            </a>
        </li>
        <li>
            <a href="/public/?c=categorias&a=index" class="<?= isActive('categorias') ?>">
                <span class="nav-icon">🏷️</span> Categorías
            </a>
        </li>
    </ul>

    <div class="sidebar-section">Movimientos</div>
    <ul class="sidebar-nav">
        <li>
            <a href="/public/?c=transacciones&a=index" class="<?= isActive('transacciones') ?>">
                <span class="nav-icon">💸</span> Transacciones
            </a>
        </li>
        <li>
            <a href="/public/?c=recordatorios&a=index" class="<?= isActive('recordatorios') ?>">
                <span class="nav-icon">🔔</span> Recordatorios
            </a>
        </li>
    </ul>

    <div class="sidebar-section">Clientes</div>
    <ul class="sidebar-nav">
        <li>
            <a href="/public/?c=clientes&a=index" class="<?= isActive('clientes') ?>">
                <span class="nav-icon">👥</span> Clientes
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['usuario_nombre'] ?? 'U', 0, 1)) ?>
            </div>
            <div>
                <div style="color:var(--text);font-weight:600;font-size:0.8rem;">
                    <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?>
                </div>
                <div style="font-size:0.7rem;">
                    <a href="/public/?c=auth&a=logout" style="color:var(--red);">
                        Cerrar sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- MAIN CONTENT WRAPPER -->
<div class="main-content">
    <!-- TOPBAR -->
    <header class="topbar">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
            <div class="topbar-title">
                <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard' ?>
            </div>
        </div>
        <div class="topbar-actions">
            <a href="/public/?c=transacciones&a=create" class="btn btn-primary btn-sm">
                + Nueva Transacción
            </a>
        </div>
    </header>

    <!-- FLASH MESSAGE -->
    <?php if (isset($_SESSION['flash'])): ?>
        <?php $flash = $_SESSION['flash'];
        unset($_SESSION['flash']); ?>
        <div style="padding: 0 1.5rem; padding-top:1rem;">
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
                <?php
                $icons = ['success' => '✅', 'danger' => '❌', 'warning' => '⚠️', 'info' => 'ℹ️'];
                echo $icons[$flash['type']] ?? '';
                ?>
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- PAGE CONTENT START -->
    <div class="page-content">

        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

        <script>
            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                sidebar.classList.toggle('open');
                overlay.classList.toggle('active');
            }
        </script>