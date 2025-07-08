<?php
// class User {
//     private $conn;
//     private $table_empleados = "empleados";
//     private $table_moderadores = "usuario_moderador";
//     private $table_concursantes = "usuario_concurso";

//     public function __construct($db) {
//         $this->conn = $db;
//     }

//     /**
//      * Autenticar usuario del sistema
//      */
//     public function authenticate($numero_documento, $password) {
//         // Primero verificar si es moderador
//         $query = "SELECT um.id, um.id_empleado, um.clave, um.nivel_permiso, 
//                          e.numero_documento, e.nombre_completo, e.cargo
//                   FROM " . $this->table_moderadores . " um
//                   INNER JOIN " . $this->table_empleados . " e ON um.id_empleado = e.id
//                   WHERE e.numero_documento = :numero_documento AND um.estado = 1";

//         $stmt = $this->conn->prepare($query);
//         $stmt->bindParam(':numero_documento', $numero_documento);
//         $stmt->execute();

//         if ($stmt->rowCount() > 0) {
//             $row = $stmt->fetch(PDO::FETCH_ASSOC);
//             if (password_verify($password, $row['clave'])) {
//                 return [
//                     'success' => true,
//                     'user_type' => 'moderador',
//                     'user_data' => $row
//                 ];
//             }
//         }

//         // Si no es moderador, verificar si es concursante activo
//         $query = "SELECT uc.id, es.id as id_empleado_sort, es.id_empleado, uc.clave, 
//                          e.numero_documento, e.nombre_completo, e.cargo,
//                          es.cantidad_elecciones, aps.descripcion as sorteo_nombre
//                   FROM " . $this->table_concursantes . " uc
//                   INNER JOIN empleados_en_sorteo es ON uc.id_empleado_sort = es.id
//                   INNER JOIN " . $this->table_empleados . " e ON es.id_empleado = e.id
//                   INNER JOIN apertura_sorteo aps ON es.id_sorteo = aps.id
//                   WHERE e.numero_documento = :numero_documento 
//                     AND uc.estado = 1 AND es.estado = 1 AND aps.estado = 1";

//         $stmt = $this->conn->prepare($query);
//         $stmt->bindParam(':numero_documento', $numero_documento);
//         $stmt->execute();

//         if ($stmt->rowCount() > 0) {
//             $row = $stmt->fetch(PDO::FETCH_ASSOC);
//             if (password_verify($password, $row['clave'])) {
//                 return [
//                     'success' => true,
//                     'user_type' => 'concursante',
//                     'user_data' => $row
//                 ];
//             }
//         }

//         return ['success' => false, 'message' => 'Credenciales inválidas o usuario inactivo'];
//     }

//     /**
//      * Crear usuario concursante
//      */
//     public function createConcursante($id_empleado_sort, $password) {
//         $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
//         $query = "INSERT INTO " . $this->table_concursantes . " (id_empleado_sort, clave) 
//                   VALUES (:id_empleado_sort, :clave)";
        
//         $stmt = $this->conn->prepare($query);
//         $stmt->bindParam(':id_empleado_sort', $id_empleado_sort);
//         $stmt->bindParam(':clave', $hashed_password);
        
//         return $stmt->execute();
//     }

//     /**
//      * Crear usuario moderador
//      */
//     public function createModerador($id_empleado, $password, $nivel_permiso = 1) {
//         $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
//         $query = "INSERT INTO " . $this->table_moderadores . " 
//                   (id_empleado, clave, nivel_permiso) 
//                   VALUES (:id_empleado, :clave, :nivel_permiso)";
        
//         $stmt = $this->conn->prepare($query);
//         $stmt->bindParam(':id_empleado', $id_empleado);
//         $stmt->bindParam(':clave', $hashed_password);
//         $stmt->bindParam(':nivel_permiso', $nivel_permiso);
        
//         return $stmt->execute();
//     }

//     /**
//      * Obtener información del empleado por documento
//      */
//     public function getEmpleadoByDocumento($numero_documento) {
//         $query = "SELECT * FROM " . $this->table_empleados . " 
//                   WHERE numero_documento = :numero_documento AND estado_empleado = 1";
        
//         $stmt = $this->conn->prepare($query);
//         $stmt->bindParam(':numero_documento', $numero_documento);
//         $stmt->execute();
        
//         return $stmt->fetch(PDO::FETCH_ASSOC);
//     }

//     /**
//      * Verificar si empleado ya tiene usuario
//      */
//     public function hasUser($id_empleado) {
//         // Verificar moderador
//         $query = "SELECT id FROM " . $this->table_moderadores . " 
//                   WHERE id_empleado = :id_empleado";
//         $stmt = $this->conn->prepare($query);
//         $stmt->bindParam(':id_empleado', $id_empleado);
//         $stmt->execute();
        
