<?php
require_once '../config/database.php';
require_once '../models/Usuario.php';


class UsuarioController {
    private $usuario;
  
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->usuario = new Usuario($this->conn);
       
    }


    public function registrarUsuario() {
        checkAdminPermission();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_empleado = filter_input(INPUT_POST, 'id_empleado', FILTER_VALIDATE_INT);
            $clave = filter_input(INPUT_POST, 'clave', FILTER_SANITIZE_STRING);
            $nivel_permiso = filter_input(INPUT_POST, 'nivel_permiso', FILTER_VALIDATE_INT);
            
            // Validaciones básicas
            if (!$id_empleado || empty($clave) || !$nivel_permiso) {
                return ['success' => false, 'message' => 'Todos los campos son obligatorios'];
            }

            // Validar longitud de contraseña
            if (strlen($clave) < 6) {
                return ['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'];
            }

            // Validar nivel de permiso válido
            if (!in_array($nivel_permiso, [1, 2])) {
                return ['success' => false, 'message' => 'Nivel de permiso inválido'];
            }
            
            // Registrar Usuario (solo una vez)
            $result = $this->usuario->registrarUsuarioModeradores($id_empleado, $clave, $nivel_permiso);
            
            if ($result['success']) {
                logActivity($_SESSION['user_id'], 'REGISTRAR_USUARIO', 
                          "Usuario moderador registrado: empleado ID {$id_empleado}");
                
                return ['success' => true, 'message' => 'Usuario registrado exitosamente'];
            }

            return $result;
        }
        
        return ['success' => false, 'message' => 'Método no permitido'];
    }

    public function getUsuarios() {
        try {
            $usuarios = $this->usuario->getUsuarios();
            return ['success' => true, 'data' => $usuarios];
        } catch (Exception $e) {
            error_log("Error en getUsuarios: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al obtener usuarios'];
        }
    }

    /**
 * Actualizar usuario
 */
    // public function actualizarUsuario() {
    //     checkAdminPermission();
        
    //     if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //         $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    //         $nivel_permiso = filter_input(INPUT_POST, 'nivel_permiso', FILTER_VALIDATE_INT);
    //         $estado = filter_input(INPUT_POST, 'estado', FILTER_VALIDATE_INT);
    //         $nueva_clave = filter_input(INPUT_POST, 'nueva_clave', FILTER_SANITIZE_STRING);
            
    //         if (!$id) {
    //             return ['success' => false, 'message' => 'ID de usuario requerido'];
    //         }

    //         // Validaciones
    //         if ($nivel_permiso !== null && !in_array($nivel_permiso, [1, 2])) {
    //             return ['success' => false, 'message' => 'Nivel de permiso inválido'];
    //         }

    //         if ($estado !== null && !in_array($estado, [0, 1])) {
    //             return ['success' => false, 'message' => 'Estado inválido'];
    //         }

    //         if (!empty($nueva_clave) && strlen($nueva_clave) < 6) {
    //             return ['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'];
    //         }

    //         $result = $this->usuario->actualizarUsuario($id, $nivel_permiso, $estado, $nueva_clave);
            
    //         if ($result['success']) {
    //             logActivity($_SESSION['user_id'], 'ACTUALIZAR_USUARIO', 
    //                     "Usuario actualizado: ID {$id}");
    //         }
            
    //         return $result;
    //     }
        
    //     return ['success' => false, 'message' => 'Método no permitido'];
    // }

    public function actualizarUsuario() {
    checkAdminPermission();
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        error_log("=== DEBUG ACTUALIZAR USUARIO ===");
        error_log("POST data: " . json_encode($_POST));
        
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nivel_permiso = filter_input(INPUT_POST, 'nivel_permiso', FILTER_VALIDATE_INT);
        $estado = filter_input(INPUT_POST, 'estado', FILTER_VALIDATE_INT);
        $nueva_clave = filter_input(INPUT_POST, 'nueva_clave', FILTER_SANITIZE_STRING);
        
        error_log("Valores filtrados:");
        error_log("- ID: " . var_export($id, true));
        error_log("- Estado: " . var_export($estado, true));
        error_log("- Tipo estado: " . gettype($estado));
        
        if (!$id) {
            return ['success' => false, 'message' => 'ID de usuario requerido'];
        }

        // ← CAMBIO: Verificar estado actual antes de actualizar
        $estadoActual = $this->usuario->getUsuarioById($id);
        if ($estadoActual) {
            error_log("Estado actual del usuario en BD: " . $estadoActual['estado']);
        }

        if ($estado !== null && !in_array($estado, [0, 1])) {
            error_log("ERROR: Estado inválido: $estado");
            return ['success' => false, 'message' => 'Estado inválido'];
        }

        $result = $this->usuario->actualizarUsuarioModel($id, $nivel_permiso, $estado, $nueva_clave);
        
        // ← CAMBIO: Verificar estado después de actualizar
        $estadoDespues = $this->usuario->getUsuarioById($id);
        if ($estadoDespues) {
            error_log("Estado después de actualizar: " . $estadoDespues['estado']);
        }
        
        return $result;
    }
    
    return ['success' => false, 'message' => 'Método no permitido'];
}


}
// require_once '../config/database.php';
// require_once '../models/Usuario.php';

