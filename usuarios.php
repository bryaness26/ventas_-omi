<?php
// Módulo de Usuarios de Ñomi
require_once __DIR__ . '/includes/header.php';

$mensaje = '';
$error = '';

// Procesar el registro de un nuevo usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_usuario'])) {
    $nombre = sanitize($_POST['nombre']);
    $usuario = sanitize($_POST['usuario']);
    $password = $_POST['password'];
    $rol = sanitize($_POST['rol']);

    if (empty($nombre) || empty($usuario) || empty($password) || empty($rol)) {
        $error = 'Por favor, complete todos los campos.';
    } else {
        try {
            // Verificar si el usuario ya existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = :usuario LIMIT 1");
            $stmt->execute(['usuario' => $usuario]);
            if ($stmt->fetch()) {
                $error = 'El nombre de usuario ya está registrado por otra persona.';
            } else {
                // Generar hash seguro de la contraseña
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, usuario, password, rol) VALUES (:nombre, :usuario, :password, :rol)");
                $stmt->execute([
                    'nombre' => $nombre,
                    'usuario' => $usuario,
                    'password' => $password_hash,
                    'rol' => $rol
                ]);
                $mensaje = '¡Usuario registrado correctamente!';
            }
        } catch (PDOException $e) {
            $error = 'Error de base de datos al guardar: ' . $e->getMessage();
        }
    }
}

// Procesar eliminación de usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_usuario_id'])) {
    $id_eliminar = (int)$_POST['eliminar_usuario_id'];
    
    // Evitar que el usuario se elimine a sí mismo
    if ($id_eliminar === (int)$_SESSION['user_id']) {
        $error = 'No puedes eliminar tu propio usuario activo.';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
            $stmt->execute(['id' => $id_eliminar]);
            $mensaje = 'Usuario eliminado correctamente.';
        } catch (PDOException $e) {
            $error = 'Error al eliminar el usuario: ' . $e->getMessage();
        }
    }
}

// Obtener lista de todos los usuarios
try {
    $stmt = $pdo->query("SELECT id, nombre, usuario, rol, created_at FROM usuarios ORDER BY id DESC");
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error al consultar la lista de usuarios: ' . $e->getMessage();
}
?>

<div style="margin-bottom: 32px;">
    <h2 style="font-size: 32px; color: var(--dark); font-weight: 800; display: flex; align-items: center; gap: 12px;">
        <i class="ph-bold ph-user-plus" style="color: var(--primary);"></i>
        <span>Gestión de Usuarios</span>
    </h2>
    <p style="color: var(--dark-light); opacity: 0.8; font-size: 15px;">
        Crea y registra nuevos perfiles para autorizar el acceso al personal de ventas o administración.
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

<div class="grid-2col">
    <!-- Formulario para agregar usuario -->
    <div class="panel-card">
        <h3 class="panel-title" style="margin-bottom: 24px;">Registrar Nuevo Usuario</h3>
        
        <form action="usuarios.php" method="POST">
            <input type="hidden" name="registrar_usuario" value="1">
            
            <div class="form-group">
                <label for="nombre">Nombre Completo</label>
                <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ej. Ana María Gómez" required>
            </div>

            <div class="form-group">
                <label for="usuario">Nombre de Usuario (Login)</label>
                <input type="text" name="usuario" id="usuario" class="form-control" placeholder="Ej. anamaria" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Contraseña Temporal</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required autocomplete="new-password">
            </div>

            <div class="form-group" style="margin-bottom: 28px;">
                <label for="rol">Rol / Nivel de Acceso</label>
                <select name="rol" id="rol" class="form-control" required>
                    <option value="vendedor" selected>Vendedor (Estándar)</option>
                    <option value="administrador">Administrador (Completo)</option>
                </select>
            </div>

            <button type="submit" class="btn-primary">
                <i class="ph-bold ph-user-plus" style="vertical-align: middle; margin-right: 6px; font-size: 18px;"></i>
                <span>Crear Cuenta</span>
            </button>
        </form>
    </div>

    <!-- Lista de Usuarios Existentes -->
    <div class="panel-card">
        <h3 class="panel-title" style="margin-bottom: 24px;">Usuarios Registrados</h3>
        
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Fecha Registro</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usr): ?>
                        <tr>
                            <td><strong><?php echo sanitize($usr['nombre']); ?></strong></td>
                            <td><?php echo sanitize($usr['usuario']); ?></td>
                            <td>
                                <span class="badge <?php echo $usr['rol'] == 'administrador' ? 'badge-large' : 'badge-small'; ?>">
                                    <?php echo sanitize($usr['rol']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($usr['created_at'])); ?></td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <a href="editar_usuario.php?id=<?php echo $usr['id']; ?>" class="btn-action btn-edit" title="Editar">
                                        <i class="ph-bold ph-pencil-simple"></i>
                                    </a>
                                    <?php if ($usr['id'] != $_SESSION['user_id']): ?>
                                    <form action="usuarios.php" method="POST" class="delete-form" style="margin: 0;">
                                        <input type="hidden" name="eliminar_usuario_id" value="<?php echo $usr['id']; ?>">
                                        <button type="submit" class="btn-action btn-delete" title="Eliminar">
                                            <i class="ph-bold ph-trash"></i>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                        <span style="opacity: 0.3; padding: 6px; display: inline-flex;" title="Tú"><i class="ph-bold ph-user-circle"></i></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
