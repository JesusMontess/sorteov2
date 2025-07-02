<?php
require_once '../config/config.php';
require_once '../config/database.php';

checkAdminPermission();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Obtener y validar datos
    $sorteo_id = filter_input(INPUT_POST, 'sorteo_id', FILTER_VALIDATE_INT);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
    $fecha_cierre = trim($_POST['fecha_cierre'] ?? '');
    
    // Validaciones básicas
    if (!$sorteo_id || empty($descripcion) || empty($fecha_inicio) || empty($fecha_cierre)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
        exit();
    }
    
    if (strlen($descripcion) < 5 || strlen($descripcion) > 500) {
        echo json_encode(['success' => false, 'message' => 'La descripción debe tener entre 5 y 500 caracteres']);
        exit();
    }
    
    // Validar formato de fechas
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio) || 
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_cierre)) {
        echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido']);
        exit();
    }
    
    // Verificar que el sorteo existe
    $query_check = "SELECT id, descripcion FROM apertura_sorteo WHERE id = :id";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bindParam(':id', $sorteo_id, PDO::PARAM_INT);
    $stmt_check->execute();
    
    if ($stmt_check->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Sorteo no encontrado']);
        exit();
    }
    
    $sorteo_actual = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    // Actualizar sorteo
    $query_update = "UPDATE apertura_sorteo 
                     SET descripcion = :descripcion,
                         fecha_inicio_sorteo = :fecha_inicio,
                         fecha_cierre_sorteo = :fecha_cierre
                     WHERE id = :id";
    
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bindParam(':descripcion', $descripcion);
    $stmt_update->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt_update->bindParam(':fecha_cierre', $fecha_cierre);
    $stmt_update->bindParam(':id', $sorteo_id, PDO::PARAM_INT);
    
    if ($stmt_update->execute()) {
        // Registrar en logs
        logActivity($_SESSION['user_id'], 'UPDATE_SORTEO', 
                    "Sorteo actualizado: '{$sorteo_actual['descripcion']}' -> '$descripcion' (ID: $sorteo_id)");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Sorteo actualizado exitosamente'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el sorteo']);
    }
    
} catch (Exception $e) {
    error_log("Error en update_sorteo.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}