<?php
// ===== PASO 1: CREAR API PARA RESET PASSWORD =====
// Archivo: api/reset_password.php

require_once '../config/database.php';
require_once '../config/config.php';

header('Content-Type: application/json');

try {
    // Verificar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Solo método POST']);
        exit;
    }
    
    // Verificar sesión de admin
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Sin autorización']);
        exit;
    }
    
    // Obtener datos
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nueva_password = isset($_POST['nueva_password']) ? trim($_POST['nueva_password']) : '';
    
    // Logs para debug
    error_log("=== RESET PASSWORD ===");
    error_log("ID usuario: $id");
    error_log("Nueva password length: " . strlen($nueva_password));
    error_log("Admin que hace el cambio: " . $_SESSION['user_id']);
    
    // Validaciones
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario inválido']);
        exit;
    }
    
    if (empty($nueva_password)) {
        echo json_encode(['success' => false, 'message' => 'La nueva contraseña es obligatoria']);
        exit;
    }
    
    if (strlen($nueva_password) < 6) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
        exit;
    }
    
    if (strlen($nueva_password) > 50) {
        echo json_encode(['success' => false, 'message' => 'La contraseña no puede exceder 50 caracteres']);
        exit;
    }
    
    // Conectar a BD
    $database = new Database();
    $conn = $database->getConnection();
    
    // Verificar que el usuario existe
    $checkQuery = "SELECT id, id_empleado FROM usuario_moderador WHERE id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute([$id]);
    $usuario = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    // Hash de la nueva contraseña
    $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
    
    // Actualizar contraseña
    $updateQuery = "UPDATE usuario_moderador SET clave = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $result = $updateStmt->execute([$password_hash, $id]);
    
    if ($result && $updateStmt->rowCount() > 0) {
        // Log de actividad
        if (function_exists('logActivity')) {
            logActivity($_SESSION['user_id'], 'RESET_PASSWORD', 
                       "Contraseña reseteada para usuario ID: $id");
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Contraseña actualizada correctamente',
            'usuario_id' => $id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la contraseña']);
    }
    
} catch (Exception $e) {
    error_log("Error en reset_password.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>