<?php
require_once '../config/config.php';
require_once '../controllers/SorteoController.php';

checkAdminPermission();

header('Content-Type: application/json');

try {
    $sorteoController = new SorteoController();
    $result = $sorteoController->inscribirEmpleado();
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Error en inscribir_empleado.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al inscribir empleado']);
}

// api/inscribir_empleado.php - Versión completa con debug
// require_once '../config/config.php';
// require_once '../controllers/SorteoController.php';

// // Activar reporting de errores
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// ini_set('error_log', '../logs/php_errors.log');

// // Función de debug mejorada
// function debugLog($message, $data = null) {
//     $timestamp = date('Y-m-d H:i:s');
//     $logEntry = "[$timestamp] $message";
//     if ($data !== null) {
//         $logEntry .= " | Data: " . print_r($data, true);
//     }
//     $logEntry .= "\n";
//     file_put_contents('../logs/debug_inscripcion.log', $logEntry, FILE_APPEND | LOCK_EX);
// }

// // Función para obtener información de sesión
// function getSessionInfo() {
//     return [
//         'user_id' => $_SESSION['user_id'] ?? 'No definido',
//         'user_name' => $_SESSION['user_name'] ?? 'No definido',
//         'nivel_permiso' => $_SESSION['nivel_permiso'] ?? 'No definido',
//         'rol_nombre' => $_SESSION['rol_nombre'] ?? 'No definido'
//     ];
// }

// try {
//     debugLog("=== INICIO DE INSCRIPCIÓN DE EMPLEADO ===");
//     debugLog("Método HTTP", $_SERVER['REQUEST_METHOD']);
//     debugLog("Datos POST recibidos", $_POST);
//     debugLog("Información de sesión", getSessionInfo());
    
//     // Verificar si la sesión está iniciada
//     if (session_status() == PHP_SESSION_NONE) {
//         debugLog("ERROR: Sesión no iniciada");
//         throw new Exception('Sesión no iniciada');
//     }
    
//     // Verificar permisos administrativos
//     debugLog("Verificando permisos administrativos...");
    
//     // Verificar si checkAdminPermission existe
//     if (!function_exists('checkAdminPermission')) {
//         debugLog("ERROR: Función checkAdminPermission no existe");
//         throw new Exception('Función de verificación de permisos no disponible');
//     }
    
//     try {
//         checkAdminPermission();
//         debugLog("Permisos administrativos verificados correctamente");
//     } catch (Exception $e) {
//         debugLog("ERROR en checkAdminPermission", $e->getMessage());
//         throw new Exception('No tienes permisos administrativos: ' . $e->getMessage());
//     }
    
//     // Verificar método HTTP
//     if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//         debugLog("ERROR: Método HTTP incorrecto", $_SERVER['REQUEST_METHOD']);
//         throw new Exception('Método no permitido');
//     }
    
//     // Validar datos recibidos
//     $required_fields = ['id_sorteo', 'numero_documento', 'cantidad_elecciones', 'fecha_final'];
//     $missing_fields = [];
    
//     foreach ($required_fields as $field) {
//         if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
//             $missing_fields[] = $field;
//         }
//     }
    
//     if (!empty($missing_fields)) {
//         debugLog("ERROR: Campos faltantes", $missing_fields);
//         throw new Exception('Campos requeridos faltantes: ' . implode(', ', $missing_fields));
//     }
    
//     // Validar y sanear datos
//     $id_sorteo = filter_input(INPUT_POST, 'id_sorteo', FILTER_VALIDATE_INT);
//     $numero_documento = trim($_POST['numero_documento']);
//     $cantidad_elecciones = filter_input(INPUT_POST, 'cantidad_elecciones', FILTER_VALIDATE_INT);
//     $fecha_final = trim($_POST['fecha_final']);
    
//     debugLog("Datos validados", [
//         'id_sorteo' => $id_sorteo,
//         'numero_documento' => $numero_documento,
//         'cantidad_elecciones' => $cantidad_elecciones,
//         'fecha_final' => $fecha_final
//     ]);
    
//     // Validaciones específicas
//     if (!$id_sorteo || $id_sorteo <= 0) {
//         debugLog("ERROR: ID de sorteo inválido", $id_sorteo);
//         throw new Exception('ID de sorteo inválido');
//     }
    
