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
