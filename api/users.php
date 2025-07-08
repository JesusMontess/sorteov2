<?php
// api/users.php
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../controllers/UserController.php';

// Verificar que solo administradores puedan acceder
checkAdminPermission();

$userController = new UserController();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch($action) {
        case 'get_all':
            $result = $userController->getAllUsers();
            if ($result['success']) {
                echo json_encode([
                    'data' => $result['data']
                ]);
            } else {
                http_response_code(500);
                echo json_encode($result);
            }
            break;

        case 'create':
        default:
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['action']) && $_POST['action'] === 'update') {
                    $result = $userController->updateUser();
                } elseif (isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
                    $result = $userController->toggleUserStatus();
                } else {
                    $result = $userController->createUser();
                }
                
                echo json_encode($result);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            }
            break;
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?>