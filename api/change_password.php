<?php
// api/change_password.php - VERSION CON DEBUG
require_once '../config/database.php';
require_once '../config/config.php';

header('Content-Type: application/json');

// DEBUG: Log de lo que llega
error_log("METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("POST DATA: " . print_r($_POST, true));

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar sesión activa
checkSession();

// DEBUG: Log de sesión
error_log("SESSION DATA: " . print_r($_SESSION, true));

// Validar que sea un empleado (concursante)
if ($_SESSION['user_type'] !== 'concursante') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

// Verificar que existe id_empleado_sort en sesión
if (!isset($_SESSION['id_empleado_sort'])) {
    echo json_encode(['success' => false, 'message' => 'Error de sesión: falta id_empleado_sort']);
    exit;
}

try {
    // Obtener los datos del POST
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // DEBUG: Log de datos recibidos
    error_log("PASSWORDS RECEIVED - Current: " . (!empty($current_password) ? '[SET]' : '[EMPTY]') . 
              ", New: " . (!empty($new_password) ? '[SET]' : '[EMPTY]') . 
              ", Confirm: " . (!empty($confirm_password) ? '[SET]' : '[EMPTY]'));

    // Validaciones básicas
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    // Validar que las contraseñas nuevas coincidan
    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Las contraseñas nuevas no coinciden']);
        exit;
    }

    // Validar longitud mínima de contraseña
    if (strlen($new_password) < 6) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
        exit;
    }

    // Validar que la nueva contraseña sea diferente a la actual
    if ($current_password === $new_password) {
        echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe ser diferente a la actual']);
        exit;
    }

    // Conectar a la base de datos
    $database = new Database();
    $conn = $database->getConnection();

    // DEBUG: Log de conexión
    error_log("DATABASE CONNECTION: " . ($conn ? 'SUCCESS' : 'FAILED'));

    // Obtener la información del usuario concursante
    $query = "SELECT uc.id, uc.clave, e.numero_documento, e.nombre_completo 
              FROM usuario_concurso uc
              INNER JOIN empleados_en_sorteo es ON uc.id_empleado_sort = es.id
              INNER JOIN empleados e ON es.id_empleado = e.id
              WHERE es.id = :id_empleado_sort AND uc.estado = 1";
    
    error_log("QUERY: " . $query);
    error_log("ID_EMPLEADO_SORT: " . $_SESSION['id_empleado_sort']);
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_empleado_sort', $_SESSION['id_empleado_sort']);
    $stmt->execute();

    error_log("QUERY RESULT COUNT: " . $stmt->rowCount());

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }

    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // DEBUG: Log de datos de usuario (sin contraseña)
    error_log("USER DATA: " . json_encode([
        'id' => $user_data['id'],
        'numero_documento' => $user_data['numero_documento'],
        'nombre_completo' => $user_data['nombre_completo']
    ]));

    // Verificar la contraseña actual
    if (!password_verify($current_password, $user_data['clave'])) {
        echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
        exit;
    }

    // Encriptar la nueva contraseña
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Actualizar la contraseña en la base de datos
    $update_query = "UPDATE usuario_concurso SET clave = :nueva_clave WHERE id = :user_id";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bindParam(':nueva_clave', $hashed_password);
    $update_stmt->bindParam(':user_id', $user_data['id']);

    $update_result = $update_stmt->execute();
    
    error_log("UPDATE RESULT: " . ($update_result ? 'SUCCESS' : 'FAILED'));

    if ($update_result) {
        // Log de actividad
        if (function_exists('logActivity')) {
            logActivity($_SESSION['user_id'], 'CAMBIO_CONTRASEÑA', 
                       'Empleado cambió su contraseña desde el panel');
        }

        echo json_encode([
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar la contraseña'
        ]);
    }

} catch(PDOException $e) {
    error_log("PDO ERROR: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    error_log("GENERAL ERROR: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>