<?php
// Dashboard Principal de Ñomi
require_once __DIR__ . '/includes/header.php';

// Inicializar variables de sumatoria
$total_ventas = 0.00;
$total_gastos = 0.00;

try {
    // 1. Obtener la suma total de ventas
    $stmt = $pdo->query("SELECT SUM(monto_total) AS total FROM ventas");
    $res = $stmt->fetch();
    if ($res && $res['total']) {
        $total_ventas = floatval($res['total']);
    }

    // 2. Obtener la suma total de gastos
    $stmt = $pdo->query("SELECT SUM(monto_total) AS total FROM gastos");
    $res = $stmt->fetch();
    if ($res && $res['total']) {
        $total_gastos = floatval($res['total']);
    }

    // 3. Obtener las últimas 5 ventas
    $stmt = $pdo->query("SELECT v.*, u.nombre AS vendedor FROM ventas v JOIN usuarios u ON v.usuario_id = u.id ORDER BY v.fecha_venta DESC, v.id DESC LIMIT 5");
    $ultimas_ventas = $stmt->fetchAll();

    // 4. Obtener las últimas 5 gastos
    $stmt = $pdo->query("SELECT g.*, u.nombre AS comprador FROM gastos g JOIN usuarios u ON g.usuario_id = u.id ORDER BY g.fecha_gasto DESC, g.id DESC LIMIT 5");
    $ultimos_gastos = $stmt->fetchAll();

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al obtener las estadísticas: " . $e->getMessage() . "</div>";
}

$balance_neto = $total_ventas - $total_gastos;
$total_ventas_ves = $total_ventas * $tasa_bcv;
?>

<div style="margin-bottom: 32px;">
    <h2 style="font-size: 32px; color: var(--dark); font-weight: 800; display: flex; align-items: center; gap: 12px;">
        <i class="ph-bold ph-chart-line-up" style="color: var(--primary);"></i>
        <span>Panel Financiero Ñomi</span>
    </h2>
    <p style="color: var(--dark-light); opacity: 0.8; font-size: 15px;">
        Control en tiempo real de las ventas de fresas con crema y chocolate.
    </p>
</div>

<!-- Grid de KPIs Premium -->
<div class="kpi-grid">
    <!-- Ventas Totales USD -->
    <div class="kpi-card kpi-primary">
        <div class="kpi-title">Ventas Totales ($)</div>
        <div class="kpi-value">$<?php echo number_format($total_ventas, 2, ',', '.'); ?></div>
        <div class="kpi-sub">Ingresos registrados en USD</div>
    </div>

    <!-- Ventas en Bolívares (BCV) -->
    <div class="kpi-card kpi-pistacho">
        <div class="kpi-title">Ingresos en Bs (BCV)</div>
        <div class="kpi-value">Bs. <?php echo number_format($total_ventas_ves, 2, ',', '.'); ?></div>
        <div class="kpi-sub">Calculado a tasa oficial de Bs. <?php echo number_format($tasa_bcv, 2, ',', '.'); ?></div>
    </div>

    <!-- Gastos Totales -->
    <div class="kpi-card kpi-secondary">
        <div class="kpi-title">Gastos Totales</div>
        <div class="kpi-value">$<?php echo number_format($total_gastos, 2, ',', '.'); ?></div>
        <div class="kpi-sub">Egresos registrados en USD</div>
    </div>

    <!-- Balance Neto -->
    <div class="kpi-card kpi-dark">
        <div class="kpi-title">Balance Neto</div>
        <div class="kpi-value" style="color: <?php echo $balance_neto >= 0 ? 'var(--dark)' : '#e02424'; ?>">
            $<?php echo number_format($balance_neto, 2, ',', '.'); ?>
        </div>
        <div class="kpi-sub">Ventas menos Gastos registrados</div>
    </div>
</div>

<!-- Tablas de Resumen Recientes (Grid de 2 Columnas) -->
<div class="grid-2col">
    <!-- Columna Izquierda: Últimas Ventas -->
    <div class="panel-card">
        <div class="panel-header">
            <h3 class="panel-title">Últimas Ventas Registradas</h3>
            <a href="ventas.php" class="btn-secondary" style="font-size: 13px; padding: 6px 12px; text-decoration: none; display: flex; align-items: center; gap: 6px;">
                <span>Ver Todas</span>
                <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Producto</th>
                        <th>Cant.</th>
                        <th>Total ($)</th>
                        <th>Método</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ultimas_ventas)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: rgba(57,31,0,0.5); padding: 30px;">
                                No hay ventas registradas aún. ¡Agrega tu primera venta!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ultimas_ventas as $venta): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($venta['fecha_venta'])); ?></td>
                                <td><strong><?php echo sanitize($venta['cliente']); ?></strong></td>
                                <td>
                                    <span class="badge <?php echo $venta['producto'] == 'Pequeña' ? 'badge-small' : 'badge-large'; ?>">
                                        <?php echo sanitize($venta['producto']); ?>
                                    </span>
                                </td>
                                <td><?php echo $venta['cantidad']; ?></td>
                                <td><strong>$<?php echo number_format($venta['monto_total'], 2, ',', '.'); ?></strong></td>
                                <td>
                                    <?php 
                                    $metodo = $venta['metodo_pago'];
                                    $badge_class = 'badge-pm';
                                    if ($metodo === 'Efectivo') $badge_class = 'badge-ef';
                                    if ($metodo === 'Transferencia') $badge_class = 'badge-tf';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo sanitize($metodo); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Columna Derecha: Últimos Gastos -->
    <div class="panel-card">
        <div class="panel-header">
            <h3 class="panel-title">Últimos Gastos</h3>
            <a href="gastos.php" class="btn-secondary" style="font-size: 13px; padding: 6px 12px; text-decoration: none; display: flex; align-items: center; gap: 6px;">
                <span>Ver Todos</span>
                <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cant.</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ultimos_gastos)): ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: rgba(57,31,0,0.5); padding: 30px;">
                                Sin gastos recientes.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ultimos_gastos as $gasto): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600;"><?php echo sanitize($gasto['producto_comprado']); ?></div>
                                    <div style="font-size: 11px; opacity: 0.6;"><?php echo date('d/m/Y', strtotime($gasto['fecha_gasto'])); ?></div>
                                </td>
                                <td><?php echo $gasto['cantidad']; ?></td>
                                <td><strong style="color: #e02424;">$<?php echo number_format($gasto['monto_total'], 2, ',', '.'); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
