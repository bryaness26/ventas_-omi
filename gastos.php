<?php
// Módulo de Gastos de Ñomi
require_once __DIR__ . '/includes/header.php';

$mensaje = '';
$error = '';

// Procesar el registro de un nuevo gasto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_gasto'])) {
    $fecha_gasto = sanitize($_POST['fecha_gasto']);
    $producto_comprado = sanitize($_POST['producto_comprado']);
    $cantidad = intval($_POST['cantidad']);
    $monto_total = floatval($_POST['monto_total']);
    $usuario_id = $_SESSION['user_id'];

    if (empty($fecha_gasto) || empty($producto_comprado) || $cantidad <= 0 || $monto_total <= 0) {
        $error = 'Por favor, complete todos los campos con valores válidos.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO gastos (fecha_gasto, producto_comprado, cantidad, monto_total, usuario_id) 
                                   VALUES (:fecha_gasto, :producto_comprado, :cantidad, :monto_total, :usuario_id)");
            $stmt->execute([
                'fecha_gasto' => $fecha_gasto,
                'producto_comprado' => $producto_comprado,
                'cantidad' => $cantidad,
                'monto_total' => $monto_total,
                'usuario_id' => $usuario_id
            ]);
            $mensaje = '¡Gasto registrado con éxito!';
        } catch (PDOException $e) {
            $error = 'Error al registrar el gasto: ' . $e->getMessage();
        }
    }
}

// Procesar la eliminación de un gasto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_gasto'])) {
    $id_gasto = intval($_POST['id_gasto']);
    if ($id_gasto > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM gastos WHERE id = :id");
            $stmt->execute(['id' => $id_gasto]);
            $mensaje = '¡Gasto eliminado correctamente!';
        } catch (PDOException $e) {
            $error = 'Error al eliminar el gasto: ' . $e->getMessage();
        }
    }
}

// Obtener todos los gastos
try {
    $stmt = $pdo->query("SELECT g.*, u.nombre AS comprador FROM gastos g JOIN usuarios u ON g.usuario_id = u.id ORDER BY g.fecha_gasto DESC, g.id DESC");
    $gastos = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error al consultar los gastos: ' . $e->getMessage();
}
?>

<div style="margin-bottom: 32px;">
    <h2 style="font-size: 32px; color: var(--dark); font-weight: 800; display: flex; align-items: center; gap: 12px;">
        <i class="ph-bold ph-receipt" style="color: var(--secondary);"></i>
        <span>Control de Gastos</span>
    </h2>
    <p style="color: var(--dark-light); opacity: 0.8; font-size: 15px;">
        Registra compras de insumos (fresas, crema, chocolate, envases) para calcular la rentabilidad real del negocio.
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
    <!-- Formulario para agregar gastos -->
    <div class="panel-card">
        <h3 class="panel-title" style="margin-bottom: 24px;">Registrar Gasto</h3>
        
        <form action="gastos.php" method="POST">
            <input type="hidden" name="registrar_gasto" value="1">
            
            <div class="form-group">
                <label for="fecha_gasto">Fecha del Gasto</label>
                <input type="date" name="fecha_gasto" id="fecha_gasto" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label for="producto_comprado">Producto o Insumo Comprado</label>
                <input type="text" name="producto_comprado" id="producto_comprado" class="form-control" placeholder="Ej. 5kg de Fresas frescas, 2lt de Crema" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="cantidad">Cantidad</label>
                    <input type="number" name="cantidad" id="cantidad" class="form-control" min="1" value="1" required>
                </div>

                <div class="form-group">
                    <label for="monto_total">Monto Total ($)</label>
                    <input type="number" name="monto_total" id="monto_total" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-hover) 100%); box-shadow: 0 8px 20px rgba(221, 78, 40, 0.2); margin-top: 10px;">
                <i class="ph-bold ph-plus-circle" style="vertical-align: middle; margin-right: 6px; font-size: 18px;"></i>
                <span>Guardar Gasto</span>
            </button>
        </form>
    </div>

    <!-- Historial de Gastos -->
    <div class="panel-card">
        <h3 class="panel-title" style="margin-bottom: 24px;">Historial de Gastos</h3>
        
        <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto / Detalle</th>
                        <th>Cantidad</th>
                        <th>Monto ($)</th>
                        <th>Registrado por</th>
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($gastos)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: rgba(57,31,0,0.5); padding: 30px;">
                                No hay gastos registrados aún.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($gastos as $gasto): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($gasto['fecha_gasto'])); ?></td>
                                <td><strong><?php echo sanitize($gasto['producto_comprado']); ?></strong></td>
                                <td><?php echo $gasto['cantidad']; ?></td>
                                <td><strong style="color: #e02424;">$<?php echo number_format($gasto['monto_total'], 2, ',', '.'); ?></strong></td>
                                <td><?php echo sanitize($gasto['comprador']); ?></td>
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; gap: 8px; justify-content: center; align-items: center;">
                                        <!-- Botón de Edición -->
                                        <a href="editar_gasto.php?id=<?php echo $gasto['id']; ?>" class="btn-secondary" style="font-size: 13px; padding: 6px 10px; background: var(--primary); display: inline-flex; align-items: center; text-decoration: none;" title="Editar">
                                            <i class="ph-bold ph-pencil"></i>
                                        </a>
                                        <!-- Formulario de Eliminación Segura -->
                                        <form action="gastos.php" method="POST" class="delete-form" style="display: inline;">
                                            <input type="hidden" name="id_gasto" value="<?php echo $gasto['id']; ?>">
                                            <input type="hidden" name="eliminar_gasto" value="1">
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
