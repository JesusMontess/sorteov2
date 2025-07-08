<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Ver estado real del usuario 7
    $query = "SELECT id, estado, 
              CASE estado WHEN 1 THEN 'Activo' WHEN 0 THEN 'Inactivo' END as estado_texto
              FROM usuario_moderador WHERE id = 7";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Estado Real del Usuario 7 en la Base de Datos:</h2>";
    if ($usuario) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Estado (número)</th><th>Estado (texto)</th></tr>";
        echo "<tr><td>" . $usuario['id'] . "</td><td>" . $usuario['estado'] . "</td><td>" . $usuario['estado_texto'] . "</td></tr>";
        echo "</table>";
        
        echo "<h3>Diagnóstico:</h3>";
        if ($usuario['estado'] == 1) {
            echo "<p style='color: green;'>✅ El usuario está ACTIVO (estado = 1)</p>";
            echo "<p>→ Al hacer clic en 'Inactivar' debería cambiar a estado = 0</p>";
        } else {
            echo "<p style='color: red;'>❌ El usuario está INACTIVO (estado = 0)</p>";
            echo "<p>→ Al hacer clic en 'Activar' debería cambiar a estado = 1</p>";
        }
        
        // Test de actualización
        echo "<h3>Probando Actualización Directa:</h3>";
        
        // Intentar cambiar estado
        $nuevoEstado = $usuario['estado'] == 1 ? 0 : 1;
        $accion = $nuevoEstado == 1 ? 'activar' : 'inactivar';
        
        $updateQuery = "UPDATE usuario_moderador SET estado = :estado WHERE id = 7";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':estado', $nuevoEstado, PDO::PARAM_INT);
        
        if ($updateStmt->execute()) {
            $rowsAffected = $updateStmt->rowCount();
            echo "<p style='color: blue;'>✅ Query ejecutado para $accion. Filas afectadas: $rowsAffected</p>";
            
            // Verificar cambio
            $stmt->execute();
            $usuarioDespues = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>Estado después del cambio: " . $usuarioDespues['estado'] . " (" . 
                 ($usuarioDespues['estado'] == 1 ? 'Activo' : 'Inactivo') . ")</p>";
        } else {
            echo "<p style='color: red;'>❌ Error ejecutando query de actualización</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Usuario 7 no encontrado</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
