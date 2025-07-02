<?php
// api/get_sorteo.php
require_once '../config/config.php';
require_once '../config/database.php';

checkAdminPermission();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de sorteo inválido']);
    exit();
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    $sorteo_id = (int)$_GET['id'];
    
    $query = "SELECT id, descripcion, 
                     DATE_FORMAT(fecha_inicio_sorteo, '%Y-%m-%d') as fecha_inicio_sorteo,
                     DATE_FORMAT(fecha_cierre_sorteo, '%Y-%m-%d') as fecha_cierre_sorteo,
                     estado
              FROM apertura_sorteo 
              WHERE id = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $sorteo_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $sorteo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sorteo) {
        echo json_encode(['success' => true, 'sorteo' => $sorteo]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sorteo no encontrado']);
    }
    
} catch (Exception $e) {
    error_log("Error en get_sorteo.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}


