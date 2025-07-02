<?php
// test_connection.php - Archivo para verificar conexión
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sorteo", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión a base de datos exitosa\n";
    
    // Verificar que las tablas existen
    $tables = ['empleados', 'usuario_moderador', 'apertura_sorteo'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabla '$table' existe\n";
        } else {
            echo "❌ Tabla '$table' NO existe\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
}
?>