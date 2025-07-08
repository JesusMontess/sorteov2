<?php
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../models/Usuario.php';

checkAdminPermission();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID requerido']);
    exit;
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    $usuario = new Usuario($conn);
    
    $usuarioData = $usuario->getUsuarioById($_GET['id']);
    
    if ($usuarioData) {
        echo json_encode(['success' => true, 'usuario' => $usuarioData]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }
} catch (Exception $e) {
    error_log("Error en get_usuario.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al obtener usuario']);
}