<?php
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
        // Primero verificar si es moderador
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
            if (password_verify($password, $row['clave'])) {
                return [
                    'success' => true,
                    'user_type' => 'moderador',
                    'user_data' => $row
                ];
            }
        }

        // Si no es moderador, verificar si es concursante activo
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
            if (password_verify($password, $row['clave'])) {
                return [
                    'success' => true,
                    'user_type' => 'concursante',
                    'user_data' => $row
                ];
            }
        }

        return ['success' => false, 'message' => 'Credenciales inválidas o usuario inactivo'];
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
                  WHERE numero_documento = :numero_documento AND estado_emplado = 1";
        
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
}