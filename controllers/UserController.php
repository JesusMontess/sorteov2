<?php
// controllers/UserController.php
require_once '../config/database.php';
require_once 'models/User.php';

class UserController {
    private $user;
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->user = new User($this->conn);
    }

    public function getAllUsers() {
        try {
            $users = $this->user->getAllModerators();
            
            return [
                'success' => true,
                'data' => $users
            ];
        } catch(Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function createUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Método no permitido'];
        }

        try {
            $numero_documento = filter_input(INPUT_POST, 'numero_documento', FILTER_SANITIZE_STRING);
            $clave = $_POST['clave'];
            $nivel_permiso = filter_input(INPUT_POST, 'nivel_permiso', FILTER_VALIDATE_INT);

            // Validaciones
            if (empty($numero_documento) || empty($clave) || !in_array($nivel_permiso, [1, 2])) {
                return ['success' => false, 'message' => 'Todos los campos son obligatorios y válidos'];
            }

            // Crear usuario usando el número de documento
            if ($this->user->createModeratorByDocument($numero_documento, $clave, $nivel_permiso)) {
                // Log de actividad
                if (function_exists('logActivity')) {
                    $rol_nombre = ($nivel_permiso == 1) ? 'Administrador' : 'Moderador';
                    logActivity($_SESSION['user_id'], 'CREAR_USUARIO', 
                               "Usuario creado: $numero_documento - Rol: $rol_nombre");
                }

                return [
                    'success' => true,
                    'message' => 'Usuario creado exitosamente'
                ];
            } else {
                return ['success' => false, 'message' => 'Error al crear el usuario'];
            }

        } catch(Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function updateUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Método no permitido'];
        }

        try {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $numero_documento = filter_input(INPUT_POST, 'numero_documento', FILTER_SANITIZE_STRING);
            $nivel_permiso = filter_input(INPUT_POST, 'nivel_permiso', FILTER_VALIDATE_INT);
            $nueva_clave = $_POST['nueva_clave'] ?? '';

            // Validaciones
            if (!$id || empty($numero_documento) || !in_array($nivel_permiso, [1, 2])) {
                return ['success' => false, 'message' => 'Datos inválidos'];
            }

            // Verificar que no se esté editando a sí mismo para quitarse permisos de admin
            if ($_SESSION['user_id'] == $numero_documento && $nivel_permiso != 1) {
                return ['success' => false, 'message' => 'No puedes quitarte los permisos de administrador'];
            }

            // Actualizar usuario
            if ($this->user->updateModerator($id, $numero_documento, $nivel_permiso, $nueva_clave)) {
                // Log de actividad
                if (function_exists('logActivity')) {
                    $rol_nombre = ($nivel_permiso == 1) ? 'Administrador' : 'Moderador';
                    $accion = "Usuario actualizado: $numero_documento - Nuevo rol: $rol_nombre";
                    if (!empty($nueva_clave)) {
                        $accion .= " - Contraseña actualizada";
                    }
                    logActivity($_SESSION['user_id'], 'ACTUALIZAR_USUARIO', $accion);
                }

                return [
                    'success' => true,
                    'message' => 'Usuario actualizado exitosamente'
                ];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar el usuario'];
            }

        } catch(Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function toggleUserStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Método no permitido'];
        }

        try {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            
            if (!$id) {
                return ['success' => false, 'message' => 'ID inválido'];
            }

            // Verificar que no se esté desactivando a sí mismo
            // Para esto necesitamos obtener el número de documento del usuario
            $users = $this->user->getAllModerators();
            $targetUser = null;
            foreach ($users as $user) {
                if ($user['id'] == $id) {
                    $targetUser = $user;
                    break;
                }
            }

            if (!$targetUser) {
                return ['success' => false, 'message' => 'Usuario no encontrado'];
            }

            if ($_SESSION['user_id'] == $targetUser['numero_documento']) {
                return ['success' => false, 'message' => 'No puedes desactivarte a ti mismo'];
            }

            // Cambiar el estado
            $nuevoEstado = $this->user->toggleModeratorStatus($id);
            
            if ($nuevoEstado !== false) {
                // Log de actividad
                if (function_exists('logActivity')) {
                    $accion = $nuevoEstado == 1 ? 'activado' : 'desactivado';
                    logActivity($_SESSION['user_id'], 'CAMBIAR_ESTADO_USUARIO', 
                               "Usuario {$targetUser['numero_documento']} $accion");
                }

                return [
                    'success' => true,
                    'message' => 'Estado del usuario actualizado exitosamente',
                    'new_status' => $nuevoEstado
                ];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar el estado'];
            }

        } catch(Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}