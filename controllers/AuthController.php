<?php
require_once 'config/database.php';
require_once 'models/User.php';

class AuthController {
    private $user;
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->user = new User($this->conn);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $numero_documento = filter_input(INPUT_POST, 'numero_documento', FILTER_SANITIZE_STRING);
            $password = $_POST['password'];

            if (empty($numero_documento) || empty($password)) {
                return ['success' => false, 'message' => 'Todos los campos son obligatorios'];
            }

            $result = $this->user->authenticate($numero_documento, $password);
            
            if ($result['success']) {
                $_SESSION['user_id'] = $result['user_data']['id_empleado'] ?? $result['user_data']['id_empleado_sort'];
                $_SESSION['user_type'] = $result['user_type'];
                $_SESSION['user_name'] = $result['user_data']['nombre_completo'];
                $_SESSION['user_document'] = $result['user_data']['numero_documento'];
                $_SESSION['last_activity'] = time();

                

                // CORREGIDO: Manejar roles correctamente
                if ($result['user_type'] == 'moderador') {
                    // Capturar directamente los datos que envía User.php
                    $_SESSION['nivel_permiso'] = $result['user_data']['nivel_permiso'];
                    $_SESSION['rol_nombre'] = $result['user_data']['rol_nombre'];
                    
                    // Debug temporal - ELIMINAR después de probar
                    error_log("DEBUG - Datos recibidos: " . print_r($result['user_data'], true));
                    error_log("DEBUG - nivel_permiso: " . $result['user_data']['nivel_permiso']);
                    error_log("DEBUG - rol_nombre: " . $result['user_data']['rol_nombre']);
                    
                } elseif ($result['user_type'] == 'concursante') {
                    $_SESSION['rol_nombre'] = 'Empleado';
                    $_SESSION['id_empleado_sort'] = $result['user_data']['id_empleado_sort'];
                    $_SESSION['elecciones_disponibles'] = $result['user_data']['cantidad_elecciones'];
                }

                // Registrar login en logs
                if (function_exists('logActivity')) {
                    logActivity($_SESSION['user_id'], 'LOGIN', 'Usuario ingresó al sistema');
                }

                return ['success' => true, 'redirect' => 'dashboard.php'];
            }

            return $result;
        }
    }

    public function logout() {
        if (isset($_SESSION['user_id'])) {
            logActivity($_SESSION['user_id'], 'LOGOUT', 'Usuario cerró sesión');
        }
        
        session_destroy();
        header('Location: index.php');
        exit();
    }
}
