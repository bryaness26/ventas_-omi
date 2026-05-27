<?php
// Módulo de Cabecera y Navegación del Sistema Ñomi
require_once __DIR__ . '/functions.php';
check_auth();

$tasa_bcv = obtener_tasa_bcv();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ñomi - Gestión Financiera</title>
    <!-- Favicon / Estilos -->
    <link rel="icon" type="image/png" href="img/logo.png">
    <link rel="stylesheet" href="css/styles.css">
    <!-- Iconos Phosphor Icons para estética premium -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>

    <!-- Header Móvil -->
    <header class="mobile-header">
        <div class="mobile-brand">
            <img src="img/logo.png" alt="Ñomi" style="height: 35px; width: auto;" onerror="this.src='https://placehold.co/100x100/d81a67/ffffff?text=%C3%91omi'">
            <span>Ñomi</span>
        </div>
        <button class="menu-toggle" id="menuToggle">
            <i class="ph-bold ph-list"></i>
        </button>
    </header>

    <div class="dashboard-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <!-- Fallback a placeholder elegante si la imagen no existe -->
                <img class="sidebar-logo" src="img/logo.png" alt="Ñomi" onerror="this.src='https://placehold.co/100x100/d81a67/ffffff?text=%C3%91'">
                <span>Ñomi</span>
            </div>

            <div class="sidebar-user">
                <div class="user-role"><?php echo strtoupper(sanitize($_SESSION['user_rol'])); ?></div>
                <div class="user-name"><?php echo sanitize($_SESSION['user_nombre']); ?></div>
            </div>

            <nav style="margin-bottom: 20px;">
                <ul class="sidebar-nav">
                    <li class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                        <a href="dashboard.php">
                            <i class="ph-bold ph-chart-pie-slice"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="<?php echo $current_page == 'ventas.php' ? 'active' : ''; ?>">
                        <a href="ventas.php">
                            <i class="ph-bold ph-shopping-cart-simple"></i>
                            <span>Ventas</span>
                        </a>
                    </li>
                    <li class="<?php echo $current_page == 'gastos.php' ? 'active' : ''; ?>">
                        <a href="gastos.php">
                            <i class="ph-bold ph-receipt"></i>
                            <span>Gastos</span>
                        </a>
                    </li>
                    <li class="<?php echo $current_page == 'usuarios.php' ? 'active' : ''; ?>">
                        <a href="usuarios.php">
                            <i class="ph-bold ph-user-plus"></i>
                            <span>Crear Usuario</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Widget BCV & Calculadora Integrada -->
            <div class="bcv-widget" style="margin-top: 20px; margin-bottom: 20px;">
                <div class="bcv-header">
                    <h4><i class="ph-bold ph-bank"></i> Dólar BCV</h4>
                    <span style="font-size: 11px; opacity: 0.85;">Oficial</span>
                </div>
                <div class="bcv-rate">
                    Bs. <?php echo number_format($tasa_bcv, 2, ',', '.'); ?>
                </div>
                
                <div class="bcv-calc-title">Convertidor</div>
                <div class="bcv-calc-inputs">
                    <input type="hidden" id="tasaBcv" value="<?php echo $tasa_bcv; ?>">
                    <div class="bcv-calc-group bcv-calc-usd">
                        <input type="number" id="calcUsd" placeholder="Dólares ($)" step="any">
                    </div>
                    <div class="bcv-calc-group bcv-calc-ves">
                        <input type="number" id="calcVes" placeholder="Bolívares (Bs)" step="any">
                    </div>
                </div>
            </div>

            <div class="sidebar-footer">
                <a href="logout.php">
                    <i class="ph-bold ph-sign-out"></i>
                    <span>Cerrar Sesión</span>
                </a>
            </div>
        </aside>

        <!-- Contenido principal -->
        <main class="main-content">
