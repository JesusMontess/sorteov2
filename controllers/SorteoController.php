<?php
require_once '../config/database.php';
require_once '../models/Sorteo.php';
require_once '../models/User.php';

class SorteoController {
    private $sorteo;
    private $user;
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->sorteo = new Sorteo($this->conn);
        $this->user = new User($this->conn);
    }

    public function crearSorteo() {
        checkAdminPermission();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
            $fecha_inicio = filter_input(INPUT_POST, 'fecha_inicio', FILTER_SANITIZE_STRING);
            $fecha_cierre = filter_input(INPUT_POST, 'fecha_cierre', FILTER_SANITIZE_STRING);

            if (empty($descripcion) || empty($fecha_inicio) || empty($fecha_cierre)) {
                return ['success' => false, 'message' => 'Todos los campos son obligatorios'];
            }

            if ($fecha_inicio >= $fecha_cierre) {
                return ['success' => false, 'message' => 'La fecha de cierre debe ser posterior a la de inicio'];
            }

            $id_sorteo = $this->sorteo->create($descripcion, $fecha_inicio, $fecha_cierre);
            
            if ($id_sorteo) {
                logActivity($_SESSION['user_id'], 'CREATE_SORTEO', "Sorteo creado: $descripcion");
                return ['success' => true, 'message' => 'Sorteo creado exitosamente', 'id' => $id_sorteo];
            }

            return ['success' => false, 'message' => 'Error al crear el sorteo'];
        }
    }

    public function inscribirEmpleado() {
        checkAdminPermission();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_sorteo = filter_input(INPUT_POST, 'id_sorteo', FILTER_VALIDATE_INT);
            $numero_documento = filter_input(INPUT_POST, 'numero_documento', FILTER_SANITIZE_STRING);
            $cantidad_elecciones = filter_input(INPUT_POST, 'cantidad_elecciones', FILTER_VALIDATE_INT);
            $fecha_final = filter_input(INPUT_POST, 'fecha_final', FILTER_SANITIZE_STRING);

            if (!$id_sorteo || empty($numero_documento) || !$cantidad_elecciones || empty($fecha_final)) {
                return ['success' => false, 'message' => 'Todos los campos son obligatorios'];
            }

            if ($cantidad_elecciones < 1 || $cantidad_elecciones > 10) {
                return ['success' => false, 'message' => 'La cantidad de elecciones debe estar entre 1 y 10'];
            }

            // Obtener empleado
            $empleado = $this->user->getEmpleadoByDocumento($numero_documento);
            if (!$empleado) {
                return ['success' => false, 'message' => 'Empleado no encontrado'];
            }

            // Inscribir empleado
            $result = $this->sorteo->inscribirEmpleado($id_sorteo, $empleado['id'], $cantidad_elecciones, $fecha_final);
            
            if ($result['success']) {
                // Crear usuario concursante con contraseña = número de documento
                $this->user->createConcursante($result['id'], $numero_documento);
                
                logActivity($_SESSION['user_id'], 'INSCRIBIR_EMPLEADO', 
                          "Empleado inscrito: {$empleado['nombre_completo']} en sorteo $id_sorteo");
                
                return ['success' => true, 'message' => 'Empleado inscrito exitosamente'];
            }

            return $result;
        }
    }

    public function elegirNumero() {
        checkSession();
        
        if ($_SESSION['user_type'] !== 'concursante') {
            return ['success' => false, 'message' => 'No tienes permisos para esta acción'];
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $numero_balota = filter_input(INPUT_POST, 'numero_balota', FILTER_VALIDATE_INT);

            if (!$numero_balota || $numero_balota < 100 || $numero_balota > 800) {
                return ['success' => false, 'message' => 'Número de balota inválido (100-800)'];
            }

            $result = $this->sorteo->elegirNumero($_SESSION['id_empleado_sort'], $numero_balota);
            
            if ($result['success']) {
                logActivity($_SESSION['user_id'], 'ELEGIR_NUMERO', "Número elegido: $numero_balota");
            }

            return $result;
        }
    }
}
?>