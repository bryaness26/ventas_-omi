<?php
// Módulo de Edición de Gastos de Ñomi
require_once __DIR__ . '/includes/header.php';

$mensaje = '';
$error = '';
$gasto = null;

// Obtener los datos del gasto actual
if (isset($_GET['id'])) {
    $id_gasto = intval($_GET['id']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM gastos WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id_gasto]);
        $gasto = $stmt->fetch();
        
        if (!$gasto) {
            $error = 'El gasto solicitado no existe o ha sido eliminado.';
        }
    } catch (PDOException $e) {
        $error = 'Error de base de datos: ' . $e->getMessage();
    }
} else {
    redirect('gastos.php');
}

// Procesar la actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_gasto']) && $gasto) {
    $fecha_gasto = sanitize($_POST['fecha_gasto']);
    $producto_comprado = sanitize($_POST['producto_comprado']);
    $cantidad = intval($_POST['cantidad']);
    $monto_total = floatval($_POST['monto_total']);

    if (empty($fecha_gasto) || empty($producto_comprado) || $cantidad <= 0 || $monto_total <= 0) {
        $error = 'Por favor, complete todos los campos con valores válidos.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE gastos SET 
                                    fecha_gasto = :fecha_gasto, 
                                    producto_comprado = :producto_comprado, 
                                    cantidad = :cantidad, 
                                    monto_total = :monto_total 
                                   WHERE id = :id");
            $stmt->execute([
                'fecha_gasto' => $fecha_gasto,
                'producto_comprado' => $producto_comprado,
                'cantidad' => $cantidad,
                'monto_total' => $monto_total,
                'id' => $gasto['id']
            ]);
            
            // Recargar datos actualizados
            $stmt = $pdo->prepare("SELECT * FROM gastos WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $gasto['id']]);
            $gasto = $stmt->fetch();
            
            $mensaje = '¡Gasto actualizado con éxito!';
        } catch (PDOException $e) {
            $error = 'Error al actualizar el gasto: ' . $e->getMessage();
        }
    }
}
?>

<div style="margin-bottom: 32px;">
    <h2 style="font-size: 32px; color: var(--dark); font-weight: 800; display: flex; align-items: center; gap: 12px;">
        <i class="ph-bold ph-pencil-simple" style="color: var(--secondary);"></i>
        <span>Editar Gasto</span>
    </h2>
    <p style="color: var(--dark-light); opacity: 0.8; font-size: 15px;">
        Corrige la información registrada sobre tus compras de insumos.
    </p>
</div>

<a href="gastos.php" class="btn-secondary" style="font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 24px; background-color: var(--dark);">
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

<?php if ($gasto): ?>
    <div class="panel-card" style="max-width: 600px;">
        <h3 class="panel-title" style="margin-bottom: 24px;">Detalles del Gasto #<?php echo $gasto['id']; ?></h3>
        
        <form action="editar_gasto.php?id=<?php echo $gasto['id']; ?>" method="POST">
            <input type="hidden" name="actualizar_gasto" value="1">
            
            <div class="form-group">
                <label for="fecha_gasto">Fecha del Gasto</label>
                <input type="date" name="fecha_gasto" id="fecha_gasto" class="form-control" value="<?php echo $gasto['fecha_gasto']; ?>" required>
            </div>

            <div class="form-group">
                <label for="producto_comprado">Producto o Insumo Comprado</label>
                <input type="text" name="producto_comprado" id="producto_comprado" class="form-control" value="<?php echo sanitize($gasto['producto_comprado']); ?>" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="cantidad">Cantidad</label>
                    <input type="number" name="cantidad" id="cantidad" class="form-control" min="1" value="<?php echo $gasto['cantidad']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="monto_total">Monto Total ($)</label>
                    <input type="number" name="monto_total" id="monto_total" class="form-control" step="0.01" min="0.01" value="<?php echo $gasto['monto_total']; ?>" required>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-hover) 100%); box-shadow: 0 8px 20px rgba(221, 78, 40, 0.2); margin-top: 10px;">
                <i class="ph-bold ph-floppy-disk" style="vertical-align: middle; margin-right: 6px; font-size: 18px;"></i>
                <span>Guardar Cambios</span>
            </button>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
