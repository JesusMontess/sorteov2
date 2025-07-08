<?php
echo "<h2>🧪 Test de API - Cambiar Estado Usuario</h2>";

try {
    require_once 'config/database.php';
    
    echo "<h3>✅ Configuración cargada correctamente</h3>";
    
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<h3>✅ Conexión a BD exitosa</h3>";
    
    // Ver usuarios actuales
    $query = "SELECT id, estado FROM usuario_moderador LIMIT 5";
    $stmt = $conn->query($query);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Usuarios actuales:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Estado</th></tr>";
    foreach ($usuarios as $usuario) {
        echo "<tr><td>{$usuario['id']}</td><td>{$usuario['estado']}</td></tr>";
    }
    echo "</table>";
    
    // Test de actualización
    if (isset($_GET['test_id']) && isset($_GET['test_estado'])) {
        $test_id = (int) $_GET['test_id'];
        $test_estado = (int) $_GET['test_estado'];
        
        echo "<h3>🔧 Probando actualización...</h3>";
        echo "<p>ID: $test_id, Nuevo estado: $test_estado</p>";
        
        $updateQuery = "UPDATE usuario_moderador SET estado = :estado WHERE id = :id";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':estado', $test_estado, PDO::PARAM_INT);
        $updateStmt->bindParam(':id', $test_id, PDO::PARAM_INT);
        
        if ($updateStmt->execute()) {
            $filasAfectadas = $updateStmt->rowCount();
            echo "<p style='color: green;'>✅ Actualización exitosa. Filas afectadas: $filasAfectadas</p>";
        } else {
            echo "<p style='color: red;'>❌ Error en actualización</p>";
        }
    }
    
    echo "<h3>🎯 URLs de Prueba:</h3>";
    echo "<a href='?test_id=7&test_estado=0'>Inactivar Usuario 7</a><br>";
    echo "<a href='?test_id=7&test_estado=1'>Activar Usuario 7</a><br>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>