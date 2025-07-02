<?php
require_once '../config/config.php';
require_once '../config/database.php';

checkAdminPermission();

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT id, descripcion FROM apertura_sorteo 
              WHERE estado = 1 AND fecha_cierre_sorteo >= CURDATE()
              ORDER BY fecha_inicio_sorteo DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $sorteos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'sorteos' => $sorteos]);
    
} catch (Exception $e) {
    error_log("Error en sorteos_activos.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'sorteos' => []]);
}
?>