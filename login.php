<?php
// Módulo de Autenticación de Ñomi
require_once __DIR__ . '/includes/functions.php';

if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = sanitize($_POST['usuario']);
    $password = $_POST['password'];

    if (empty($usuario) || empty($password)) {
        $error = 'Por favor, complete todos los campos.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1");
            $stmt->execute(['usuario' => $usuario]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Registrar datos de la sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nombre'] = $user['nombre'];
                $_SESSION['user_usuario'] = $user['usuario'];
                $_SESSION['user_rol'] = $user['rol'];

                redirect('dashboard.php');
            } else {
                $error = 'Usuario o contraseña incorrectos.';
            }
        } catch (PDOException $e) {
            $error = 'Error de conexión con la base de datos: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ñomi - Iniciar Sesión</title>
    <link rel="icon" type="image/png" href="img/logo.png">
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="login-container">

    <div class="login-card">
        <!-- Logo con fallback premium -->
        <img class="login-logo" src="img/logo.png" alt="Ñomi Logo" onerror="this.src='https://placehold.co/150x150/d81a67/ffffff?text=%C3%91omi'">
        
        <h2>Bienvenido a Ñomi</h2>
        <p>Introduce tus credenciales para acceder</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="ph-bold ph-warning-circle" style="vertical-align: middle; margin-right: 8px;"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="usuario">Nombre de Usuario</label>
                <div style="position: relative;">
                    <i class="ph-bold ph-user" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--dark-light); opacity: 0.7;"></i>
                    <input type="text" name="usuario" id="usuario" class="form-control" style="padding-left: 42px;" placeholder="Ej. admin" required autocomplete="username">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 28px;">
                <label for="password">Contraseña</label>
                <div style="position: relative;">
                    <i class="ph-bold ph-lock" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--dark-light); opacity: 0.7;"></i>
                    <input type="password" name="password" id="password" class="form-control" style="padding-left: 42px;" placeholder="••••••••" required autocomplete="current-password">
                </div>
            </div>

            <button type="submit" class="btn-primary">
                <span>Ingresar al Sistema</span>
                <i class="ph-bold ph-caret-right" style="vertical-align: middle; margin-left: 4px;"></i>
            </button>
        </form>
        
        <div style="margin-top: 24px; font-size: 12px; color: var(--dark-light); opacity: 0.6;">
            &copy; <?php echo date('Y'); ?> Ñomi Fresas. Todos los derechos reservados.
        </div>
    </div>

</body>
</html>
