<?php
require_once '../config/database.php';  // ← Incluir database.php directamente
require_once '../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);



header('Content-Type: application/json');

try {
    // Verificar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Solo método POST']);
        exit;
    }
    
    // Verificar sesión
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Sin sesión']);
        exit;
    }
    
    // Obtener datos POST
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : -1;
    
    // Validar datos
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit;
    }
    
    if ($estado !== 0 && $estado !== 1) {
        echo json_encode(['success' => false, 'message' => 'Estado debe ser 0 o 1']);
        exit;
    }
    
    // Conectar BD
    $database = new Database();
    $conn = $database->getConnection();
    
    // Actualizar estado
    $sql = "UPDATE usuario_moderador SET estado = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([$estado, $id]);
    
    if ($result) {
        $affected = $stmt->rowCount();
        
        if ($affected > 0) {
            $mensaje = $estado == 1 ? 'Usuario activado' : 'Usuario inactivado';
            echo json_encode(['success' => true, 'message' => $mensaje]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se actualizó']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en query']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>