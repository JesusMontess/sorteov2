<?php
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
    
    // Obtener información del sorteo
    $query_sorteo = "SELECT id, descripcion, 
                            DATE_FORMAT(fecha_inicio_sorteo, '%Y-%m-%d') as fecha_inicio_sorteo,
                            DATE_FORMAT(fecha_cierre_sorteo, '%Y-%m-%d') as fecha_cierre_sorteo,
                            estado
                     FROM apertura_sorteo 
                     WHERE id = :id";
    
    $stmt_sorteo = $conn->prepare($query_sorteo);
    $stmt_sorteo->bindParam(':id', $sorteo_id, PDO::PARAM_INT);
    $stmt_sorteo->execute();
    
    $sorteo = $stmt_sorteo->fetch(PDO::FETCH_ASSOC);
    
    if (!$sorteo) {
        echo json_encode(['success' => false, 'message' => 'Sorteo no encontrado']);
        exit();
    }
    
    // Obtener participantes
    $query_participantes = "SELECT e.numero_documento, e.nombre_completo, e.cargo,
                                   es.cantidad_elecciones,
                                   COUNT(bc.id) as elecciones_usadas
                            FROM empleados_en_sorteo es
                            INNER JOIN empleados e ON es.id_empleado = e.id
                            LEFT JOIN balota_concursante bc ON es.id = bc.id_empleado_sort
                            WHERE es.id_sorteo = :sorteo_id AND es.estado = 1
                            GROUP BY es.id, e.numero_documento, e.nombre_completo, e.cargo, es.cantidad_elecciones
                            ORDER BY e.nombre_completo";
    
    $stmt_participantes = $conn->prepare($query_participantes);
    $stmt_participantes->bindParam(':sorteo_id', $sorteo_id, PDO::PARAM_INT);
    $stmt_participantes->execute();
    
    $participantes = $stmt_participantes->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener estadísticas
    $query_stats = "SELECT 
                        COUNT(DISTINCT es.id_empleado) as total_participantes,
                        COUNT(bc.id) as numeros_elegidos,
                        (701 - COUNT(bc.id)) as numeros_disponibles
                    FROM empleados_en_sorteo es
                    LEFT JOIN balota_concursante bc ON es.id = bc.id_empleado_sort
                    WHERE es.id_sorteo = :sorteo_id AND es.estado = 1";
    
    $stmt_stats = $conn->prepare($query_stats);
    $stmt_stats->bindParam(':sorteo_id', $sorteo_id, PDO::PARAM_INT);
    $stmt_stats->execute();
    
    $estadisticas = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'sorteo' => $sorteo,
        'participantes' => $participantes,
        'estadisticas' => $estadisticas
    ]);
    
} catch (Exception $e) {
    error_log("Error en get_sorteo_details.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>