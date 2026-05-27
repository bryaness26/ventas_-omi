<?php
// Configuración de la conexión a la base de datos MariaDB / MySQL
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ventas_nomi');

try {
    // Establecer conexión PDO con soporte UTF-8
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // Si la base de datos no existe aún (para facilitar la primera ejecución), intentamos conectar sin base de datos
    try {
        $pdoTemp = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdoTemp->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Reintentamos conectar
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        
        // Cargar esquema por defecto si está la tabla vacía
        $schemaFile = __DIR__ . '/../db/schema.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $pdo->exec($sql);
        }
    } catch (PDOException $ex) {
        die("Error crítico de conexión a la base de datos: " . $ex->getMessage());
    }
}

// Migración automática para agregar cant_pequena y cant_grande a la tabla de ventas
try {
    $pdo->query("SELECT cant_pequena FROM ventas LIMIT 1");
} catch (PDOException $e) {
    try {
        $pdo->exec("ALTER TABLE ventas ADD COLUMN cant_pequena INT NOT NULL DEFAULT 0 AFTER cliente");
        $pdo->exec("ALTER TABLE ventas ADD COLUMN cant_grande INT NOT NULL DEFAULT 0 AFTER cant_pequena");
        // Rellenar registros existentes
        $pdo->exec("UPDATE ventas SET cant_pequena = cantidad WHERE producto = 'Pequeña'");
        $pdo->exec("UPDATE ventas SET cant_grande = cantidad WHERE producto = 'Grande'");
    } catch (PDOException $ex) {
        // Silenciar error en caso de que ya existan o haya problemas
    }
}
?>