//         if ($stmt->rowCount() > 0) {
//             return ['has_user' => true, 'type' => 'moderador'];
//         }

//         // Verificar concursante activo
//         $query = "SELECT uc.id FROM " . $this->table_concursantes . " uc
//                   INNER JOIN empleados_en_sorteo es ON uc.id_empleado_sort = es.id
//                   WHERE es.id_empleado = :id_empleado AND es.estado = 1";
//         $stmt = $this->conn->prepare($query);
//         $stmt->bindParam(':id_empleado', $id_empleado);
//         $stmt->execute();
        
//         if ($stmt->rowCount() > 0) {
//             return ['has_user' => true, 'type' => 'concursante'];
//         }

//         return ['has_user' => false];
//     }
// }


// models/User.php

// models/User.php - Usando tu estructura real
class User {
    private $conn;
    private $table_empleados = "empleados";
    private $table_moderadores = "usuario_moderador";
    private $table_concursantes = "usuario_concurso";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Autenticar usuario del sistema
     */
    public function authenticate($numero_documento, $password) {
        try {
            // Primero verificar si es moderador (USANDO TU ESTRUCTURA ORIGINAL)
            $query = "SELECT um.id, um.id_empleado, um.clave, um.nivel_permiso, 
                             e.numero_documento, e.nombre_completo, e.cargo
                      FROM " . $this->table_moderadores . " um
                      INNER JOIN " . $this->table_empleados . " e ON um.id_empleado = e.id
                      WHERE e.numero_documento = :numero_documento AND um.estado = 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':numero_documento', $numero_documento);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verificar contraseña con hash
                if (password_verify($password, $row['clave'])) {
                    return [
                        'success' => true,
                        'user_type' => 'moderador',
                        'user_data' => [
                            'id_empleado' => $row['numero_documento'], // Para compatibilidad con sesiones
                            'nombre_completo' => $row['nombre_completo'],
                            'numero_documento' => $row['numero_documento'],
                            'nivel_permiso' => $row['nivel_permiso'], // AGREGADO PARA EL SISTEMA DE ROLES
                            'rol_nombre' => $row['nivel_permiso'] == 1 ? 'Administrador' : 'Moderador',
                            'cargo' => $row['cargo']
                        ]
                    ];
                }
            }

