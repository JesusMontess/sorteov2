<?php
require_once '../config/config.php';
require_once '../controllers/UsuarioController.php';

checkAdminPermission();

header('Content-Type: application/json');

try {
    $usuarioController = new UsuarioController();
    $result = $usuarioController->registrarUsuario();
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Error en inscribir_empleado.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al inscribir empleado']);
}

// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// error_log("DEBUG: Iniciando registrar_usuario.php");

// require_once '../config/config.php';
// require_once '../controllers/UsuarioController.php';

// // Verificar que existe la función checkAdminPermission
// if (!function_exists('checkAdminPermission')) {
//     error_log("ERROR: checkAdminPermission function not found");
//     echo json_encode(['success' => false, 'message' => 'Error de configuración del sistema']);
//     exit;
// }

// checkAdminPermission();

// header('Content-Type: application/json');

// error_log("DEBUG: POST data: " . json_encode($_POST));

// try {
//     $usuarioController = new UsuarioController();
//     $result = $usuarioController->registrarUsuario();
//     error_log("DEBUG: Resultado final: " . json_encode($result));
//     echo json_encode($result);
// } catch (Exception $e) {
//     error_log("EXCEPCIÓN en registrar_usuario.php: " . $e->getMessage());
//     error_log("STACK TRACE: " . $e->getTraceAsString());
//     echo json_encode(['success' => false, 'message' => 'Error al registrar usuario: ' . $e->getMessage()]);
// }
