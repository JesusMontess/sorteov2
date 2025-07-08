<?php
require_once '../config/config.php';
require_once '../controllers/UsuarioController.php';

checkAdminPermission();

header('Content-Type: application/json');

try {
    $usuarioController = new UsuarioController();
    $result = $usuarioController->actualizarUsuario();
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Error en actualizar_usuario.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al actualizar usuario']);
}