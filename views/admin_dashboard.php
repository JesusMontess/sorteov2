<?php
// views/admin_dashboard.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - Sistema de Sorteos</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-hospital-symbol"></i>
                    <span>Sistema Sorteos</span>
                </div>
            </div>
            
            <ul class="sidebar-nav">
                <li class="nav-item active">
                    <a href="#dashboard" class="nav-link" data-section="dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#sorteos" class="nav-link" data-section="sorteos">
                        <i class="fas fa-trophy"></i>
                        <span>Gestión de Sorteos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#empleados" class="nav-link" data-section="empleados">
                        <i class="fas fa-users"></i>
                        <span>Empleados</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#reportes" class="nav-link" data-section="reportes">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reportes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#usuarios" class="nav-link" data-section="usuarios">
                        <i class="fas fa-user-cog"></i>
                        <span>Usuarios</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#logs" class="nav-link" data-section="logs">
                        <i class="fas fa-history"></i>
                        <span>Logs del Sistema</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </div>
                <button class="btn-logout" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i>
                    Cerrar Sesión
                </button>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <button class="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 id="pageTitle">Dashboard</h1>
                <div class="header-actions">
                    <span class="current-time" id="currentTime"></span>
                </div>
            </header>

            <div class="content-wrapper">
                <!-- Dashboard Section -->
                <section id="dashboard-section" class="content-section active">
                    <div class="stats-grid">
                        <div class="stat-card primary">
                            <div class="stat-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="stat-info">
                                <h3 id="totalSorteos">0</h3>
                                <p>Sorteos Activos</p>
                            </div>
                        </div>
                        
                        <div class="stat-card success">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3 id="totalParticipantes">0</h3>
                                <p>Participantes</p>
                            </div>
                        </div>
                        
                        <div class="stat-card warning">
                            <div class="stat-icon">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3 id="numerosElegidos">0</h3>
                                <p>Números Elegidos</p>
                            </div>
                        </div>
                        
                        <div class="stat-card info">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <h3 id="actividadReciente">0</h3>
                                <p>Actividad Hoy</p>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-grid">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h3>Sorteos Recientes</h3>
                                <button class="btn-primary btn-sm" onclick="showSection('sorteos')">
                                    Ver Todos
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="sorteos-recientes" class="table-responsive">
                                    <!-- Datos cargados por AJAX -->
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-card">
                            <div class="card-header">
                                <h3>Actividad Reciente</h3>
                                <button class="btn-secondary btn-sm" onclick="showSection('logs')">
                                    Ver Logs
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="actividad-reciente">
                                    <!-- Datos cargados por AJAX -->
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Sorteos Section -->
                <section id="sorteos-section" class="content-section">
                    <div class="section-header">
                        <h2>Gestión de Sorteos</h2>
                        <button class="btn-primary" onclick="openModal('createSorteoModal')">
                            <i class="fas fa-plus"></i>
                            Nuevo Sorteo
                        </button>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="sorteosTable" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Descripción</th>
                                            <th>Fecha Inicio</th>
                                            <th>Fecha Cierre</th>
                                            <th>Participantes</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Datos cargados por AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Empleados Section -->
                <section id="empleados-section" class="content-section">
                    <div class="section-header">
                        <h2>Gestión de Empleados</h2>
                        <button class="btn-primary" onclick="openModal('inscribirEmpleadoModal')">
                            <i class="fas fa-user-plus"></i>
                            Inscribir Empleado
                        </button>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="empleadosTable" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Documento</th>
                                            <th>Nombre</th>
                                            <th>Cargo</th>
                                            <th>Sorteo</th>
                                            <th>Elecciones</th>
                                            <th>Usadas</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Datos cargados por AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Reportes Section -->
                <section id="reportes-section" class="content-section">
                    <div class="section-header">
                        <h2>Reportes y Estadísticas</h2>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <p class="text-center text-muted">Módulo de reportes en desarrollo</p>
                        </div>
                    </div>
                </section>

                <!-- Usuarios Section -->
                <section id="usuarios-section" class="content-section">
                    <div class="section-header">
                        <h2>Gestión de Usuarios</h2>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <p class="text-center text-muted">Módulo de usuarios en desarrollo</p>
                        </div>
                    </div>
                </section>

                <!-- Logs Section -->
                <section id="logs-section" class="content-section">
                    <div class="section-header">
                        <h2>Logs del Sistema</h2>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <p class="text-center text-muted">Módulo de logs en desarrollo</p>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Modales -->
    <!-- Modal Crear Sorteo -->
    <div id="createSorteoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Crear Nuevo Sorteo</h3>
                <button class="modal-close" onclick="closeModal('createSorteoModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="createSorteoForm" class="modal-body">
                <div class="form-group">
                    <label for="descripcion">Descripción del Sorteo</label>
                    <input type="text" id="descripcion" name="descripcion" required
                           placeholder="Ej: Sorteo de Julio 2025">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha de Inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_cierre">Fecha de Cierre</label>
                        <input type="date" id="fecha_cierre" name="fecha_cierre" required>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('createSorteoModal')">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i>
                        Crear Sorteo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Inscribir Empleado -->
    <div id="inscribirEmpleadoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Inscribir Empleado en Sorteo</h3>
                <button class="modal-close" onclick="closeModal('inscribirEmpleadoModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="inscribirEmpleadoForm" class="modal-body">
                <div class="form-group">
                    <label for="sorteo_select">Sorteo</label>
                    <select id="sorteo_select" name="id_sorteo" required>
                        <option value="">Seleccione un sorteo</option>
                        <!-- Opciones cargadas por AJAX -->
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="numero_documento_inscripcion">Número de Documento</label>
                    <input type="text" id="numero_documento_inscripcion" name="numero_documento" required
                           placeholder="Ingrese número de documento del empleado">
                    <small class="form-text">El empleado usará este número como contraseña inicial</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="cantidad_elecciones">Cantidad de Elecciones</label>
                        <input type="number" id="cantidad_elecciones" name="cantidad_elecciones" 
                               min="1" max="10" value="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_final">Fecha Final</label>
                        <input type="date" id="fecha_final" name="fecha_final" required>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('inscribirEmpleadoModal')">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-user-plus"></i>
                        Inscribir Empleado
                    </button>
                </div>
            </form>
        </div>
    </div>
