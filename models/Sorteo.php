<?php
class Sorteo {
    private $conn;
    private $table_sorteos = "apertura_sorteo";
    private $table_empleados_sorteo = "empleados_en_sorteo";
    private $table_balotas = "balota_concursante";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear nuevo sorteo
     */
    public function create($descripcion, $fecha_inicio, $fecha_cierre) {
        $query = "INSERT INTO " . $this->table_sorteos . " 
                  (descripcion, fecha_inicio_sorteo, fecha_cierre_sorteo) 
                  VALUES (:descripcion, :fecha_inicio, :fecha_cierre)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt->bindParam(':fecha_cierre', $fecha_cierre);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Obtener sorteos activos
     */
    public function getActiveSorteos() {
        $query = "SELECT * FROM " . $this->table_sorteos . " 
                  WHERE estado = 1 AND fecha_cierre_sorteo >= CURDATE() 
                  ORDER BY fecha_inicio_sorteo DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener todos los sorteos
     */
    public function getAllSorteos() {
        $query = "SELECT *, 
                    CASE 
                        WHEN estado = 1 AND fecha_cierre_sorteo >= CURDATE() THEN 'Activo'
                        WHEN estado = 1 AND fecha_cierre_sorteo < CURDATE() THEN 'Finalizado'
                        ELSE 'Inactivo'
                    END as estado_texto
                  FROM " . $this->table_sorteos . " 
                  ORDER BY fecha_inicio_sorteo DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Inscribir empleado en sorteo
     */
    public function inscribirEmpleado($id_sorteo, $id_empleado, $cantidad_elecciones, $fecha_final) {
        // Verificar si ya está inscrito
        $query_check = "SELECT id FROM " . $this->table_empleados_sorteo . " 
                        WHERE id_sorteo = :id_sorteo AND id_empleado = :id_empleado";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(':id_sorteo', $id_sorteo);
        $stmt_check->bindParam(':id_empleado', $id_empleado);
        $stmt_check->execute();

        if ($stmt_check->rowCount() > 0) {
            return ['success' => false, 'message' => 'El empleado ya está inscrito en este sorteo'];
        }

        $query = "INSERT INTO " . $this->table_empleados_sorteo . " 
                  (id_sorteo, id_empleado, cantidad_elecciones, fecha_autorizacion, fecha_final) 
                  VALUES (:id_sorteo, :id_empleado, :cantidad_elecciones, CURDATE(), :fecha_final)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_sorteo', $id_sorteo);
        $stmt->bindParam(':id_empleado', $id_empleado);
        $stmt->bindParam(':cantidad_elecciones', $cantidad_elecciones);
        $stmt->bindParam(':fecha_final', $fecha_final);
        
        if ($stmt->execute()) {
            return ['success' => true, 'id' => $this->conn->lastInsertId()];
        }
        return ['success' => false, 'message' => 'Error al inscribir empleado'];
    }

    /**
     * Elegir número de la suerte (con manejo de concurrencia)
     */
    public function elegirNumero($id_empleado_sort, $numero_balota) {
        try {
            $this->conn->beginTransaction();

            // Verificar que el empleado tenga elecciones disponibles
            $query_check = "SELECT es.cantidad_elecciones,
                               (SELECT COUNT(*) FROM balota_concursante bc 
                                WHERE bc.id_empleado_sort = es.id) as elecciones_usadas
                           FROM empleados_en_sorteo es
                           WHERE es.id = :id_empleado_sort AND es.estado = 1";
            
            $stmt_check = $this->conn->prepare($query_check);
            $stmt_check->bindParam(':id_empleado_sort', $id_empleado_sort);
            $stmt_check->execute();
            
            $result = $stmt_check->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                throw new Exception('Empleado no encontrado o inactivo');
            }

            if ($result['elecciones_usadas'] >= $result['cantidad_elecciones']) {
                throw new Exception('No tienes elecciones disponibles');
            }

            // Verificar que el número esté disponible (bloqueo de fila)
            $query_lock = "SELECT numero_balota FROM balotas 
                          WHERE numero_balota = :numero_balota FOR UPDATE";
            $stmt_lock = $this->conn->prepare($query_lock);
            $stmt_lock->bindParam(':numero_balota', $numero_balota);
            $stmt_lock->execute();

            if ($stmt_lock->rowCount() == 0) {
                throw new Exception('Número de balota no válido');
            }

            // Verificar que el número no esté ya elegido en este sorteo
            $query_used = "SELECT bc.id FROM balota_concursante bc
                          INNER JOIN empleados_en_sorteo es ON bc.id_empleado_sort = es.id
                          WHERE bc.numero_balota = :numero_balota 
                            AND es.id_sorteo = (SELECT id_sorteo FROM empleados_en_sorteo WHERE id = :id_empleado_sort)";
            
            $stmt_used = $this->conn->prepare($query_used);
            $stmt_used->bindParam(':numero_balota', $numero_balota);
            $stmt_used->bindParam(':id_empleado_sort', $id_empleado_sort);
            $stmt_used->execute();

            if ($stmt_used->rowCount() > 0) {
                throw new Exception('Este número ya ha sido elegido por otro participante');
            }

            // Insertar la elección
            $query_insert = "INSERT INTO " . $this->table_balotas . " 
                            (id_empleado_sort, numero_balota) 
                            VALUES (:id_empleado_sort, :numero_balota)";
            
            $stmt_insert = $this->conn->prepare($query_insert);
            $stmt_insert->bindParam(':id_empleado_sort', $id_empleado_sort);
            $stmt_insert->bindParam(':numero_balota', $numero_balota);
            
            if (!$stmt_insert->execute()) {
                throw new Exception('Error al registrar la elección');
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Número elegido correctamente'];

        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Obtener números elegidos por un empleado
     */
    public function getEleccionesEmpleado($id_empleado_sort) {
        $query = "SELECT bc.numero_balota, bc.fecha_eleccion, b.equivalencia_binaria
                  FROM " . $this->table_balotas . " bc
                  INNER JOIN balotas b ON bc.numero_balota = b.numero_balota
                  WHERE bc.id_empleado_sort = :id_empleado_sort
                  ORDER BY bc.fecha_eleccion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_empleado_sort', $id_empleado_sort);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener números disponibles para un sorteo
     */
    public function getNumerosDisponibles($id_sorteo) {
        $query = "SELECT b.numero_balota, b.equivalencia_binaria
                  FROM balotas b
                  WHERE b.numero_balota NOT IN (
                      SELECT bc.numero_balota 
                      FROM balota_concursante bc
                      INNER JOIN empleados_en_sorteo es ON bc.id_empleado_sort = es.id
                      WHERE es.id_sorteo = :id_sorteo
                  )
                  AND CAST(b.numero_balota AS UNSIGNED) BETWEEN 100 AND 800
                  ORDER BY CAST(b.numero_balota AS UNSIGNED)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_sorteo', $id_sorteo);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener estadísticas del sorteo
     */
    public function getEstadisticasSorteo($id_sorteo) {
        $query = "SELECT 
                    COUNT(DISTINCT es.id_empleado) as total_participantes,
                    COUNT(bc.id) as numeros_elegidos,
                    SUM(es.cantidad_elecciones) as total_elecciones_disponibles,
                    (701 - COUNT(bc.id)) as numeros_disponibles
                  FROM empleados_en_sorteo es
                  LEFT JOIN balota_concursante bc ON es.id = bc.id_empleado_sort
                  WHERE es.id_sorteo = :id_sorteo AND es.estado = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_sorteo', $id_sorteo);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
