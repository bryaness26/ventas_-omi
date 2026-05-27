<?php
// Módulo de Edición de Ventas de Ñomi
require_once __DIR__ . '/includes/header.php';

$mensaje = '';
$error = '';
$venta = null;

// Obtener los datos de la venta actual
if (isset($_GET['id'])) {
    $id_venta = intval($_GET['id']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM ventas WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id_venta]);
        $venta = $stmt->fetch();
        
        if (!$venta) {
            $error = 'La venta solicitada no existe o ha sido eliminada.';
        }
    } catch (PDOException $e) {
        $error = 'Error de base de datos: ' . $e->getMessage();
    }
} else {
    redirect('ventas.php');
}

// Procesar la actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_venta']) && $venta) {
    $fecha_venta = sanitize($_POST['fecha_venta']);
    $cliente = sanitize($_POST['cliente']);
    $cant_pequena = intval($_POST['cant_pequena'] ?? 0);
    $cant_grande = intval($_POST['cant_grande'] ?? 0);
    $metodo_pago = sanitize($_POST['metodo_pago']);

    if (empty($fecha_venta) || empty($cliente) || ($cant_pequena <= 0 && $cant_grande <= 0) || empty($metodo_pago)) {
        $error = 'Por favor, complete todos los campos con valores válidos. Debe ingresar al menos una cantidad.';
    } else {
        // Determinar producto y cantidad total
        if ($cant_pequena > 0 && $cant_grande > 0) {
            $producto = 'Mixta';
        } elseif ($cant_pequena > 0) {
            $producto = 'Pequeña';
        } else {
            $producto = 'Grande';
        }
        $cantidad = $cant_pequena + $cant_grande;
        $monto_total = ($cant_pequena * 2.00) + ($cant_grande * 5.00);

        try {
            $stmt = $pdo->prepare("UPDATE ventas SET 
                                    fecha_venta = :fecha_venta, 
                                    cliente = :cliente, 
                                    producto = :producto, 
                                    cantidad = :cantidad, 
                                    cant_pequena = :cant_pequena, 
                                    cant_grande = :cant_grande, 
                                    monto_total = :monto_total, 
                                    metodo_pago = :metodo_pago 
                                   WHERE id = :id");
            $stmt->execute([
                'fecha_venta' => $fecha_venta,
                'cliente' => $cliente,
                'producto' => $producto,
                'cantidad' => $cantidad,
                'cant_pequena' => $cant_pequena,
                'cant_grande' => $cant_grande,
                'monto_total' => $monto_total,
                'metodo_pago' => $metodo_pago,
                'id' => $venta['id']
            ]);
            
            // Recargar datos actualizados
            $stmt = $pdo->prepare("SELECT * FROM ventas WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $venta['id']]);
            $venta = $stmt->fetch();
            
            $mensaje = '¡Venta actualizada con éxito!';
        } catch (PDOException $e) {
            $error = 'Error al actualizar la venta: ' . $e->getMessage();
        }
    }
}
?>

<div style="margin-bottom: 32px;">
    <h2 style="font-size: 32px; color: var(--dark); font-weight: 800; display: flex; align-items: center; gap: 12px;">
        <i class="ph-bold ph-pencil-simple" style="color: var(--primary);"></i>
        <span>Editar Venta</span>
    </h2>
    <p style="color: var(--dark-light); opacity: 0.8; font-size: 15px;">
        Corrige la información registrada sobre la venta de fresas con crema.
    </p>
</div>

<a href="ventas.php" class="btn-secondary" style="font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 24px; background-color: var(--dark);">
    <i class="ph-bold ph-arrow-left"></i>
    <span>Volver al Historial</span>
</a>

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

<?php if ($venta): ?>
    <div class="panel-card" style="max-width: 600px;">
        <h3 class="panel-title" style="margin-bottom: 24px;">Detalles de la Venta #<?php echo $venta['id']; ?></h3>
        
        <form action="editar_venta.php?id=<?php echo $venta['id']; ?>" method="POST">
            <input type="hidden" name="actualizar_venta" value="1">
            
            <div class="form-group">
                <label for="fecha_venta">Fecha de Venta</label>
                <input type="date" name="fecha_venta" id="fecha_venta" class="form-control" value="<?php echo $venta['fecha_venta']; ?>" required>
            </div>

            <div class="form-group">
                <label for="cliente">Nombre del Cliente</label>
                <input type="text" name="cliente" id="cliente" class="form-control" value="<?php echo sanitize($venta['cliente']); ?>" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="cant_pequena">Cant. Pequeña ($2.00)</label>
                    <input type="number" name="cant_pequena" id="cant_pequena" class="form-control" min="0" value="<?php echo $venta['cant_pequena']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="cant_grande">Cant. Grande ($5.00)</label>
                    <input type="number" name="cant_grande" id="cant_grande" class="form-control" min="0" value="<?php echo $venta['cant_grande']; ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="metodo_pago">Método de Pago</label>
                    <select name="metodo_pago" id="metodo_pago" class="form-control" required>
                        <option value="Pago Móvil" <?php echo $venta['metodo_pago'] == 'Pago Móvil' ? 'selected' : ''; ?>>Pago Móvil</option>
                        <option value="Efectivo" <?php echo $venta['metodo_pago'] == 'Efectivo' ? 'selected' : ''; ?>>Efectivo</option>
                        <option value="Transferencia" <?php echo $venta['metodo_pago'] == 'Transferencia' ? 'selected' : ''; ?>>Transferencia</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="monto_total">Monto Total ($)</label>
                    <input type="text" name="monto_total" id="monto_total" class="form-control" style="background-color: var(--bg-light); font-weight: 700; color: var(--primary);" readonly required value="<?php echo $venta['monto_total']; ?>">
                </div>
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 10px;">
                <i class="ph-bold ph-floppy-disk" style="vertical-align: middle; margin-right: 6px; font-size: 18px;"></i>
                <span>Guardar Cambios</span>
            </button>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
