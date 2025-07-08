<?php
require_once '../config/config.php';
require_once '../controllers/UsuarioController.php';

// Verificar permisos de administrador
checkAdminPermission();

header('Content-Type: application/json');

try {
    $usuarioController = new UsuarioController();
    $result = $usuarioController->getUsuarios();
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Error en usuarios.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al obtener usuarios']);
}
