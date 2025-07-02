<?php
// // process_login.php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// require_once 'config/config.php';
// require_once 'controllers/AuthController.php';

// // Headers para CORS y JSON
// header('Content-Type: application/json; charset=utf-8');
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: POST');
// header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// // Solo permitir POST
// if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//     http_response_code(405);
//     echo json_encode([
//         'success' => false, 
//         'message' => 'Método no permitido'
//     ]);
//     exit();
// }

// // Verificar si es una petición AJAX
// $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
//           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// try {
//     // Validar datos de entrada
//     $numero_documento = trim($_POST['numero_documento'] ?? '');
//     $password = $_POST['password'] ?? '';
    
//     if (empty($numero_documento) || empty($password)) {
//         $result = [
//             'success' => false, 
//             'message' => 'Todos los campos son obligatorios'
//         ];
//     } else {
//         $authController = new AuthController();
//         $result = $authController->login();
//     }
    
//     if ($isAjax) {
//         echo json_encode($result);
//     } else {
//         if ($result['success']) {
//             header('Location: dashboard.php');
//             exit();
//         } else {
//             header('Location: index.php?error=' . urlencode($result['message']));
//             exit();
//         }
//     }
    
// } catch (Exception $e) {
//     error_log("Error en process_login.php: " . $e->getMessage());
    
//     $error_response = [
//         'success' => false, 
//         'message' => 'Error interno del servidor',
//         'debug' => $e->getMessage() // Solo para desarrollo
//     ];
    
//     if ($isAjax) {
//         echo json_encode($error_response);
//     } else {
//         header('Location: index.php?error=' . urlencode($error_response['message']));
//         exit();
//     }
// }


// process_login.php
ob_start(); // Iniciar buffer de salida para evitar headers ya enviados

// Configuración de errores solo para desarrollo
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require_once 'config/config.php';
require_once 'controllers/AuthController.php';

// Verificar si es una petición AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Solo establecer headers JSON para peticiones AJAX
if ($isAjax) {
    ob_clean(); // Limpiar cualquier salida previa
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
}

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        http_response_code(405);
        echo json_encode([
            'success' => false, 
            'message' => 'Método no permitido'
        ]);
    } else {
        header('Location: index.php?error=' . urlencode('Método no permitido'));
    }
    exit();
}

try {
    // Validar datos de entrada
    $numero_documento = trim($_POST['numero_documento'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($numero_documento) || empty($password)) {
        $result = [
            'success' => false, 
            'message' => 'Todos los campos son obligatorios'
        ];
    } else {
        $authController = new AuthController();
        $result = $authController->login();
        
        // Asegurar que tenemos un resultado válido
        if (!isset($result['success'])) {
            $result = [
                'success' => false,
                'message' => 'Error en la respuesta del controlador'
            ];
        }
        
        // Si es exitoso y no tiene redirect, agregarlo
        if ($result['success'] && !isset($result['redirect'])) {
            $result['redirect'] = 'dashboard.php';
        }
    }
    
    if ($isAjax) {
        // Para AJAX, siempre devolver JSON válido
        ob_clean(); // Asegurar que no hay salida previa
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit();
    } else {
        // Para navegación tradicional
        if ($result['success']) {
            header('Location: dashboard.php');
        } else {
            header('Location: index.php?error=' . urlencode($result['message']));
        }
        exit();
    }
    
} catch (Exception $e) {
    // Log del error para debugging
    error_log("Error en process_login.php: " . $e->getMessage());
    
    $error_message = 'Error interno del servidor';
    
    if ($isAjax) {
        ob_clean();
        echo json_encode([
            'success' => false, 
            'message' => $error_message
        ], JSON_UNESCAPED_UNICODE);
    } else {
        header('Location: index.php?error=' . urlencode($error_message));
    }
    exit();
}

?>