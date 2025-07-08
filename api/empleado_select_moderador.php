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
    $id_empleado_select = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'id_empleado_select' => $id_empleado_select]);
    
} catch (Exception $e) {
    error_log("Error en empleado_select_moderador.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'id_empleado_select' => []]);
}
?>