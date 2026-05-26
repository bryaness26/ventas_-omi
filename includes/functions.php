<?php
// Funciones globales de utilidad para el sistema Ñomi
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Requerir base de datos
require_once __DIR__ . '/../config/database.php';

/**
 * Sanea entradas de texto para evitar ataques XSS
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirecciona a una URL específica
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Verifica si el usuario ha iniciado sesión
 */
function check_auth() {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

/**
 * Obtiene la tasa del Dólar BCV desde la API o mediante scraping del BCV con caché de 1 hora
 */
function obtener_tasa_bcv() {
    $cache_file = __DIR__ . '/../config/bcv_cache.json';
    $cache_time = 3600; // 1 hora en segundos
    $default_rate = 36.50; // Tasa de respaldo si todo falla

    // Si el caché es válido, retornar la tasa guardada
    if (file_exists($cache_file)) {
        $cache_data = json_decode(file_get_contents($cache_file), true);
        if ($cache_data && (time() - $cache_data['timestamp'] < $cache_time)) {
            return floatval($cache_data['rate']);
        }
    }

    $rate = null;

    // Método 1: Intentar DolarAPI (Rápido y Limpio)
    try {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 3, // Timeout de 3 segundos
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
            ]
        ]);
        $response = @file_get_contents('https://ve.dolarapi.com/v1/dolares/oficial', false, $ctx);
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['promedio']) && is_numeric($data['promedio'])) {
                $rate = floatval($data['promedio']);
            } elseif (isset($data['price']) && is_numeric($data['price'])) {
                $rate = floatval($data['price']);
            } elseif (isset($data['venta']) && is_numeric($data['venta'])) {
                $rate = floatval($data['venta']);
            }
        }
    } catch (Exception $e) {
        $rate = null;
    }

    // Método 2: Scraping de la página oficial del BCV (si falla DolarAPI)
    if (!$rate) {
        try {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.0.0 Safari/537.36'
                ]
            ]);
            $html = @file_get_contents('http://www.bcv.org.ve/', false, $ctx);
            if ($html) {
                // Buscamos el contenedor id="dolar" y capturamos el valor numérico
                // El BCV suele usar comas como separador decimal: "36,55420000"
                if (preg_match('/id="dolar"[\s\S]*?<strong>\s*([0-9.,]+)\s*<\/strong>/i', $html, $matches)) {
                    $cleaned = str_replace(',', '.', trim($matches[1]));
                    if (is_numeric($cleaned)) {
                        $rate = floatval($cleaned);
                    }
                }
            }
        } catch (Exception $e) {
            $rate = null;
        }
    }

    // Si fallan ambos, usar el último caché disponible o la tasa por defecto
    if (!$rate) {
        if (file_exists($cache_file)) {
            $cache_data = json_decode(file_get_contents($cache_file), true);
            return $cache_data ? floatval($cache_data['rate']) : $default_rate;
        }
        return $default_rate;
    }

    // Guardar tasa en el caché
    $cache_data = [
        'rate' => $rate,
        'timestamp' => time()
    ];
    @file_put_contents($cache_file, json_encode($cache_data));

    return $rate;
}
?>