// class UsuarioController {
//     private $usuario;
//     private $conn;

//     public function __construct() {
//         try {
//             error_log("DEBUG: Iniciando UsuarioController constructor");
//             $database = new Database();
//             $this->conn = $database->getConnection();
//             $this->usuario = new Usuario($this->conn);
//             error_log("DEBUG: UsuarioController constructor exitoso");
//         } catch (Exception $e) {
//             error_log("ERROR en constructor UsuarioController: " . $e->getMessage());
//             throw $e;
//         }
//     }

//     public function registrarUsuario() {
//         error_log("DEBUG: Iniciando registrarUsuario()");
        
//         try {
//             // Verificar permisos
//             if (!function_exists('checkAdminPermission')) {
//                 error_log("ERROR: Función checkAdminPermission no existe");
//                 return ['success' => false, 'message' => 'Error de configuración - función de permisos no encontrada'];
//             }
            
//             checkAdminPermission();
//             error_log("DEBUG: Permisos verificados correctamente");
            
//             if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//                 error_log("DEBUG: Método no es POST: " . $_SERVER['REQUEST_METHOD']);
//                 return ['success' => false, 'message' => 'Método no permitido'];
//             }
            
//             // Obtener y validar datos
//             $id_empleado = filter_input(INPUT_POST, 'id_empleado', FILTER_VALIDATE_INT);
//             $clave = filter_input(INPUT_POST, 'clave', FILTER_SANITIZE_STRING);
//             $nivel_permiso = filter_input(INPUT_POST, 'nivel_permiso', FILTER_VALIDATE_INT);
            
//             error_log("DEBUG: Datos recibidos - id_empleado: $id_empleado, clave: " . (empty($clave) ? 'VACÍA' : 'OK') . ", nivel_permiso: $nivel_permiso");
            
//             // Validaciones básicas
//             if (!$id_empleado) {
//                 error_log("ERROR: id_empleado inválido: " . var_export($_POST['id_empleado'], true));
//                 return ['success' => false, 'message' => 'ID de empleado inválido'];
//             }
            
//             if (empty($clave)) {
//                 error_log("ERROR: Contraseña vacía");
//                 return ['success' => false, 'message' => 'La contraseña es obligatoria'];
//             }
            
//             if (!$nivel_permiso) {
//                 error_log("ERROR: nivel_permiso inválido: " . var_export($_POST['nivel_permiso'], true));
//                 return ['success' => false, 'message' => 'Nivel de permiso inválido'];
//             }

//             // Validar longitud de contraseña
//             if (strlen($clave) < 6) {
//                 error_log("ERROR: Contraseña muy corta: " . strlen($clave) . " caracteres");
//                 return ['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'];
//             }

//             // Validar nivel de permiso válido
//             if (!in_array($nivel_permiso, [1, 2])) {
//                 error_log("ERROR: Nivel de permiso no válido: $nivel_permiso");
//                 return ['success' => false, 'message' => 'Nivel de permiso inválido (debe ser 1 o 2)'];
//             }
            
//             error_log("DEBUG: Todas las validaciones pasaron, llamando a registrarUsuarioModeradores");
            
//             // Registrar Usuario
//             $result = $this->usuario->registrarUsuarioModeradores($id_empleado, $clave, $nivel_permiso);
            
//             error_log("DEBUG: Resultado de registrarUsuarioModeradores: " . json_encode($result));
            
//             if ($result['success']) {
//                 // Verificar si existe la sesión y la función logActivity
//                 if (isset($_SESSION['user_id']) && function_exists('logActivity')) {
//                     error_log("DEBUG: Registrando actividad en log");
//                     logActivity($_SESSION['user_id'], 'REGISTRAR_USUARIO', 
//                               "Usuario moderador registrado: empleado ID {$id_empleado}");
//                 } else {
//                     error_log("WARNING: No se pudo registrar actividad - SESSION o logActivity no disponible");
//                 }
                
//                 return ['success' => true, 'message' => 'Usuario registrado exitosamente'];
//             }

//             return $result;
            
//         } catch (Exception $e) {
//             error_log("EXCEPCIÓN en registrarUsuario: " . $e->getMessage());
//             error_log("STACK TRACE: " . $e->getTraceAsString());
//             return ['success' => false, 'message' => 'Error interno del servidor'];
//         }
//     }
// }

// ?>