            // Si no es moderador, verificar si es concursante activo (TU ESTRUCTURA ORIGINAL)
            $query = "SELECT uc.id, es.id as id_empleado_sort, es.id_empleado, uc.clave, 
                             e.numero_documento, e.nombre_completo, e.cargo,
                             es.cantidad_elecciones, aps.descripcion as sorteo_nombre
                      FROM " . $this->table_concursantes . " uc
                      INNER JOIN empleados_en_sorteo es ON uc.id_empleado_sort = es.id
                      INNER JOIN " . $this->table_empleados . " e ON es.id_empleado = e.id
                      INNER JOIN apertura_sorteo aps ON es.id_sorteo = aps.id
                      WHERE e.numero_documento = :numero_documento 
                        AND uc.estado = 1 AND es.estado = 1 AND aps.estado = 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':numero_documento', $numero_documento);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verificar contraseña con hash
                if (password_verify($password, $row['clave'])) {
                    return [
                        'success' => true,
                        'user_type' => 'concursante',
                        'user_data' => [
                            'id_empleado_sort' => $row['id_empleado_sort'],
                            'numero_documento' => $row['numero_documento'],
                            'nombre_completo' => $row['nombre_completo'],
                            'cantidad_elecciones' => $row['cantidad_elecciones'],
                            'sorteo_descripcion' => $row['sorteo_nombre'],
                            'cargo' => $row['cargo']
                        ]
                    ];
                }
            }

            return [
                'success' => false, 
                'message' => 'Credenciales inválidas o usuario inactivo'
            ];

        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Crear usuario concursante
     */
    public function createConcursante($id_empleado_sort, $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO " . $this->table_concursantes . " (id_empleado_sort, clave) 
                  VALUES (:id_empleado_sort, :clave)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_empleado_sort', $id_empleado_sort);
        $stmt->bindParam(':clave', $hashed_password);
        
        return $stmt->execute();
    }

    /**
     * Crear usuario moderador
     */
    public function createModerador($id_empleado, $password, $nivel_permiso = 1) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO " . $this->table_moderadores . " 
                  (id_empleado, clave, nivel_permiso) 
                  VALUES (:id_empleado, :clave, :nivel_permiso)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_empleado', $id_empleado);
        $stmt->bindParam(':clave', $hashed_password);
        $stmt->bindParam(':nivel_permiso', $nivel_permiso);
        
        return $stmt->execute();
    }

    /**
     * Obtener información del empleado por documento
     */
    public function getEmpleadoByDocumento($numero_documento) {
        $query = "SELECT * FROM " . $this->table_empleados . " 
                  WHERE numero_documento = :numero_documento AND estado_empleado = 1"; 
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':numero_documento', $numero_documento);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar si empleado ya tiene usuario
     */
    public function hasUser($id_empleado) {
        // Verificar moderador
        $query = "SELECT id FROM " . $this->table_moderadores . " 
                  WHERE id_empleado = :id_empleado";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_empleado', $id_empleado);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return ['has_user' => true, 'type' => 'moderador'];
        }

        // Verificar concursante activo
        $query = "SELECT uc.id FROM " . $this->table_concursantes . " uc
                  INNER JOIN empleados_en_sorteo es ON uc.id_empleado_sort = es.id
                  WHERE es.id_empleado = :id_empleado AND es.estado = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_empleado', $id_empleado);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return ['has_user' => true, 'type' => 'concursante'];
        }

        return ['has_user' => false];
    }

    /**
     * NUEVOS MÉTODOS PARA EL SISTEMA DE ROLES
     */

    /**
     * Obtener todos los usuarios moderadores para gestión
     */
    public function getAllModerators() {
        try {
            $query = "SELECT um.id, um.id_empleado, um.nivel_permiso, um.estado,
                             e.numero_documento, e.nombre_completo, e.cargo,
                             CASE 
                                WHEN um.nivel_permiso = 1 THEN 'Administrador'
                                WHEN um.nivel_permiso = 2 THEN 'Moderador'
                                ELSE 'Sin definir'
                             END as rol_nombre,
                             CASE 
                                WHEN um.estado = 1 THEN 'Activo'
                                ELSE 'Inactivo'
                             END as estado_texto
                      FROM " . $this->table_moderadores . " um
                      INNER JOIN " . $this->table_empleados . " e ON um.id_empleado = e.id
                      ORDER BY um.nivel_permiso ASC, um.id DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            throw new Exception('Error al obtener usuarios: ' . $e->getMessage());
        }
    }

    /**
     * Crear usuario moderador por número de documento
     */
    public function createModeratorByDocument($numero_documento, $password, $nivel_permiso = 2) {
        try {
            // Primero obtener el ID del empleado
            $empleado = $this->getEmpleadoByDocumento($numero_documento);
            if (!$empleado) {
                throw new Exception('Empleado no encontrado');
            }

            // Verificar que no exista ya un usuario para este empleado
            $existing = $this->hasUser($empleado['id']);
            if ($existing['has_user']) {
                throw new Exception('Ya existe un usuario para este empleado');
            }

            // Crear el usuario
            return $this->createModerador($empleado['id'], $password, $nivel_permiso);

        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * Actualizar usuario moderador
     */
    public function updateModerator($id, $numero_documento, $nivel_permiso, $nueva_password = null) {
        try {
            // Obtener el ID del empleado por documento
            $empleado = $this->getEmpleadoByDocumento($numero_documento);
            if (!$empleado) {
                throw new Exception('Empleado no encontrado');
            }

            // Preparar la consulta base
            if ($nueva_password) {
                $hashed_password = password_hash($nueva_password, PASSWORD_DEFAULT);
                $query = "UPDATE " . $this->table_moderadores . " 
                          SET id_empleado = :id_empleado, nivel_permiso = :nivel_permiso, clave = :clave
                          WHERE id = :id";
            } else {
                $query = "UPDATE " . $this->table_moderadores . " 
                          SET id_empleado = :id_empleado, nivel_permiso = :nivel_permiso 
                          WHERE id = :id";
            }

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':id_empleado', $empleado['id']);
            $stmt->bindParam(':nivel_permiso', $nivel_permiso);
            
            if ($nueva_password) {
                $stmt->bindParam(':clave', $hashed_password);
            }

            return $stmt->execute();

        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * Cambiar estado de usuario moderador
     */
    public function toggleModeratorStatus($id) {
        try {
            // Obtener estado actual
            $query = "SELECT estado FROM " . $this->table_moderadores . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                throw new Exception('Usuario no encontrado');
            }

            // Cambiar estado
            $nuevo_estado = $user['estado'] == 1 ? 0 : 1;
            
            $query = "UPDATE " . $this->table_moderadores . " SET estado = :estado WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':estado', $nuevo_estado);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute() ? $nuevo_estado : false;

        } catch(Exception $e) {
            throw $e;
        }
    }
}