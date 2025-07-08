<?php

// Ocultar advertencias deprecadas en producción
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0); // En producción debe ser 0

// Solo mostrar errores en desarrollo
// if (defined('DEVELOPMENT') && DEVELOPMENT === true) {
//     error_reporting(E_ALL);
//     ini_set('display_errors', 1);
// }
// Configuración general del sistema
define('SITE_NAME', 'Sistema de Sorteos');
define('TIMEZONE', 'America/Bogota');

// Configuración de sesiones
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en HTTPS

session_start();
date_default_timezone_set(TIMEZONE);

// Función para generar logs de seguridad
function logActivity($empleado_id, $accion, $detalles = null) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "INSERT INTO system_logs (empleado_id, accion, detalles, ip_address) 
              VALUES (:empleado_id, :accion, :detalles, :ip_address)";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':empleado_id', $empleado_id);
    $stmt->bindParam(':accion', $accion);
    $stmt->bindParam(':detalles', $detalles);
    $stmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR']);
    
    return $stmt->execute();
}

// Función para verificar sesión activa
function checkSession() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
        header('Location: index.php');
        exit();
    }
    
    // Verificar timeout de sesión (30 minutos)
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity'] > 1800)) {
        session_destroy();
        header('Location: index.php?timeout=1');
        exit();
    }
    
    $_SESSION['last_activity'] = time();
}

// Función para verificar permisos de administrador
function checkAdminPermission() {
    checkSession();
    if ($_SESSION['user_type'] !== 'moderador') {
        header('Location: dashboard.php');
        exit();
    }
}
