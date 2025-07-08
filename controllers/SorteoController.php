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
// ?>

<?php
// controllers/SorteoController.php - Versión con debug mejorado
// require_once '../config/database.php';
// require_once '../models/Sorteo.php';
// require_once '../models/User.php';

// class SorteoController {
//     private $sorteo;
//     private $user;
//     private $conn;

//     public function __construct() {
//         $this->debugLog("=== INICIALIZANDO SORTEO CONTROLLER ===");
        
//         try {
//             $this->debugLog("Creando conexión a base de datos...");
//             $database = new Database();
//             $this->conn = $database->getConnection();
            
//             if (!$this->conn) {
//                 throw new Exception("No se pudo establecer conexión con la base de datos");
//             }
            
//             $this->debugLog("Conexión a base de datos exitosa");
            
//             $this->debugLog("Creando instancia del modelo Sorteo...");
//             $this->sorteo = new Sorteo($this->conn);
//             $this->debugLog("Modelo Sorteo creado exitosamente");
            
//             $this->debugLog("Creando instancia del modelo User...");
//             $this->user = new User($this->conn);
//             $this->debugLog("Modelo User creado exitosamente");
            
//         } catch (Exception $e) {
//             $this->debugLog("ERROR en constructor: " . $e->getMessage());
//             throw new Exception("Error al inicializar el controlador: " . $e->getMessage());
//         }
//     }

//     private function debugLog($message, $data = null) {
//         $timestamp = date('Y-m-d H:i:s');
//         $logEntry = "[$timestamp] [SorteoController] $message";
//         if ($data !== null) {
//             $logEntry .= " | Data: " . print_r($data, true);
//         }
//         $logEntry .= "\n";
//         file_put_contents('../logs/debug_controller.log', $logEntry, FILE_APPEND | LOCK_EX);
//     }

//     public function crearSorteo() {
//         $this->debugLog("=== MÉTODO CREAR SORTEO ===");
        
//         try {
//             checkAdminPermission();
//             $this->debugLog("Permisos administrativos verificados");
            
//             if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//                 $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
//                 $fecha_inicio = filter_input(INPUT_POST, 'fecha_inicio', FILTER_SANITIZE_STRING);
//                 $fecha_cierre = filter_input(INPUT_POST, 'fecha_cierre', FILTER_SANITIZE_STRING);

//                 $this->debugLog("Datos recibidos para crear sorteo", [
//                     'descripcion' => $descripcion,
//                     'fecha_inicio' => $fecha_inicio,
//                     'fecha_cierre' => $fecha_cierre
//                 ]);

//                 if (empty($descripcion) || empty($fecha_inicio) || empty($fecha_cierre)) {
//                     return ['success' => false, 'message' => 'Todos los campos son obligatorios'];
//                 }

//                 if ($fecha_inicio >= $fecha_cierre) {
//                     return ['success' => false, 'message' => 'La fecha de cierre debe ser posterior a la de inicio'];
//                 }

//                 $id_sorteo = $this->sorteo->create($descripcion, $fecha_inicio, $fecha_cierre);
                
//                 if ($id_sorteo) {
//                     logActivity($_SESSION['user_id'], 'CREATE_SORTEO', "Sorteo creado: $descripcion");
//                     return ['success' => true, 'message' => 'Sorteo creado exitosamente', 'id' => $id_sorteo];
//                 }

//                 return ['success' => false, 'message' => 'Error al crear el sorteo'];
//             }
//         } catch (Exception $e) {
//             $this->debugLog("ERROR en crearSorteo: " . $e->getMessage());
//             return ['success' => false, 'message' => 'Error al crear sorteo: ' . $e->getMessage()];
//         }
//     }

//     public function inscribirEmpleado() {
//         $this->debugLog("=== MÉTODO INSCRIBIR EMPLEADO ===");
        
//         try {
//             $this->debugLog("Verificando permisos administrativos...");
//             checkAdminPermission();
//             $this->debugLog("Permisos administrativos verificados");
            
//             if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//                 $this->debugLog("Método POST verificado, procesando datos...");
                
//                 // Obtener y validar datos
//                 $id_sorteo = filter_input(INPUT_POST, 'id_sorteo', FILTER_VALIDATE_INT);
//                 $numero_documento = filter_input(INPUT_POST, 'numero_documento', FILTER_SANITIZE_STRING);
//                 $cantidad_elecciones = filter_input(INPUT_POST, 'cantidad_elecciones', FILTER_VALIDATE_INT);
//                 $fecha_final = filter_input(INPUT_POST, 'fecha_final', FILTER_SANITIZE_STRING);

//                 $this->debugLog("Datos filtrados", [
//                     'id_sorteo' => $id_sorteo,
//                     'numero_documento' => $numero_documento,
//                     'cantidad_elecciones' => $cantidad_elecciones,
//                     'fecha_final' => $fecha_final
//                 ]);

//                 // Validaciones
//                 if (!$id_sorteo || empty($numero_documento) || !$cantidad_elecciones || empty($fecha_final)) {
//                     $this->debugLog("ERROR: Campos obligatorios faltantes");
//                     return ['success' => false, 'message' => 'Todos los campos son obligatorios'];
//                 }

//                 if ($cantidad_elecciones < 1 || $cantidad_elecciones > 10) {
//                     $this->debugLog("ERROR: Cantidad de elecciones inválida", $cantidad_elecciones);
//                     return ['success' => false, 'message' => 'La cantidad de elecciones debe estar entre 1 y 10'];
//                 }

