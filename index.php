<?php
// index.php
require_once 'config/config.php';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Sorteos</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-hospital-symbol"></i>
                </div>
                <h1>Sistema de Sorteos</h1>
                <p>Instituto de Salud</p>
            </div>

            <form id="loginForm" class="login-form" method="POST" action="process_login.php">
                <div class="form-group">
                    <label for="numero_documento">
                        <i class="fas fa-id-card"></i>
                        Número de Documento
                    </label>
                    <input type="text" id="numero_documento" name="numero_documento" 
                           placeholder="Ingrese su número de documento" required
                           value="<?php echo htmlspecialchars($_GET['user'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Contraseña
                    </label>
                    <input type="password" id="password" name="password" 
                           placeholder="Ingrese su contraseña" required>
                </div>

                <button type="submit" class="btn-primary btn-full">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar Sesión
                </button>
            </form>

            <div id="alertContainer" class="alert-container"></div>

            <div class="login-footer">
                <p><i class="fas fa-info-circle"></i> 
                   Solo empleados inscritos en sorteos activos pueden acceder</p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Función de emergencia si el archivo JS no carga
        function fallbackLogin() {
            const form = document.getElementById('loginForm');
            if (form) {
                form.submit();
            }
        }
        
        // Verificar si hay errores en la URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('error')) {
            alert('Error: ' + decodeURIComponent(urlParams.get('error')));
        }
    </script>
    
    <script src="assets/js/login.js" onerror="console.log('Error cargando login.js')"></script>
    
    <noscript>
        <style>
            .login-card::after {
                content: "JavaScript está deshabilitado. El sistema requiere JavaScript para funcionar correctamente.";
                display: block;
                background: #f8d7da;
                color: #721c24;
                padding: 10px;
                margin: 10px;
                border-radius: 5px;
                text-align: center;
            }
        </style>
    </noscript>
</body>
</html>