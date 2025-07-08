<?php
require_once '../config/config.php';
require_once '../config/database.php';

checkAdminPermission();

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT es.*, e.numero_documento, e.nombre_completo, e.cargo,
                     aps.descripcion as sorteo_descripcion,
                     COUNT(bc.id) as elecciones_usadas, bc.numero_balota
              FROM empleados_en_sorteo es
              INNER JOIN empleados e ON es.id_empleado = e.id
              INNER JOIN apertura_sorteo aps ON es.id_sorteo = aps.id
              LEFT JOIN balota_concursante bc ON es.id = bc.id_empleado_sort
              GROUP BY es.id
              ORDER BY e.nombre_completo";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['data' => $empleados]);
    
} catch (Exception $e) {
    error_log("Error en empleados_sorteo.php: " . $e->getMessage());
    echo json_encode(['data' => []]);
}
