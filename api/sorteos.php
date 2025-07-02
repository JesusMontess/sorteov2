<?php
// require_once '../config/config.php';
// require_once '../config/database.php';

// checkAdminPermission();

// header('Content-Type: application/json');

// try {
//     $database = new Database();
//     $conn = $database->getConnection();
    
//     $query = "SELECT aps.*, 
//                      COUNT(DISTINCT es.id_empleado) as participantes,
//                      CASE 
//                         WHEN aps.estado = 1 AND aps.fecha_cierre_sorteo >= CURDATE() THEN 'Activo'
//                         WHEN aps.estado = 1 AND aps.fecha_cierre_sorteo < CURDATE() THEN 'Finalizado'
//                         ELSE 'Inactivo'
//                      END as estado_texto
//               FROM apertura_sorteo aps
//               LEFT JOIN empleados_en_sorteo es ON aps.id = es.id_sorteo AND es.estado = 1
//               GROUP BY aps.id
//               ORDER BY aps.fecha_inicio_sorteo DESC";
    
//     $stmt = $conn->prepare($query);
//     $stmt->execute();
//     $sorteos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
//     echo json_encode(['data' => $sorteos]);
    
// } catch (Exception $e) {
//     error_log("Error en sorteos.php: " . $e->getMessage());
//     echo json_encode(['data' => []]);
// }


// api/sorteos.php
require_once '../config/config.php';
require_once '../config/database.php';

checkAdminPermission();

header('Content-Type: application/json; charset=utf-8');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT aps.id, aps.descripcion, 
                     DATE_FORMAT(aps.fecha_inicio_sorteo, '%Y-%m-%d') as fecha_inicio_sorteo,
                     DATE_FORMAT(aps.fecha_cierre_sorteo, '%Y-%m-%d') as fecha_cierre_sorteo,
                     aps.estado,
                     COUNT(DISTINCT es.id_empleado) as participantes,
                     CASE 
                        WHEN aps.estado = 1 AND aps.fecha_cierre_sorteo >= CURDATE() THEN 'Activo'
                        WHEN aps.estado = 1 AND aps.fecha_cierre_sorteo < CURDATE() THEN 'Finalizado'
                        ELSE 'Inactivo'
                     END as estado_texto
              FROM apertura_sorteo aps
              LEFT JOIN empleados_en_sorteo es ON aps.id = es.id_sorteo AND es.estado = 1
              GROUP BY aps.id, aps.descripcion, aps.fecha_inicio_sorteo, aps.fecha_cierre_sorteo, aps.estado
              ORDER BY aps.fecha_inicio_sorteo DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $sorteos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: verificar fechas
    foreach ($sorteos as &$sorteo) {
        // Asegurar que las fechas estén en formato correcto
        if (isset($sorteo['fecha_inicio_sorteo'])) {
            // Verificar que esté en formato YYYY-MM-DD
            $sorteo['fecha_inicio_display'] = $sorteo['fecha_inicio_sorteo'];
        }
        if (isset($sorteo['fecha_cierre_sorteo'])) {
            $sorteo['fecha_cierre_display'] = $sorteo['fecha_cierre_sorteo'];
        }
    }
    
    echo json_encode(['data' => $sorteos], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error en sorteos.php: " . $e->getMessage());
    echo json_encode(['data' => []]);
}
?>