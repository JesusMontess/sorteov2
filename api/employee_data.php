<?php
require_once '../config/config.php';
require_once '../config/database.php';

checkSession();

if ($_SESSION['user_type'] !== 'concursante') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    $id_empleado_sort = $_SESSION['id_empleado_sort'];
    
    // Estadísticas del empleado
    $query = "SELECT es.cantidad_elecciones,
                     (SELECT COUNT(*) FROM balota_concursante WHERE id_empleado_sort = es.id) as numeros_elegidos,
                     aps.descripcion as sorteo_nombre,
                     aps.fecha_cierre_sorteo
              FROM empleados_en_sorteo es
              INNER JOIN apertura_sorteo aps ON es.id_sorteo = aps.id
              WHERE es.id = :id_empleado_sort";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_empleado_sort', $id_empleado_sort);
    $stmt->execute();
    $empleado_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stats = [];
    if ($empleado_data) {
        $stats['elecciones_disponibles'] = $empleado_data['cantidad_elecciones'];
        $stats['numeros_elegidos'] = $empleado_data['numeros_elegidos'];
        $stats['elecciones_restantes'] = $empleado_data['cantidad_elecciones'] - $empleado_data['numeros_elegidos'];
        $stats['fecha_cierre'] = $empleado_data['fecha_cierre_sorteo'];
    }
    
    // Info del sorteo
    $query = "SELECT aps.descripcion as nombre,
                     COUNT(DISTINCT es.id_empleado) as total_participantes,
                     (701 - COUNT(bc.numero_balota)) as numeros_disponibles
              FROM apertura_sorteo aps
              INNER JOIN empleados_en_sorteo es ON aps.id = es.id_sorteo
              LEFT JOIN balota_concursante bc ON es.id = bc.id_empleado_sort
              WHERE es.id = :id_empleado_sort
              GROUP BY aps.id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_empleado_sort', $id_empleado_sort);
    $stmt->execute();
    $sorteo_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Mis números
    $query = "SELECT bc.numero_balota, bc.fecha_eleccion, b.equivalencia_binaria
              FROM balota_concursante bc
              INNER JOIN balotas b ON bc.numero_balota = b.numero_balota
              WHERE bc.id_empleado_sort = :id_empleado_sort
              ORDER BY bc.fecha_eleccion DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_empleado_sort', $id_empleado_sort);
    $stmt->execute();
    $mis_numeros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'sorteo_info' => $sorteo_info ?: [],
        'mis_numeros' => $mis_numeros
    ]);
    
} catch (Exception $e) {
    error_log("Error en employee_data.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al cargar datos']);
}