//     if (strlen($numero_documento) < 6) {
//         debugLog("ERROR: Documento muy corto", $numero_documento);
//         throw new Exception('Número de documento debe tener al menos 6 dígitos');
//     }
    
//     if (!$cantidad_elecciones || $cantidad_elecciones < 1 || $cantidad_elecciones > 10) {
//         debugLog("ERROR: Cantidad de elecciones inválida", $cantidad_elecciones);
//         throw new Exception('Cantidad de elecciones debe estar entre 1 y 10');
//     }
    
//     // Validar fecha
//     $fecha_final_obj = DateTime::createFromFormat('Y-m-d', $fecha_final);
//     if (!$fecha_final_obj) {
//         debugLog("ERROR: Fecha final inválida", $fecha_final);
//         throw new Exception('Fecha final inválida');
//     }
    
//     $hoy = new DateTime();
//     if ($fecha_final_obj <= $hoy) {
//         debugLog("ERROR: Fecha final no es futura", [
//             'fecha_final' => $fecha_final,
//             'hoy' => $hoy->format('Y-m-d')
//         ]);
//         throw new Exception('La fecha final debe ser posterior a hoy');
//     }
    
//     debugLog("Todas las validaciones pasaron correctamente");
    
//     // Intentar crear el controlador
//     debugLog("Creando instancia del controlador...");
    
//     try {
//         $sorteoController = new SorteoController();
//         debugLog("Controlador creado exitosamente");
//     } catch (Exception $e) {
//         debugLog("ERROR al crear controlador", [
//             'mensaje' => $e->getMessage(),
//             'archivo' => $e->getFile(),
//             'linea' => $e->getLine()
//         ]);
//         throw new Exception('Error al inicializar el controlador: ' . $e->getMessage());
//     }
    
//     // Llamar al método del controlador
//     debugLog("Llamando al método inscribirEmpleado del controlador...");
    
//     try {
//         $result = $sorteoController->inscribirEmpleado();
//         debugLog("Resultado del controlador", $result);
//     } catch (Exception $e) {
//         debugLog("ERROR en el método inscribirEmpleado", [
//             'mensaje' => $e->getMessage(),
//             'archivo' => $e->getFile(),
//             'linea' => $e->getLine(),
//             'trace' => $e->getTraceAsString()
//         ]);
//         throw new Exception('Error en el proceso de inscripción: ' . $e->getMessage());
//     }
    
//     // Verificar estructura del resultado
//     if (!is_array($result)) {
//         debugLog("ERROR: Resultado no es un array", gettype($result));
//         throw new Exception('Respuesta del controlador inválida');
//     }
    
//     if (!isset($result['success'])) {
//         debugLog("ERROR: Resultado no tiene propiedad 'success'", $result);
//         throw new Exception('Respuesta del controlador malformada');
//     }
    
//     debugLog("Proceso completado exitosamente", $result);
    
//     // Enviar respuesta
//     header('Content-Type: application/json');
//     echo json_encode($result);
    
// } catch (Exception $e) {
//     $error_message = $e->getMessage();
//     $error_details = [
//         'mensaje' => $error_message,
//         'archivo' => $e->getFile(),
//         'linea' => $e->getLine(),
//         'trace' => $e->getTraceAsString()
//     ];
    
//     debugLog("ERROR CAPTURADO", $error_details);
    
//     http_response_code(400);
//     header('Content-Type: application/json');
//     echo json_encode([
//         'success' => false,
//         'message' => $error_message,
//         'debug' => $error_details,
//         'timestamp' => date('Y-m-d H:i:s')
//     ]);
    
// } catch (Throwable $e) {
//     $error_message = "Error crítico: " . $e->getMessage();
//     $error_details = [
//         'mensaje' => $error_message,
//         'archivo' => $e->getFile(),
//         'linea' => $e->getLine(),
//         'trace' => $e->getTraceAsString()
//     ];
    
//     debugLog("ERROR CRÍTICO", $error_details);
    
//     http_response_code(500);
//     header('Content-Type: application/json');
//     echo json_encode([
//         'success' => false,
//         'message' => 'Error interno del servidor',
//         'debug' => $error_details,
//         'timestamp' => date('Y-m-d H:i:s')
//     ]);
// }

// debugLog("=== FIN DE INSCRIPCIÓN DE EMPLEADO ===\n");
// ?>