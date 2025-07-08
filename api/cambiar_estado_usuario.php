
<?php
// ===== CREAR ESTE ARCHIVO: api/cambiar_estado_usuario.php =====
require_once '../config/database.php';  // ← Incluir database.php directamente
require_once '../config/config.php';
require_once '../models/Usuario.php';


checkAdminPermission();

header('Content-Type: application/json');

try {
    // Validar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit;
    }
    
    // Obtener y validar datos
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $nuevo_estado = filter_input(INPUT_POST, 'estado', FILTER_VALIDATE_INT);
    
    // Log de entrada
    error_log("=== CAMBIAR ESTADO USUARIO ===");
    error_log("ID recibido: " . var_export($id, true));
    error_log("Estado recibido: " . var_export($nuevo_estado, true));
    error_log("POST completo: " . json_encode($_POST));
    
    // Validaciones
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
        exit;
    }
    
    if ($nuevo_estado === null || !in_array($nuevo_estado, [0, 1])) {
        echo json_encode(['success' => false, 'message' => 'Estado inválido (debe ser 0 o 1)']);
        exit;
    }
    
    // Conectar a BD y modelo
    $database = new Database();
    $conn = $database->getConnection();
    $usuario = new Usuario($conn);
    
    // Verificar que el usuario existe y obtener estado actual
    $usuarioActual = $usuario->getUsuarioById($id);
    if (!$usuarioActual) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    $estadoActual = (int) $usuarioActual['estado'];
    error_log("Estado actual del usuario: $estadoActual");
    error_log("Nuevo estado solicitado: $nuevo_estado");
    
    // Verificar si ya tiene ese estado
    if ($estadoActual === $nuevo_estado) {
        $mensaje = $nuevo_estado === 1 ? 'El usuario ya está activo' : 'El usuario ya está inactivo';
        echo json_encode(['success' => true, 'message' => $mensaje, 'sin_cambios' => true]);
        exit;
    }
    
    // Actualizar estado usando el método que ya tienes
    $resultado = $usuario->cambiarEstadoUsuario($id, $nuevo_estado);
    
    error_log("Resultado de cambio: " . json_encode($resultado));
    
    if ($resultado['success']) {
        // Log de actividad
        $accion = $nuevo_estado === 1 ? 'ACTIVAR_USUARIO' : 'INACTIVAR_USUARIO';
        $descripcion = $nuevo_estado === 1 ? 'Usuario activado' : 'Usuario inactivado';
        
        if (function_exists('logActivity')) {
            logActivity($_SESSION['user_id'], $accion, "$descripcion: ID $id");
        }
        
        echo json_encode([
            'success' => true, 
            'message' => $resultado['message'],
            'estado_anterior' => $estadoActual,
            'estado_nuevo' => $nuevo_estado
        ]);
    } else {
        echo json_encode($resultado);
    }
    
} catch (Exception $e) {
    error_log("Error en cambiar_estado_usuario.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>