<!-- Modal Editar Sorteo -->
<div id="editSorteoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editar Sorteo</h3>
            <button class="modal-close" onclick="closeModal('editSorteoModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editSorteoForm" class="modal-body">
            <input type="hidden" id="edit_sorteo_id" name="sorteo_id">
            
            <div class="form-group">
                <label for="edit_descripcion">Descripción del Sorteo *</label>
                <input type="text" id="edit_descripcion" name="descripcion" required
                       placeholder="Ej: Sorteo de Julio 2025"
                       minlength="5" maxlength="500">
                <small class="form-text">Entre 5 y 500 caracteres</small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_fecha_inicio">Fecha de Inicio *</label>
                    <input type="date" id="edit_fecha_inicio" name="fecha_inicio" required>
                    <small class="form-text">Fecha mínima: hoy</small>
                </div>
                
                <div class="form-group">
                    <label for="edit_fecha_cierre">Fecha de Cierre *</label>
                    <input type="date" id="edit_fecha_cierre" name="fecha_cierre" required>
                    <small class="form-text">Debe ser posterior a la fecha de inicio</small>
                </div>
            </div>
            
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Atención:</strong> Si hay empleados ya inscritos, algunos cambios pueden afectar su participación.
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('editSorteoModal')">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    Actualizar Sorteo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ver Sorteo -->
<div id="viewSorteoModal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Detalles del Sorteo</h3>
            <button class="modal-close" onclick="closeModal('viewSorteoModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="sorteo-details">
                <div class="detail-section">
                    <h4><i class="fas fa-info-circle"></i> Información General</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Descripción:</label>
                            <span id="view_sorteo_descripcion">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Fecha de Inicio:</label>
                            <span id="view_sorteo_fecha_inicio">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Fecha de Cierre:</label>
                            <span id="view_sorteo_fecha_cierre">-</span>
                        </div>
                        <div class="detail-item">
                            <label>Estado:</label>
                            <span id="view_sorteo_estado">-</span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h4><i class="fas fa-chart-bar"></i> Estadísticas</h4>
                    <div class="stats-mini-grid">
                        <div class="stat-mini-card">
                            <div class="stat-mini-number" id="view_total_participantes">0</div>
                            <div class="stat-mini-label">Participantes</div>
                        </div>
                        <div class="stat-mini-card">
                            <div class="stat-mini-number" id="view_numeros_elegidos">0</div>
                            <div class="stat-mini-label">Números Elegidos</div>
                        </div>
                        <div class="stat-mini-card">
                            <div class="stat-mini-number" id="view_numeros_disponibles">0</div>
                            <div class="stat-mini-label">Disponibles</div>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h4><i class="fas fa-users"></i> Participantes</h4>
                    <div id="view_participantes_list">
                        <p class="text-muted">Cargando participantes...</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal('viewSorteoModal')">
                <i class="fas fa-times"></i>
                Cerrar
            </button>
        </div>
    </div>
</div>
    <!-- Alert Container -->
    <div id="alertContainer" class="alert-container"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/admin.js"></script>
</body>
</html>
<style>
/* Estilos adicionales para los modales */
.modal-content.large {
    max-width: 800px;
}

.sorteo-details {
    space-y: 2rem;
}

.detail-section {
    margin-bottom: 2rem;
}

.detail-section h4 {
    margin-bottom: 1rem;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-item label {
    font-weight: 600;
    color: #6b7280;
    font-size: 0.875rem;
}

.detail-item span {
    color: #111827;
    font-weight: 500;
}

.stats-mini-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

.stat-mini-card {
    background: #f9fafb;
    padding: 1rem;
    border-radius: 0.5rem;
    text-align: center;
    border: 1px solid #e5e7eb;
}

.stat-mini-number {
    font-size: 2rem;
    font-weight: 700;
    color: #2563eb;
    margin-bottom: 0.25rem;
}

.stat-mini-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

.alert {
    padding: 1rem;
    border-radius: 0.375rem;
    border: 1px solid;
    margin: 1rem 0;
}

.alert-warning {
    background-color: #fffbeb;
    border-color: #fed7aa;
    color: #92400e;
}

.alert i {
    margin-right: 0.5rem;
}
</style>