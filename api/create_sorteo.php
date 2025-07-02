<?php
require_once '../config/config.php';
require_once '../controllers/SorteoController.php';

checkAdminPermission();

header('Content-Type: application/json');

try {
    $sorteoController = new SorteoController();
    $result = $sorteoController->crearSorteo();
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Error en create_sorteo.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al crear sorteo']);
}
