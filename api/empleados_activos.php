<?php
require_once '../config/config.php';
require_once '../config/database.php';

checkAdminPermission();

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT e.id,e.numero_documento, CONCAT(e.numero_documento, ' - ', e.nombre_completo) as nombre_completo 
              FROM empleados e
              WHERE e.estado_empleado = 1 
              ORDER BY e.nombre_completo ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $numero_documento_selects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'numero_documento_selects' => $numero_documento_selects]);
    
} catch (Exception $e) {
    error_log("Error en empleado_activos.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'numero_documento_selects' => []]);
}
?>