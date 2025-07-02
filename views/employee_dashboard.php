<?php
// views/employee_dashboard.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - Sistema de Sorteos</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="employee-layout">
        <!-- Header -->
        <header class="employee-header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-hospital-symbol"></i>
                    <span>Sistema de Sorteos</span>
                </div>
                
                <div class="user-menu">
                    <div class="user-info">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    </div>
                    <button class="btn-logout" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i>
                        Salir
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="employee-main">
            <div class="container">
                <!-- Bienvenida -->
                <div class="welcome-section">
                    <h1>¡Bienvenido al Sorteo!</h1>
                    <p>Elige tus números de la suerte entre el 100 y 800</p>
                </div>

                <!-- Stats Cards -->
                <div class="employee-stats">
                    <div class="stat-card primary">
                        <div class="stat-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="eleccionesDisponibles">0</h3>
                            <p>Elecciones Disponibles</p>
                        </div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="numerosElegidos">0</h3>
                            <p>Números Elegidos</p>
                        </div>
                    </div>
                    
                    <div class="stat-card warning">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="tiempoRestante">--</h3>
                            <p>Tiempo Restante</p>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="tabs-nav">
                    <button class="tab-btn active" data-tab="elegir">
                        <i class="fas fa-plus-circle"></i>
                        Elegir Número
                    </button>
                    <button class="tab-btn" data-tab="mis-numeros">
                        <i class="fas fa-list"></i>
                        Mis Números
                    </button>
                    <button class="tab-btn" data-tab="historial">
                        <i class="fas fa-history"></i>
                        Historial
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Elegir Número Tab -->
                    <div id="elegir-tab" class="tab-panel active">
                        <div class="card">
                            <div class="card-header">
                                <h3>Elegir Número de la Suerte</h3>
                                <p>Selecciona un número entre 100 y 800</p>
                            </div>
                            <div class="card-body">
                                <form id="elegirNumeroForm" class="number-selection-form">
                                    <div class="number-input-section">
                                        <div class="form-group">
                                            <label for="numero_balota">Número de la Suerte</label>
                                            <input type="number" id="numero_balota" name="numero_balota" 
                                                   min="100" max="800" required
                                                   placeholder="Ingrese un número entre 100 y 800">
                                        </div>
                                        
                                        <button type="submit" class="btn-primary btn-large" id="elegirBtn">
                                            <i class="fas fa-star"></i>
                                            Elegir Este Número
                                        </button>
                                    </div>

                                    <div class="number-suggestions">
                                        <h4>Números Sugeridos</h4>
                                        <div id="numerosSugeridos" class="suggested-numbers">
                                            <!-- Números aleatorios generados por JS -->
                                        </div>
                                    </div>
                                </form>

                                <!-- Número Elegido Preview -->
                                <div id="numeroPreview" class="number-preview" style="display: none;">
                                    <div class="preview-card">
                                        <h4>Número Seleccionado</h4>
                                        <div class="big-number" id="numeroSeleccionado">000</div>
                                        <div class="binary-representation">
                                            <small>Representación binaria: <span id="binarioSeleccionado"></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mis Números Tab -->
                    <div id="mis-numeros-tab" class="tab-panel">
                        <div class="card">
                            <div class="card-header">
                                <h3>Mis Números Elegidos</h3>
                                <p>Números que has seleccionado en el sorteo actual</p>
                            </div>
                            <div class="card-body">
                                <div id="misNumerosContainer">
                                    <!-- Contenido cargado por AJAX -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historial Tab -->
                    <div id="historial-tab" class="tab-panel">
                        <div class="card">
                            <div class="card-header">
                                <h3>Historial de Participaciones</h3>
                                <p>Todos tus números elegidos en sorteos anteriores</p>
                            </div>
                            <div class="card-body">
                                <div id="historialContainer">
                                    <!-- Contenido cargado por AJAX -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Números Disponibles Info -->
                <div class="info-section">
                    <div class="info-card">
                        <div class="info-header">
                            <i class="fas fa-info-circle"></i>
                            <h4>Información del Sorteo</h4>
                        </div>
                        <div class="info-content">
                            <div class="info-item">
                                <span class="label">Sorteo Activo:</span>
                                <span class="value" id="sorteoActivo">Cargando...</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Números Disponibles:</span>
                                <span class="value" id="numerosDisponibles">Cargando...</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Total Participantes:</span>
                                <span class="value" id="totalParticipantes">Cargando...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="employee-footer">
            <div class="container">
                <p>&copy; 2025 Instituto de Salud - Sistema de Sorteos</p>
                <p><i class="fas fa-shield-alt"></i> Todos los números son únicos por sorteo</p>
            </div>
        </footer>
    </div>

    <!-- Modales -->
    <!-- Modal de Confirmación -->
    <div id="confirmModal" class="modal">
        <div class="modal-content small">
            <div class="modal-header">
                <h3>Confirmar Elección</h3>
                <button class="modal-close" onclick="closeModal('confirmModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="confirm-content">
                    <div class="confirm-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <p>¿Estás seguro de que quieres elegir el número <strong id="numeroConfirmar">000</strong>?</p>
                    <small>Esta acción no se puede deshacer</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('confirmModal')">
                    Cancelar
                </button>
                <button type="button" class="btn-primary" onclick="confirmarEleccion()">
                    <i class="fas fa-check"></i>
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer" class="alert-container"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="assets/js/employee.js"></script>
</body>
</html>