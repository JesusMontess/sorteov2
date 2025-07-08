<?php
class Usuario {
    private $conn;
    private $table_usuario_moderador= "usuario_moderador";
  

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear usuario concursante
     */
    public function createUsuario($id_empleado, $password, $nivel_permiso) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO " . $this->table_usuario_moderador . " (id_empleado, clave, nivel_permiso) 
                  VALUES (:id_empleado, :clave, :nivel_permiso)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_empleado', $id_empleado);
        $stmt->bindParam(':clave', $hashed_password);
        $stmt->bindParam(':nivel_permiso', $nivel_permiso);
        
        return $stmt->execute();
    }

    public function registrarUsuarioModeradores($id_empleado, $clave, $nivel_permiso) {
            try {
                // Verificar si el empleado existe
                $query_empleado = "SELECT id, numero_documento, nombre_completo FROM empleados WHERE id = :id_empleado";
                $stmt_empleado = $this->conn->prepare($query_empleado);
                $stmt_empleado->bindParam(':id_empleado', $id_empleado);
                $stmt_empleado->execute();

                if ($stmt_empleado->rowCount() == 0) {
                    return ['success' => false, 'message' => 'Empleado no encontrado'];
                }

                // Verificar si ya está registrado como usuario
                $query_check = "SELECT id FROM " . $this->table_usuario_moderador . " 
                                WHERE id_empleado = :id_empleado";
                $stmt_check = $this->conn->prepare($query_check);
                $stmt_check->bindParam(':id_empleado', $id_empleado);
                $stmt_check->execute();

                if ($stmt_check->rowCount() > 0) {
                    return ['success' => false, 'message' => 'Este empleado ya está registrado como usuario'];
                }

                // Hash de la contraseña
                $hashed_password = password_hash($clave, PASSWORD_DEFAULT);

                // Insertar usuario
                $query = "INSERT INTO " . $this->table_usuario_moderador . " 
                        (id_empleado, clave, nivel_permiso, estado) 
                        VALUES (:id_empleado, :clave, :nivel_permiso, 1)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id_empleado', $id_empleado);
                $stmt->bindParam(':clave', $hashed_password);
                $stmt->bindParam(':nivel_permiso', $nivel_permiso);
                
                if ($stmt->execute()) {
                    return ['success' => true, 'id' => $this->conn->lastInsertId()];
                }
                
                return ['success' => false, 'message' => 'Error al registrar usuario'];
                
            } catch (Exception $e) {
                error_log("Error en registrarUsuarioModeradores: " . $e->getMessage());
                return ['success' => false, 'message' => 'Error interno del servidor'];
            }
        }

        /**
         * Obtener todos los usuarios con información del empleado
         */
    public function getUsuarios() {
        try {
            // IMPORTANTE: Ajusta el nombre de la tabla empleado según tu BD
            // Si tu tabla se llama 'empleados' (plural), cambia 'empleado' por 'empleados'
            
            $query = "SELECT 
                        um.id,
                        um.id_empleado,
                        um.nivel_permiso,
                        um.estado,  -- ← CAMBIO: Devolver número original
                        e.numero_documento,
                        e.nombre_completo,
                        e.cargo,
                        CASE um.nivel_permiso 
                            WHEN 1 THEN 'Administrador'
                            WHEN 2 THEN 'Moderador'
                            ELSE 'Usuario'
                        END as rol_nombre,
                        CASE um.estado
                            WHEN 1 THEN 'Activo'
                            WHEN 0 THEN 'Inactivo'  
                            ELSE 'Desconocido'
                        END as estado_texto  -- ← CAMBIO: Campo separado para texto
                    FROM " . $this->table_usuario_moderador . " um
                    INNER JOIN empleados e ON um.id_empleado = e.id
                    ORDER BY um.id ASC";
                
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Error preparando consulta: " . implode(", ", $this->conn->errorInfo()));
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Error ejecutando consulta: " . implode(", ", $stmt->errorInfo()));
            }
            
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Log para debug
            error_log("DEBUG getUsuarios: " . count($usuarios) . " usuarios encontrados");
            
            return $usuarios;
            
        } catch (Exception $e) {
            error_log("Error en getUsuarios: " . $e->getMessage());
            throw $e;
        }
    }

        /**
     * Obtener usuario por ID con información del empleado
     */
    public function getUsuarioById($id) {
        try {
            $query = "SELECT 
                        um.id,
                        um.id_empleado,
                        um.nivel_permiso,
                        um.estado,
                        e.numero_documento,
                        e.nombre_completo,
                        e.cargo,
                        CASE um.nivel_permiso 
                            WHEN 1 THEN 'Administrador'
                            WHEN 2 THEN 'Moderador'
                            ELSE 'Usuario'
                        END as rol_nombre
                    FROM " . $this->table_usuario_moderador . " um
                    INNER JOIN empleados e ON um.id_empleado = e.id
                    WHERE um.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario) {
                // Asegurar que estado sea numérico
                $usuario['estado'] = (int) $usuario['estado'];
            }
            
            return $usuario;
            
        } catch (Exception $e) {
            error_log("Error en getUsuarioById: " . $e->getMessage());
            throw $e;
        }
    }

    public function cambiarEstadoSimple($id, $nuevo_estado) {
        try {
            error_log("=== CAMBIAR ESTADO SIMPLE ===");
            error_log("ID: $id, Nuevo estado: $nuevo_estado");
            
            // Query simple y directa
            $query = "UPDATE " . $this->table_usuario_moderador . " 
                    SET estado = :estado 
                    WHERE id = :id";
            
            error_log("Query: $query");
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                $error = implode(", ", $this->conn->errorInfo());
                error_log("Error preparando consulta: $error");
                return ['success' => false, 'message' => 'Error preparando consulta'];
            }
            
            // Bind de parámetros
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $nuevo_estado, PDO::PARAM_INT);
            
            error_log("Ejecutando query con ID=$id, estado=$nuevo_estado");
            
            if ($stmt->execute()) {
                $filas_afectadas = $stmt->rowCount();
                error_log("Filas afectadas: $filas_afectadas");
                
                if ($filas_afectadas > 0) {
                    $mensaje = $nuevo_estado === 1 ? 'Usuario activado correctamente' : 'Usuario inactivado correctamente';
                    return [
                        'success' => true, 
                        'message' => $mensaje,
                        'filas_afectadas' => $filas_afectadas
                    ];
                } else {
                    return ['success' => false, 'message' => 'No se encontró el usuario o el estado ya era el mismo'];
                }
            } else {
                $error = implode(", ", $stmt->errorInfo());
                error_log("Error ejecutando consulta: $error");
                return ['success' => false, 'message' => 'Error ejecutando consulta: ' . $error];
            }
            
        } catch (Exception $e) {
            error_log("Excepción en cambiarEstadoSimple: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
    }


        /**
     * Actualizar usuario
     */
    // public function actualizarUsuario($id, $nivel_permiso = null, $estado = null, $nueva_clave = null) {
    //     try {
    //         // Construir query dinámicamente
    //         $campos = [];
    //         $params = [':id' => $id];
            
    //         if ($nivel_permiso !== null) {
    //             $campos[] = "nivel_permiso = :nivel_permiso";
    //             $params[':nivel_permiso'] = $nivel_permiso;
    //         }
            
    //         if ($estado !== null) {
    //             $campos[] = "estado = :estado";
    //             $params[':estado'] = $estado;
    //         }
            
    //         if (!empty($nueva_clave)) {
    //             $campos[] = "clave = :clave";
    //             $params[':clave'] = password_hash($nueva_clave, PASSWORD_DEFAULT);
    //         }
            
    //         if (empty($campos)) {
    //             return ['success' => false, 'message' => 'No hay campos para actualizar'];
    //         }
            
    //         $query = "UPDATE " . $this->table_usuario_moderador . " 
    //                 SET " . implode(', ', $campos) . "
    //                 WHERE id = :id";
            
    //         $stmt = $this->conn->prepare($query);
            
    //         if ($stmt->execute($params)) {
    //             return ['success' => true, 'message' => 'Usuario actualizado correctamente'];
    //         }
            
    //         return ['success' => false, 'message' => 'Error al actualizar usuario'];
            
    //     } catch (Exception $e) {
    //         error_log("Error en actualizarUsuario: " . $e->getMessage());
    //         return ['success' => false, 'message' => 'Error interno del servidor'];
    //     }
    // }
    public function actualizarUsuarioModel($id, $nivel_permiso = null, $estado = null, $nueva_clave = null) {
    try {
        error_log("=== DEBUG MODELO actualizarUsuario ===");
        error_log("Parámetros recibidos: ID=$id, nivel_permiso=" . var_export($nivel_permiso, true) . 
                 ", estado=" . var_export($estado, true) . ", nueva_clave=" . (empty($nueva_clave) ? 'VACÍA' : 'TIENE VALOR'));
        
        // Construir query dinámicamente
        $campos = [];
        $params = [':id' => $id];
        
        if ($nivel_permiso !== null) {
            $campos[] = "nivel_permiso = :nivel_permiso";
            $params[':nivel_permiso'] = $nivel_permiso;
            error_log("Agregando nivel_permiso al UPDATE: $nivel_permiso");
        }
        
        if ($estado !== null) {
            $campos[] = "estado = :estado";
            $params[':estado'] = $estado;
            error_log("Agregando estado al UPDATE: $estado");
        }
        
        if (!empty($nueva_clave)) {
            $campos[] = "clave = :clave";
            $params[':clave'] = password_hash($nueva_clave, PASSWORD_DEFAULT);
            error_log("Agregando nueva clave al UPDATE (hasheada)");
        }
        
        if (empty($campos)) {
            error_log("ERROR: No hay campos para actualizar");
            return ['success' => false, 'message' => 'No hay campos para actualizar'];
        }
        
        $query = "UPDATE " . $this->table_usuario_moderador . " 
                  SET " . implode(', ', $campos) . "
                  WHERE id = :id";
        
        error_log("Query generado: $query");
        error_log("Parámetros: " . json_encode($params));
        
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            error_log("ERROR: No se pudo preparar la consulta: " . implode(", ", $this->conn->errorInfo()));
            return ['success' => false, 'message' => 'Error preparando consulta'];
        }
        
        $result = $stmt->execute($params);
        
        if (!$result) {
            error_log("ERROR: Error ejecutando consulta: " . implode(", ", $stmt->errorInfo()));
            return ['success' => false, 'message' => 'Error ejecutando actualización'];
        }
        
        $rowsAffected = $stmt->rowCount();
        error_log("Filas afectadas: $rowsAffected");
        
        if ($rowsAffected > 0) {
            error_log("SUCCESS: Usuario actualizado correctamente");
            return ['success' => true, 'message' => 'Usuario actualizado correctamente'];
        } else {
            error_log("WARNING: No se actualizaron filas (posiblemente el usuario no existe o los datos son iguales)");
            return ['success' => true, 'message' => 'Usuario actualizado (sin cambios)'];
        }
        
    } catch (Exception $e) {
        error_log("EXCEPCIÓN en actualizarUsuario: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return ['success' => false, 'message' => 'Error interno del servidor'];
    }
}

    /**
     * Cambiar solo el estado del usuario
     */
    public function cambiarEstadoUsuario($id, $estado) {
        try {
            $query = "UPDATE " . $this->table_usuario_moderador . " 
                    SET estado = :estado 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $mensaje = $estado ? 'Usuario activado correctamente' : 'Usuario inactivado correctamente';
                return ['success' => true, 'message' => $mensaje];
            }
            
            return ['success' => false, 'message' => 'Error al cambiar estado del usuario'];
            
        } catch (Exception $e) {
            error_log("Error en cambiarEstadoUsuario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
}

    

// class Usuario {
//     private $conn;
//     private $table_usuario_moderador = "usuario_moderador";
//     private $table_empleado = "empleados";

//     public function __construct($db) {
//         error_log("DEBUG: Iniciando constructor Usuario");
//         $this->conn = $db;
//         if (!$this->conn) {
//             error_log("ERROR: Conexión de base de datos es null");
//             throw new Exception("Conexión de base de datos no válida");
//         }
//         error_log("DEBUG: Constructor Usuario exitoso");
//     }

//     public function registrarUsuarioModeradores($id_empleado, $clave, $nivel_permiso) {
//         error_log("DEBUG: Iniciando registrarUsuarioModeradores con id_empleado: $id_empleado, nivel_permiso: $nivel_permiso");
        
//         try {
//             // Verificar si el empleado existe
//             error_log("DEBUG: Verificando si empleado existe");
//             $query_empleado = "SELECT id, numero_documento, nombre_completo FROM empleados WHERE id = :id_empleado";
//             $stmt_empleado = $this->conn->prepare($query_empleado);
            
//             if (!$stmt_empleado) {
//                 error_log("ERROR: No se pudo preparar query de empleado: " . implode(", ", $this->conn->errorInfo()));
//                 return ['success' => false, 'message' => 'Error en consulta de empleado'];
//             }
            
//             $stmt_empleado->bindParam(':id_empleado', $id_empleado);
//             $result = $stmt_empleado->execute();
            
//             if (!$result) {
//                 error_log("ERROR: Error ejecutando query empleado: " . implode(", ", $stmt_empleado->errorInfo()));
//                 return ['success' => false, 'message' => 'Error verificando empleado'];
//             }

//             if ($stmt_empleado->rowCount() == 0) {
//                 error_log("ERROR: Empleado no encontrado con ID: $id_empleado");
//                 return ['success' => false, 'message' => 'Empleado no encontrado'];
//             }
            
//             $empleado = $stmt_empleado->fetch(PDO::FETCH_ASSOC);
//             error_log("DEBUG: Empleado encontrado: " . json_encode($empleado));

//             // Verificar si ya está registrado como usuario
//             error_log("DEBUG: Verificando si empleado ya está registrado como usuario");
//             $query_check = "SELECT id FROM " . $this->table_usuario_moderador . " WHERE id_empleado = :id_empleado";
//             $stmt_check = $this->conn->prepare($query_check);
            
//             if (!$stmt_check) {
//                 error_log("ERROR: No se pudo preparar query de verificación: " . implode(", ", $this->conn->errorInfo()));
//                 return ['success' => false, 'message' => 'Error en verificación de usuario'];
//             }
            
//             $stmt_check->bindParam(':id_empleado', $id_empleado);
//             $result = $stmt_check->execute();
            
//             if (!$result) {
//                 error_log("ERROR: Error ejecutando query de verificación: " . implode(", ", $stmt_check->errorInfo()));
//                 return ['success' => false, 'message' => 'Error verificando usuario existente'];
//             }

//             if ($stmt_check->rowCount() > 0) {
//                 error_log("ERROR: Empleado ya registrado como usuario");
//                 return ['success' => false, 'message' => 'Este empleado ya está registrado como usuario'];
//             }

//             // Hash de la contraseña
//             error_log("DEBUG: Hasheando contraseña");
//             $hashed_password = password_hash($clave, PASSWORD_DEFAULT);
            
//             if (!$hashed_password) {
//                 error_log("ERROR: No se pudo hashear la contraseña");
//                 return ['success' => false, 'message' => 'Error procesando contraseña'];
//             }
            
//             error_log("DEBUG: Contraseña hasheada correctamente");

//             // Insertar usuario
//             error_log("DEBUG: Insertando usuario en base de datos");
//             $query = "INSERT INTO " . $this->table_usuario_moderador . " 
//                       (id_empleado, clave, nivel_permiso, estado) 
//                       VALUES (:id_empleado, :clave, :nivel_permiso, 1)";
            
//             $stmt = $this->conn->prepare($query);
            
//             if (!$stmt) {
//                 error_log("ERROR: No se pudo preparar query de inserción: " . implode(", ", $this->conn->errorInfo()));
//                 return ['success' => false, 'message' => 'Error preparando inserción'];
//             }
            
//             $stmt->bindParam(':id_empleado', $id_empleado);
//             $stmt->bindParam(':clave', $hashed_password);
//             $stmt->bindParam(':nivel_permiso', $nivel_permiso);
            
//             error_log("DEBUG: Ejecutando inserción...");
//             $result = $stmt->execute();
            
//             if (!$result) {
//                 error_log("ERROR: Error ejecutando inserción: " . implode(", ", $stmt->errorInfo()));
//                 return ['success' => false, 'message' => 'Error insertando usuario: ' . implode(", ", $stmt->errorInfo())];
//             }
            
//             $lastId = $this->conn->lastInsertId();
//             error_log("DEBUG: Usuario insertado correctamente con ID: $lastId");
            
//             return ['success' => true, 'id' => $lastId];
            
//         } catch (Exception $e) {
//             error_log("EXCEPCIÓN en registrarUsuarioModeradores: " . $e->getMessage());
//             error_log("STACK TRACE: " . $e->getTraceAsString());
//             return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
//         }
//     }
// }