<?php
// Enrutador de Entrada al Sistema Ñomi
require_once __DIR__ . '/includes/functions.php';

if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
} else {
    redirect('login.php');
}
?>
