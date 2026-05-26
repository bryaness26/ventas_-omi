<?php
// Módulo para Editar Usuarios
require_once __DIR__ . '/includes/header.php';

$mensaje = '';
$error = '';
$usuario_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($usuario_id <= 0) {
    echo "<div class='alert alert-danger'>ID de usuario no válido. <a href='usuarios.php'>Volver</a></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_usuario'])) {
    $nombre = sanitize($_POST['nombre']);
    $usuario = sanitize($_POST['usuario']);
    $rol = sanitize($_POST['rol']);
    $password = $_POST['password'];

    if (empty($nombre) || empty($usuario) || empty($rol)) {
        $error = 'Por favor, complete los campos obligatorios (Nombre, Usuario y Rol).';
    } else {
        try {
            // Verificar si el nombre de usuario ya está registrado por otro
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = :usuario AND id != :id LIMIT 1");
            $stmt->execute(['usuario' => $usuario, 'id' => $usuario_id]);
            if ($stmt->fetch()) {
                $error = 'El nombre de usuario ya está registrado por otra persona.';
            } else {
                if (!empty($password)) {
                    // Actualizar con contraseña
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = :nombre, usuario = :usuario, password = :password, rol = :rol WHERE id = :id");
                    $stmt->execute([
                        'nombre' => $nombre,
                        'usuario' => $usuario,
                        'password' => $password_hash,
                        'rol' => $rol,
                        'id' => $usuario_id
                    ]);
                } else {
                    // Actualizar sin contraseña
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = :nombre, usuario = :usuario, rol = :rol WHERE id = :id");
                    $stmt->execute([
                        'nombre' => $nombre,
                        'usuario' => $usuario,
                        'rol' => $rol,
                        'id' => $usuario_id
                    ]);
                }
                $mensaje = 'Usuario actualizado correctamente.';
            }
        } catch (PDOException $e) {
            $error = 'Error de base de datos al actualizar: ' . $e->getMessage();
        }
    }
}

// Obtener datos actuales del usuario
try {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $usuario_id]);
    $usuario_actual = $stmt->fetch();

    if (!$usuario_actual) {
        echo "<div class='alert alert-danger'>El usuario no existe. <a href='usuarios.php'>Volver</a></div>";
        require_once __DIR__ . '/includes/footer.php';
        exit;
    }
} catch (PDOException $e) {
    echo "Error al consultar la base de datos.";
    exit;
}
?>

<div style="margin-bottom: 32px; display: flex; align-items: center; justify-content: space-between;">
    <div>
        <h2 style="font-size: 32px; color: var(--dark); font-weight: 800; display: flex; align-items: center; gap: 12px;">
            <i class="ph-bold ph-pencil-simple" style="color: var(--primary);"></i>
            <span>Editar Usuario</span>
        </h2>
        <p style="color: var(--dark-light); opacity: 0.8; font-size: 15px;">
            Actualiza los datos del usuario seleccionado.
        </p>
    </div>
    <a href="usuarios.php" class="btn-primary" style="background: rgba(255,255,255,0.8); color: var(--dark); box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid rgba(255,255,255,1);">
        <i class="ph-bold ph-arrow-left" style="margin-right: 6px;"></i>
        <span>Volver a Usuarios</span>
    </a>
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

<div class="panel-card" style="max-width: 600px; margin: 0 auto;">
    <form action="editar_usuario.php?id=<?php echo $usuario_id; ?>" method="POST">
        <input type="hidden" name="actualizar_usuario" value="1">
        
        <div class="form-group">
            <label for="nombre">Nombre Completo</label>
            <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo sanitize($usuario_actual['nombre']); ?>" required>
        </div>

        <div class="form-group">
            <label for="usuario">Nombre de Usuario (Login)</label>
            <input type="text" name="usuario" id="usuario" class="form-control" value="<?php echo sanitize($usuario_actual['usuario']); ?>" required autocomplete="username">
        </div>

        <div class="form-group">
            <label for="password">Nueva Contraseña (Opcional)</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Dejar en blanco para mantener la actual" autocomplete="new-password">
            <small style="color: var(--dark-light); opacity: 0.7; margin-top: 4px; display: block;">Solo llena este campo si deseas cambiar la contraseña del usuario.</small>
        </div>

        <div class="form-group" style="margin-bottom: 28px;">
            <label for="rol">Rol / Nivel de Acceso</label>
            <?php if ($usuario_id == $_SESSION['user_id']): ?>
                <input type="text" class="form-control" value="<?php echo sanitize($usuario_actual['rol']); ?>" disabled>
                <input type="hidden" name="rol" value="<?php echo sanitize($usuario_actual['rol']); ?>">
                <small style="color: var(--primary); margin-top: 4px; display: block;">No puedes cambiar tu propio rol.</small>
            <?php else: ?>
                <select name="rol" id="rol" class="form-control" required>
                    <option value="vendedor" <?php echo $usuario_actual['rol'] == 'vendedor' ? 'selected' : ''; ?>>Vendedor (Estándar)</option>
                    <option value="administrador" <?php echo $usuario_actual['rol'] == 'administrador' ? 'selected' : ''; ?>>Administrador (Completo)</option>
                </select>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%;">
            <i class="ph-bold ph-floppy-disk" style="vertical-align: middle; margin-right: 6px; font-size: 18px;"></i>
            <span>Guardar Cambios</span>
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
