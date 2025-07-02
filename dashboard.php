<?php
// dashboard.php
require_once 'config/config.php';
checkSession();

// Redirigir según el tipo de usuario
if ($_SESSION['user_type'] === 'moderador') {
    // Cargar dashboard de administrador
    include 'views/admin_dashboard.php';
} else {
    // Cargar dashboard de empleado
    include 'views/employee_dashboard.php';
}
?>