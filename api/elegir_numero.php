<?php
require_once '../config/config.php';
require_once '../controllers/SorteoController.php';

checkSession();

if ($_SESSION['user_type'] !== 'concursante') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

header('Content-Type: application/json');

try {
    $sorteoController = new SorteoController();
    $result = $sorteoController->elegirNumero();
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Error en elegir_numero.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al elegir número']);
}