//                 // Obtener empleado
//                 $this->debugLog("Buscando empleado por documento: " . $numero_documento);
                
//                 if (!method_exists($this->user, 'getEmpleadoByDocumento')) {
//                     $this->debugLog("ERROR: Método getEmpleadoByDocumento no existe en el modelo User");
//                     return ['success' => false, 'message' => 'Error en el sistema: método de búsqueda no disponible'];
//                 }
                
//                 try {
//                     $empleado = $this->user->getEmpleadoByDocumento($numero_documento);
//                     $this->debugLog("Resultado de búsqueda de empleado", $empleado);
//                 } catch (Exception $e) {
//                     $this->debugLog("ERROR al buscar empleado: " . $e->getMessage());
//                     return ['success' => false, 'message' => 'Error al buscar empleado: ' . $e->getMessage()];
//                 }
                
//                 if (!$empleado) {
//                     $this->debugLog("ERROR: Empleado no encontrado", $numero_documento);
//                     return ['success' => false, 'message' => 'Empleado no encontrado'];
//                 }

//                 $this->debugLog("Empleado encontrado exitosamente", $empleado);

//                 // Verificar que el sorteo existe
//                 $this->debugLog("Verificando que el sorteo existe...");
//                 // Aquí podrías agregar una verificación adicional del sorteo si es necesario

//                 // Inscribir empleado
//                 $this->debugLog("Inscribiendo empleado en sorteo...");
                
//                 if (!method_exists($this->sorteo, 'inscribirEmpleado')) {
//                     $this->debugLog("ERROR: Método inscribirEmpleado no existe en el modelo Sorteo");
//                     return ['success' => false, 'message' => 'Error en el sistema: método de inscripción no disponible'];
//                 }
                
//                 try {
//                     $result = $this->sorteo->inscribirEmpleado($id_sorteo, $empleado['id'], $cantidad_elecciones, $fecha_final);
//                     $this->debugLog("Resultado de inscripción", $result);
//                 } catch (Exception $e) {
//                     $this->debugLog("ERROR en inscribirEmpleado: " . $e->getMessage());
//                     return ['success' => false, 'message' => 'Error al inscribir empleado: ' . $e->getMessage()];
//                 }
                
//                 if ($result['success']) {
//                     $this->debugLog("Inscripción exitosa, creando usuario concursante...");
                    
//                     // Crear usuario concursante con contraseña = número de documento
//                     if (!method_exists($this->user, 'createConcursante')) {
//                         $this->debugLog("ADVERTENCIA: Método createConcursante no existe en el modelo User");
//                     } else {
//                         try {
//                             $this->user->createConcursante($result['id'], $numero_documento);
//                             $this->debugLog("Usuario concursante creado exitosamente");
//                         } catch (Exception $e) {
//                             $this->debugLog("ERROR al crear usuario concursante: " . $e->getMessage());
//                             // No retornar error aquí, ya que la inscripción fue exitosa
//                         }
//                     }
                    
//                     // Log de actividad
//                     if (function_exists('logActivity')) {
//                         try {
//                             logActivity($_SESSION['user_id'], 'INSCRIBIR_EMPLEADO', 
//                                       "Empleado inscrito: {$empleado['nombre_completo']} en sorteo $id_sorteo");
//                             $this->debugLog("Actividad registrada exitosamente");
//                         } catch (Exception $e) {
//                             $this->debugLog("ERROR al registrar actividad: " . $e->getMessage());
//                         }
//                     } else {
//                         $this->debugLog("ADVERTENCIA: Función logActivity no disponible");
//                     }
                    
//                     $this->debugLog("Proceso de inscripción completado exitosamente");
//                     return ['success' => true, 'message' => 'Empleado inscrito exitosamente'];
//                 } else {
//                     $this->debugLog("ERROR: Inscripción falló", $result);
//                     return $result;
//                 }
//             } else {
//                 $this->debugLog("ERROR: Método HTTP no es POST", $_SERVER['REQUEST_METHOD']);
//                 return ['success' => false, 'message' => 'Método no permitido'];
//             }
//         } catch (Exception $e) {
//             $this->debugLog("ERROR CRÍTICO en inscribirEmpleado", [
//                 'mensaje' => $e->getMessage(),
//                 'archivo' => $e->getFile(),
//                 'linea' => $e->getLine(),
//                 'trace' => $e->getTraceAsString()
//             ]);
//             return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
//         }
//     }

//     public function elegirNumero() {
//         $this->debugLog("=== MÉTODO ELEGIR NÚMERO ===");
        
//         try {
//             checkSession();
            
//             if ($_SESSION['user_type'] !== 'concursante') {
//                 return ['success' => false, 'message' => 'No tienes permisos para esta acción'];
//             }

//             if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//                 $numero_balota = filter_input(INPUT_POST, 'numero_balota', FILTER_VALIDATE_INT);

//                 if (!$numero_balota || $numero_balota < 100 || $numero_balota > 800) {
//                     return ['success' => false, 'message' => 'Número de balota inválido (100-800)'];
//                 }

//                 $result = $this->sorteo->elegirNumero($_SESSION['id_empleado_sort'], $numero_balota);
                
//                 if ($result['success']) {
//                     logActivity($_SESSION['user_id'], 'ELEGIR_NUMERO', "Número elegido: $numero_balota");
//                 }

//                 return $result;
//             }
//         } catch (Exception $e) {
//             $this->debugLog("ERROR en elegirNumero: " . $e->getMessage());
//             return ['success' => false, 'message' => 'Error al elegir número: ' . $e->getMessage()];
//         }
//     }
// }
?>