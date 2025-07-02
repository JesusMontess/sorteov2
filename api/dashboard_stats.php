<?php
// api/dashboard_stats.php
require_once '../config/config.php';
require_once '../config/database.php';

checkSession();
checkAdminPermission();

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Estadísticas generales
    $stats = [];
    
    // Total sorteos activos
    $query = "SELECT COUNT(*) as total FROM apertura_sorteo WHERE estado = 1 AND fecha_cierre_sorteo >= CURDATE()";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stats['total_sorteos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total participantes
    $query = "SELECT COUNT(DISTINCT id_empleado) as total FROM empleados_en_sorteo WHERE estado = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stats['total_participantes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Números elegidos
    $query = "SELECT COUNT(*) as total FROM balota_concursante bc 
              INNER JOIN empleados_en_sorteo es ON bc.id_empleado_sort = es.id
              WHERE es.estado = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stats['numeros_elegidos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Actividad de hoy
    $query = "SELECT COUNT(*) as total FROM system_logs WHERE DATE(fecha) = CURDATE()";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stats['actividad_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Sorteos recientes
    $query = "SELECT * FROM apertura_sorteo ORDER BY fecha_inicio_sorteo DESC LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $sorteos_recientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Actividad reciente
    $query = "SELECT sl.*, e.nombre_completo 
              FROM system_logs sl
              LEFT JOIN empleados e ON sl.empleado_id = e.id
              ORDER BY sl.fecha DESC LIMIT 10";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $actividad_reciente = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'sorteos_recientes' => $sorteos_recientes,
        'actividad_reciente' => $actividad_reciente
    ]);
    
} catch (Exception $e) {
    error_log("Error en dashboard_stats.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al cargar estadísticas']);
}