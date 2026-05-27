<?php
// Módulo de Ventas de Ñomi
require_once __DIR__ . '/includes/header.php';

$mensaje = '';
$error = '';

// Procesar el registro de una nueva venta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_venta'])) {
    $fecha_venta = sanitize($_POST['fecha_venta']);
    $cliente = sanitize($_POST['cliente']);
    $producto = sanitize($_POST['producto']);
    $cantidad = intval($_POST['cantidad']);
    $monto_total = floatval($_POST['monto_total']);
    $metodo_pago = sanitize($_POST['metodo_pago']);
    $usuario_id = $_SESSION['user_id'];

    if (empty($fecha_venta) || empty($cliente) || empty($producto) || $cantidad <= 0 || $monto_total <= 0 || empty($metodo_pago)) {
        $error = 'Por favor, complete todos los campos con valores válidos.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO ventas (fecha_venta, cliente, producto, cantidad, monto_total, metodo_pago, usuario_id) 
                                   VALUES (:fecha_venta, :cliente, :producto, :cantidad, :monto_total, :metodo_pago, :usuario_id)");
            $stmt->execute([
                'fecha_venta' => $fecha_venta,
                'cliente' => $cliente,
                'producto' => $producto,
                'cantidad' => $cantidad,
                'monto_total' => $monto_total,
                'metodo_pago' => $metodo_pago,
                'usuario_id' => $usuario_id
            ]);
            $mensaje = '¡Venta registrada con éxito!';
        } catch (PDOException $e) {
            $error = 'Error al registrar la venta: ' . $e->getMessage();
        }
    }
}

// Procesar la eliminación de una venta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_venta'])) {
    $id_venta = intval($_POST['id_venta']);
    if ($id_venta > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM ventas WHERE id = :id");
            $stmt->execute(['id' => $id_venta]);
            $mensaje = '¡Venta eliminada correctamente!';
        } catch (PDOException $e) {
            $error = 'Error al eliminar la venta: ' . $e->getMessage();
        }
    }
}

// Obtener todas las ventas
try {
    $stmt = $pdo->query("SELECT v.*, u.nombre AS vendedor FROM ventas v JOIN usuarios u ON v.usuario_id = u.id ORDER BY v.fecha_venta DESC, v.id DESC");
    $ventas = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error al consultar las ventas: ' . $e->getMessage();
}
?>

<div style="margin-bottom: 32px;">
    <h2 style="font-size: 32px; color: var(--dark); font-weight: 800; display: flex; align-items: center; gap: 12px;">
        <i class="ph-bold ph-shopping-cart-simple" style="color: var(--primary);"></i>
        <span>Gestión de Ventas</span>
    </h2>
    <p style="color: var(--dark-light); opacity: 0.8; font-size: 15px;">
        Agrega nuevas ventas de fresas con crema y visualiza el historial de transacciones.
    </p>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success">
        <i class="ph-bold ph-check-circle" style="vertical-align: middle; margin-right: 8px; font-size: 18px;"></i>
        <?php echo $mensaje; ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <i class="ph-bold ph-warning-circle" style="vertical-align: middle; margin-right: 8px; font-size: 18px;"></i>
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="grid-form-table">
    <!-- Formulario para agregar venta (Estilo Premium) -->
    <div class="panel-card">
        <h3 class="panel-title" style="margin-bottom: 24px;">Registrar Venta</h3>
        
        <form action="ventas.php" method="POST">
            <input type="hidden" name="registrar_venta" value="1">
            
            <div class="form-group">
                <label for="fecha_venta">Fecha de Venta</label>
                <input type="date" name="fecha_venta" id="fecha_venta" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label for="cliente">Nombre del Cliente</label>
                <input type="text" name="cliente" id="cliente" class="form-control" placeholder="Ej. Juan Pérez" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="producto">Presentación</label>
                    <select name="producto" id="producto" class="form-control" required>
                        <option value="" disabled selected>Seleccione...</option>
                        <option value="Pequeña">Pequeña ($2.00)</option>
                        <option value="Grande">Grande ($5.00)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cantidad">Cantidad</label>
                    <input type="number" name="cantidad" id="cantidad" class="form-control" min="1" value="1" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="metodo_pago">Método de Pago</label>
                    <select name="metodo_pago" id="metodo_pago" class="form-control" required>
                        <option value="Pago Móvil">Pago Móvil</option>
                        <option value="Efectivo" selected>Efectivo</option>
                        <option value="Transferencia">Transferencia</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="monto_total">Monto Total ($)</label>
                    <!-- Campo de solo lectura, se calcula en tiempo real por JS en app.js -->
                    <input type="text" name="monto_total" id="monto_total" class="form-control" style="background-color: var(--bg-light); font-weight: 700; color: var(--primary);" readonly required placeholder="0.00">
                </div>
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 10px;">
                <i class="ph-bold ph-plus-circle" style="vertical-align: middle; margin-right: 6px; font-size: 18px;"></i>
                <span>Guardar Venta</span>
            </button>
        </form>
    </div>

    <!-- Historial de Ventas -->
    <div class="panel-card">
        <h3 class="panel-title" style="margin-bottom: 24px;">Historial de Ventas</h3>
        
        <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Producto</th>
                        <th>Cant.</th>
                        <th>Total</th>
                        <th>Pago</th>
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ventas)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: rgba(57,31,0,0.5); padding: 30px;">
                                No hay ventas registradas aún.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ventas as $venta): ?>
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
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; gap: 8px; justify-content: center; align-items: center;">
                                        <!-- Botón de Edición -->
                                        <a href="editar_venta.php?id=<?php echo $venta['id']; ?>" class="btn-secondary" style="font-size: 13px; padding: 6px 10px; background: var(--primary); display: inline-flex; align-items: center; text-decoration: none;" title="Editar">
                                            <i class="ph-bold ph-pencil"></i>
                                        </a>
                                        <!-- Formulario de Eliminación Segura -->
                                        <form action="ventas.php" method="POST" class="delete-form" style="display: inline;">
                                            <input type="hidden" name="id_venta" value="<?php echo $venta['id']; ?>">
                                            <input type="hidden" name="eliminar_venta" value="1">
                                            <button type="submit" class="btn-secondary" style="font-size: 13px; padding: 6px 10px; background: var(--secondary); border: none; cursor: pointer; display: inline-flex; align-items: center;" title="Eliminar">
                                                <i class="ph-bold ph-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
