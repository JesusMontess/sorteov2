// assets/js/admin.js - Versión completa corregida

$(document).ready(function() {
    // Variables globales
    let currentSection = 'dashboard';
    let sorteosTable, empleadosTable;

    // Inicialización
    initializeApp();

    function initializeApp() {
        updateCurrentTime();
        setInterval(updateCurrentTime, 1000);
        
        loadDashboardData();
        initializeEventListeners();
        
        // Cargar primera sección
        showSection('dashboard');
    }

    function initializeEventListeners() {
        // Navegación del sidebar
        $('.nav-link').on('click', function(e) {
            e.preventDefault();
            const section = $(this).data('section');
            showSection(section);
        });

        // Toggle mobile menu
        $('.mobile-menu-toggle').on('click', function() {
            $('.sidebar').toggleClass('show');
        });

        // Cerrar mobile menu al hacer click fuera
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.sidebar, .mobile-menu-toggle').length) {
                $('.sidebar').removeClass('show');
            }
        });

        // Formularios
        $('#createSorteoForm').on('submit', handleCreateSorteo);
        $('#inscribirEmpleadoForm').on('submit', handleInscribirEmpleado);
        $('#editSorteoForm').on('submit', handleEditSorteo); // ← AGREGAR ESTA LÍNEA
    

        // Modales
        $('.modal-close, .modal').on('click', function(e) {
            if (e.target === this) {
                closeAllModals();
            }
        });

        // ===== VALIDACIONES DE FECHA =====
        // Establecer fecha mínima en los inputs de fecha
        const today = new Date();
        const todayString = today.toISOString().split('T')[0];
        
        $('#fecha_inicio').attr('min', todayString);
        $('#fecha_cierre').attr('min', todayString);
        
        // Cuando cambia fecha de inicio, actualizar mínimo de fecha de cierre
        $('#fecha_inicio').on('change', function() {
            const fechaInicio = this.value;
            if (fechaInicio) {
                // La fecha de cierre debe ser al menos 1 día después
                const nextDay = new Date(fechaInicio);
                nextDay.setDate(nextDay.getDate() + 1);
                const nextDayString = nextDay.toISOString().split('T')[0];
                $('#fecha_cierre').attr('min', nextDayString);
                
                // Si la fecha de cierre actual es menor que la nueva mínima, actualizarla
                const fechaCierreActual = $('#fecha_cierre').val();
                if (fechaCierreActual && fechaCierreActual <= fechaInicio) {
                    $('#fecha_cierre').val(nextDayString);
                }
            }
        });

        // Validación en tiempo real de fechas
        $('#fecha_inicio, #fecha_cierre').on('change', function() {
            const fechaInicio = $('#fecha_inicio').val();
            const fechaCierre = $('#fecha_cierre').val();
            
            if (fechaInicio && fechaCierre) {
                const inicio = new Date(fechaInicio);
                const cierre = new Date(fechaCierre);
                
                if (cierre <= inicio) {
                    showAlert('La fecha de cierre debe ser posterior a la fecha de inicio', 'warning');
                    // Corregir automáticamente
                    const nextDay = new Date(inicio);
                    nextDay.setDate(nextDay.getDate() + 1);
                    $('#fecha_cierre').val(nextDay.toISOString().split('T')[0]);
                }
            }
        });
        // ===== FIN VALIDACIONES DE FECHA =====

        // Validaciones en tiempo real
        $('#numero_documento_inscripcion').on('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });

        $('#cantidad_elecciones').on('input', function() {
            const value = parseInt(this.value);
            if (value < 1) this.value = 1;
            if (value > 10) this.value = 10;
        });

        // Validación de descripción del sorteo
        $('#descripcion').on('input', function() {
            const length = this.value.length;
            const maxLength = 500;
            const minLength = 5;
            
            if (length > maxLength) {
                this.value = this.value.substring(0, maxLength);
            }
            
            // Mostrar contador de caracteres
            let counter = this.parentElement.querySelector('.char-counter');
            if (!counter) {
                counter = document.createElement('small');
                counter.className = 'char-counter form-text';
                this.parentElement.appendChild(counter);
            }
            
            const remaining = maxLength - this.value.length;
            counter.textContent = `${this.value.length}/${maxLength} caracteres`;
            
            if (this.value.length < minLength) {
                counter.style.color = '#dc3545'; // Rojo
                counter.textContent += ` (mínimo ${minLength})`;
            } else if (remaining < 50) {
                counter.style.color = '#f59e0b'; // Amarillo
            } else {
                counter.style.color = '#10b981'; // Verde
            }
        });
        // Agregar estos nuevos event listeners:
    $('#editSorteoForm').on('submit', handleEditSorteo);
    
    // Validación en tiempo real para modal de edición
    $('#edit_descripcion').on('input', function() {
        const length = this.value.length;
        const maxLength = 500;
        const minLength = 5;
        
        if (length > maxLength) {
            this.value = this.value.substring(0, maxLength);
        }
        
        let counter = this.parentElement.querySelector('.char-counter');
        if (!counter) {
            counter = document.createElement('small');
            counter.className = 'char-counter form-text';
            this.parentElement.appendChild(counter);
        }
        
        counter.textContent = `${this.value.length}/${maxLength} caracteres`;
        
        if (this.value.length < minLength) {
            counter.style.color = '#dc3545';
            counter.textContent += ` (mínimo ${minLength})`;
        } else if (maxLength - this.value.length < 50) {
            counter.style.color = '#f59e0b';
        } else {
            counter.style.color = '#10b981';
        }
    });
    
    // Validación de fechas para modal de edición
    $('#edit_fecha_inicio, #edit_fecha_cierre').on('change', function() {
        const fechaInicio = $('#edit_fecha_inicio').val();
        const fechaCierre = $('#edit_fecha_cierre').val();
        
        if (fechaInicio && fechaCierre) {
            const inicio = new Date(fechaInicio);
            const cierre = new Date(fechaCierre);
            
            if (cierre <= inicio) {
                showAlert('La fecha de cierre debe ser posterior a la fecha de inicio', 'warning');
                const nextDay = new Date(inicio);
                nextDay.setDate(nextDay.getDate() + 1);
                $('#edit_fecha_cierre').val(nextDay.toISOString().split('T')[0]);
            }
        }
    });
    }

    function showSection(sectionName) {
        // Actualizar navegación
        $('.nav-item').removeClass('active');
        $(`.nav-link[data-section="${sectionName}"]`).parent().addClass('active');

        // Mostrar sección
        $('.content-section').removeClass('active');
        $(`#${sectionName}-section`).addClass('active');

        // Actualizar título
        const titles = {
            dashboard: 'Dashboard',
            sorteos: 'Gestión de Sorteos',
            empleados: 'Gestión de Empleados',
            reportes: 'Reportes y Estadísticas',
            usuarios: 'Gestión de Usuarios',
            logs: 'Logs del Sistema'
        };
        $('#pageTitle').text(titles[sectionName] || sectionName);

        currentSection = sectionName;

        // Cargar datos específicos de la sección
        switch(sectionName) {
            case 'dashboard':
                loadDashboardData();
                break;
            case 'sorteos':
                loadSorteosData();
                break;
            case 'empleados':
                loadEmpleadosData();
                break;
            case 'reportes':
                loadReportesData();
                break;
            case 'usuarios':
                loadUsuariosData();
                break;
            case 'logs':
                loadLogsData();
                break;
        }
    }

    function loadDashboardData() {
        $.ajax({
            url: 'api/dashboard_stats.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    $('#totalSorteos').text(data.stats.total_sorteos || 0);
                    $('#totalParticipantes').text(data.stats.total_participantes || 0);
                    $('#numerosElegidos').text(data.stats.numeros_elegidos || 0);
                    $('#actividadReciente').text(data.stats.actividad_hoy || 0);

                    // Cargar sorteos recientes
                    if (data.sorteos_recientes) {
                        renderSorteosRecientes(data.sorteos_recientes);
                    }

                    // Cargar actividad reciente
                    if (data.actividad_reciente) {
                        renderActividadReciente(data.actividad_reciente);
                    }
                }
            },
            error: function() {
                showAlert('Error al cargar datos del dashboard', 'danger');
            }
        });
    }

    function loadSorteosData() {
        if (sorteosTable) {
            sorteosTable.destroy();
        }

        sorteosTable = $('#sorteosTable').DataTable({
            ajax: {
                url: 'api/sorteos.php',
                type: 'GET',
                dataSrc: 'data'
            },
            columns: [
                { data: 'id' },
                { data: 'descripcion' },
                { 
                    data: 'fecha_inicio_sorteo',
                    render: function(data) {
                        return formatDate(data);
                    }
                },
                { 
                    data: 'fecha_cierre_sorteo',
                    render: function(data) {
                        return formatDate(data);
                    }
                },
                { 
                    data: 'participantes',
                    defaultContent: '0'
                },
                { 
                    data: 'estado_texto',
                    render: function(data, type, row) {
                        const badgeClass = {
                            'Activo': 'success',
                            'Finalizado': 'info',
                            'Inactivo': 'danger'
                        };
                        return `<span class="badge badge-${badgeClass[data] || 'secondary'}">${data}</span>`;
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group">
                                <button class="btn btn-sm btn-primary" onclick="viewSorteo(${row.id})" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="editSorteo(${row.id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                ${row.estado == 1 ? `
                                <button class="btn btn-sm btn-danger" onclick="closeSorteo(${row.id})" title="Cerrar">
                                    <i class="fas fa-stop"></i>
                                </button>
                                ` : ''}
                            </div>
                        `;
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
            },
            responsive: true,
            order: [[0, 'desc']]
        });

        // Cargar sorteos para el select de inscripción
        loadSorteosForSelect();
    }

    function loadEmpleadosData() {
        if (empleadosTable) {
            empleadosTable.destroy();
        }

        empleadosTable = $('#empleadosTable').DataTable({
            ajax: {
                url: 'api/empleados_sorteo.php',
                type: 'GET',
                dataSrc: 'data'
            },
            columns: [
                { data: 'numero_documento' },
                { data: 'nombre_completo' },
                { data: 'cargo' },
                { data: 'sorteo_descripcion' },
                { data: 'cantidad_elecciones' },
                { data: 'elecciones_usadas' },
                { 
                    data: 'estado',
                    render: function(data) {
                        return data == 1 ? 
                            '<span class="badge badge-success">Activo</span>' : 
                            '<span class="badge badge-danger">Inactivo</span>';
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group">
                                <button class="btn btn-sm btn-info" onclick="viewEmpleadoStats(${row.id})" title="Estadísticas">
                                    <i class="fas fa-chart-bar"></i>
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="editEmpleadoSorteo(${row.id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                ${row.estado == 1 ? `
                                <button class="btn btn-sm btn-danger" onclick="removeEmpleadoSorteo(${row.id})" title="Remover">
                                    <i class="fas fa-trash"></i>
                                </button>
                                ` : ''}
                            </div>
                        `;
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
            },
            responsive: true,
            order: [[1, 'asc']]
        });
    }

    function handleCreateSorteo(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        
        // Obtener valores de fecha
        const fechaInicioValue = formData.get('fecha_inicio');
        const fechaCierreValue = formData.get('fecha_cierre');
        const descripcion = formData.get('descripcion');
        
        console.log('Datos del formulario:', {
            descripcion: descripcion,
            fecha_inicio: fechaInicioValue,
            fecha_cierre: fechaCierreValue
        });
        
        // Validaciones básicas
        if (!descripcion || !fechaInicioValue || !fechaCierreValue) {
            showAlert('Todos los campos son obligatorios', 'warning');
            return;
        }
        
        // Validar descripción
        if (descripcion.length < 5) {
            showAlert('La descripción debe tener al menos 5 caracteres', 'warning');
            return;
        }
        
        if (descripcion.length > 500) {
            showAlert('La descripción no puede exceder 500 caracteres', 'warning');
            return;
        }
        
        // Crear objetos Date correctamente
        const fechaInicio = new Date(fechaInicioValue + 'T00:00:00');
        const fechaCierre = new Date(fechaCierreValue + 'T23:59:59');
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0); // Establecer a inicio del día para comparación justa
        
        console.log('Fechas procesadas:', {
            fechaInicio: fechaInicio,
            fechaCierre: fechaCierre,
            hoy: hoy,
            fechaInicioValida: fechaInicio >= hoy,
            fechaCierreValida: fechaCierre > fechaInicio
        });
        
        // Validación de fecha de inicio - CORREGIDA
        if (fechaInicio < hoy) {
            showAlert('La fecha de inicio debe ser hoy o una fecha futura', 'warning');
            return;
        }

        // Validación de fecha de cierre
        if (fechaCierre <= fechaInicio) {
            showAlert('La fecha de cierre debe ser posterior a la fecha de inicio', 'warning');
            return;
        }
        
        // Validación adicional: máximo 1 año de duración
        const unAnoEnMs = 365 * 24 * 60 * 60 * 1000;
        if (fechaCierre.getTime() - fechaInicio.getTime() > unAnoEnMs) {
            showAlert('El sorteo no puede durar más de un año', 'warning');
            return;
        }

        setButtonLoading(submitBtn, true);

        $.ajax({
            url: 'api/create_sorteo.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta del servidor:', response);
                
                if (response.success) {
                    showAlert('✅ Sorteo creado exitosamente', 'success');
                    closeModal('createSorteoModal');
                    document.getElementById('createSorteoForm').reset();
                    
                    // Limpiar contadores de caracteres
                    $('.char-counter').remove();
                    
                    // Recargar datos
                    if (sorteosTable) {
                        sorteosTable.ajax.reload();
                    }
                    loadDashboardData();
                } else {
                    showAlert(response.message || 'Error al crear sorteo', 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', {xhr, status, error});
                showAlert('Error de conexión al crear sorteo', 'danger');
            },
            complete: function() {
                setButtonLoading(submitBtn, false);
            }
        });
    }

    function handleInscribirEmpleado(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        
        // Validaciones
        const documento = formData.get('numero_documento');
        if (documento.length < 6) {
            showAlert('Número de documento inválido', 'warning');
            return;
        }

        const fechaFinal = new Date(formData.get('fecha_final'));
        const hoy = new Date();
        
        if (fechaFinal <= hoy) {
            showAlert('La fecha final debe ser posterior a hoy', 'warning');
            return;
        }

        setButtonLoading(submitBtn, true);

        $.ajax({
            url: 'api/inscribir_empleado.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert('✅ Empleado inscrito exitosamente', 'success');
                    closeModal('inscribirEmpleadoModal');
                    document.getElementById('inscribirEmpleadoForm').reset();
                    
                    if (empleadosTable) {
                        empleadosTable.ajax.reload();
                    }
                    loadDashboardData();
                } else {
                    showAlert(response.message || 'Error al inscribir empleado', 'danger');
                }
            },
            error: function() {
                showAlert('Error de conexión al inscribir empleado', 'danger');
            },
            complete: function() {
                setButtonLoading(submitBtn, false);
            }
        });
    }

    function loadSorteosForSelect() {
        $.ajax({
            url: 'api/sorteos_activos.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                const select = $('#sorteo_select');
                select.empty().append('<option value="">Seleccione un sorteo</option>');
                
                if (data.success && data.sorteos) {
                    data.sorteos.forEach(sorteo => {
                        select.append(`<option value="${sorteo.id}">${sorteo.descripcion}</option>`);
                    });
                }
            }
        });
    }

    // Funciones para secciones no implementadas
    function loadReportesData() {
        console.log('Cargando reportes...');
    }

    function loadUsuariosData() {
        console.log('Cargando usuarios...');
    }

    function loadLogsData() {
        console.log('Cargando logs...');
    }

    // Funciones auxiliares
    function updateCurrentTime() {
        const now = new Date();
        const timeString = now.toLocaleString('es-CO', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        $('#currentTime').text(timeString);
    }

    // En assets/js/admin.js - Reemplazar la función formatDate

function formatDate(dateString) {
    if (!dateString) return '';
    
    // SOLUCIÓN: Crear fecha con zona horaria local
    const parts = dateString.split('-');
    if (parts.length === 3) {
        // Crear fecha directamente sin conversión UTC
        const year = parseInt(parts[0]);
        const month = parseInt(parts[1]) - 1; // JavaScript months are 0-indexed
        const day = parseInt(parts[2]);
        const date = new Date(year, month, day);
        
        return date.toLocaleDateString('es-CO', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
    
    // Fallback para otros formatos
    const date = new Date(dateString + 'T00:00:00');
    return date.toLocaleDateString('es-CO', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

// Función auxiliar corregida
function formatDateCorrect(dateString) {
    if (!dateString) return '';
    
    try {
        // Si viene en formato YYYY-MM-DD
        if (typeof dateString === 'string' && dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
            const [year, month, day] = dateString.split('-').map(Number);
            const date = new Date(year, month - 1, day); // month - 1 porque JS es 0-indexed
            
            return date.toLocaleDateString('es-CO', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }
        
        // Para otros formatos
        const date = new Date(dateString);
        return date.toLocaleDateString('es-CO', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    } catch (error) {
        console.error('Error formateando fecha:', dateString, error);
        return dateString; // Devolver string original si hay error
    }
}

// También actualizar las columnas del DataTable para fechas
function loadSorteosData() {
    if (sorteosTable) {
        sorteosTable.destroy();
    }

    sorteosTable = $('#sorteosTable').DataTable({
        ajax: {
            url: 'api/sorteos.php',
            type: 'GET',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            { data: 'descripcion' },
            { 
                data: 'fecha_inicio_sorteo',
                render: function(data) {
                    return formatDateCorrect(data); // Usar función corregida
                }
            },
            { 
                data: 'fecha_cierre_sorteo',
                render: function(data) {
                    return formatDateCorrect(data); // Usar función corregida
                }
            },
            { 
                data: 'participantes',
                defaultContent: '0'
            },
            { 
                data: 'estado_texto',
                render: function(data, type, row) {
                    const badgeClass = {
                        'Activo': 'success',
                        'Finalizado': 'info',
                        'Inactivo': 'danger'
                    };
                    return `<span class="badge badge-${badgeClass[data] || 'secondary'}">${data}</span>`;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group">
                            <button class="btn btn-sm btn-primary" onclick="viewSorteo(${row.id})" title="Ver">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="editSorteo(${row.id})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            ${row.estado == 1 ? `
                            <button class="btn btn-sm btn-danger" onclick="closeSorteo(${row.id})" title="Cerrar">
                                <i class="fas fa-stop"></i>
                            </button>
                            ` : ''}
                        </div>
                    `;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        responsive: true,
        order: [[0, 'desc']]
    });

    // Cargar sorteos para el select de inscripción
    loadSorteosForSelect();
}

    function setButtonLoading(button, loading) {
        if (loading) {
            button.prop('disabled', true);
            const originalText = button.html();
            button.data('original-text', originalText);
            button.html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
        } else {
            button.prop('disabled', false);
            const originalText = button.data('original-text');
            if (originalText) {
                button.html(originalText);
            }
        }
    }

    function renderSorteosRecientes(sorteos) {
        const container = $('#sorteos-recientes');
        if (sorteos.length === 0) {
            container.html('<p class="text-muted">No hay sorteos recientes</p>');
            return;
        }

        let html = '<div class="list-group">';
        sorteos.forEach(sorteo => {
            html += `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${sorteo.descripcion}</h6>
                            <small class="text-muted">
                                ${formatDate(sorteo.fecha_inicio_sorteo)} - ${formatDate(sorteo.fecha_cierre_sorteo)}
                            </small>
                        </div>
                        <span class="badge badge-primary">${sorteo.participantes || 0} participantes</span>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.html(html);
    }

    function renderActividadReciente(actividades) {
        const container = $('#actividad-reciente');
        if (actividades.length === 0) {
            container.html('<p class="text-muted">No hay actividad reciente</p>');
            return;
        }

        let html = '<div class="list-group">';
        actividades.forEach(actividad => {
            const icon = getActivityIcon(actividad.accion);
            html += `
                <div class="list-group-item">
                    <div class="d-flex align-items-center">
                        <div class="activity-icon mr-3">
                            <i class="fas fa-${icon}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="activity-text">${actividad.accion}</div>
                            <small class="text-muted">${formatDate(actividad.fecha)}</small>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.html(html);
    }

    function getActivityIcon(accion) {
        const icons = {
            'LOGIN': 'sign-in-alt',
            'LOGOUT': 'sign-out-alt',
            'CREATE_SORTEO': 'plus-circle',
            'INSCRIBIR_EMPLEADO': 'user-plus',
            'ELEGIR_NUMERO': 'star'
        };
        return icons[accion] || 'info-circle';
    }

    // Función para editar sorteo
window.editSorteo = function(id) {
    $.ajax({
        url: 'api/get_sorteo.php',
        method: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                openEditSorteoModal(response.sorteo);
            } else {
                showAlert('Error al cargar datos del sorteo', 'danger');
            }
        },
        error: function() {
            showAlert('Error de conexión al cargar sorteo', 'danger');
        }
    });
};

// Función para abrir modal de edición
function openEditSorteoModal(sorteo) {
    $('#edit_sorteo_id').val(sorteo.id);
    $('#edit_descripcion').val(sorteo.descripcion);
    $('#edit_fecha_inicio').val(sorteo.fecha_inicio_sorteo);
    $('#edit_fecha_cierre').val(sorteo.fecha_cierre_sorteo);
    
    const today = new Date().toISOString().split('T')[0];
    $('#edit_fecha_inicio').attr('min', today);
    $('#edit_fecha_cierre').attr('min', today);
    
    openModal('editSorteoModal');
}

// Función para manejar la actualización del sorteo
function handleEditSorteo(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = $(this).find('button[type="submit"]');
    
    const descripcion = formData.get('descripcion');
    const fechaInicio = formData.get('fecha_inicio');
    const fechaCierre = formData.get('fecha_cierre');
    
    if (!descripcion || !fechaInicio || !fechaCierre) {
        showAlert('Todos los campos son obligatorios', 'warning');
        return;
    }
    
    if (descripcion.length < 5 || descripcion.length > 500) {
        showAlert('La descripción debe tener entre 5 y 500 caracteres', 'warning');
        return;
    }
    
    const inicio = new Date(fechaInicio + 'T00:00:00');
    const cierre = new Date(fechaCierre + 'T23:59:59');
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    
    if (inicio < hoy) {
        showAlert('La fecha de inicio debe ser hoy o una fecha futura', 'warning');
        return;
    }
    
    if (cierre <= inicio) {
        showAlert('La fecha de cierre debe ser posterior a la fecha de inicio', 'warning');
        return;
    }
    
    setButtonLoading(submitBtn, true);
    
    $.ajax({
        url: 'api/update_sorteo.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('✅ Sorteo actualizado exitosamente', 'success');
                closeModal('editSorteoModal');
                
                if (sorteosTable) {
                    sorteosTable.ajax.reload();
                }
                loadDashboardData();
            } else {
                showAlert(response.message || 'Error al actualizar sorteo', 'danger');
            }
        },
        error: function() {
            showAlert('Error de conexión al actualizar sorteo', 'danger');
        },
        complete: function() {
            setButtonLoading(submitBtn, false);
        }
    });
}

// Función para ver detalles del sorteo
window.viewSorteo = function(id) {
    $.ajax({
        url: 'api/get_sorteo_details.php',
        method: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                openViewSorteoModal(response.sorteo, response.participantes, response.estadisticas);
            } else {
                showAlert('Error al cargar detalles del sorteo', 'danger');
            }
        },
        error: function() {
            showAlert('Error de conexión', 'danger');
        }
    });
};

// Función para mostrar modal de vista de sorteo
function openViewSorteoModal(sorteo, participantes, estadisticas) {
    $('#view_sorteo_descripcion').text(sorteo.descripcion);
    $('#view_sorteo_fecha_inicio').text(formatDateCorrect(sorteo.fecha_inicio_sorteo));
    $('#view_sorteo_fecha_cierre').text(formatDateCorrect(sorteo.fecha_cierre_sorteo));
    $('#view_sorteo_estado').html(`<span class="badge badge-${sorteo.estado == 1 ? 'success' : 'danger'}">${sorteo.estado == 1 ? 'Activo' : 'Inactivo'}</span>`);
    
    $('#view_total_participantes').text(estadisticas.total_participantes || 0);
    $('#view_numeros_elegidos').text(estadisticas.numeros_elegidos || 0);
    $('#view_numeros_disponibles').text(estadisticas.numeros_disponibles || 0);
    
    let participantesHtml = '';
    if (participantes && participantes.length > 0) {
        participantesHtml = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Documento</th><th>Nombre</th><th>Elecciones</th><th>Usadas</th></tr></thead><tbody>';
        participantes.forEach(p => {
            participantesHtml += `
                <tr>
                    <td>${p.numero_documento}</td>
                    <td>${p.nombre_completo}</td>
                    <td>${p.cantidad_elecciones}</td>
                    <td>${p.elecciones_usadas}</td>
                </tr>
            `;
        });
        participantesHtml += '</tbody></table></div>';
    } else {
        participantesHtml = '<p class="text-muted">No hay participantes inscritos</p>';
    }
    $('#view_participantes_list').html(participantesHtml);
    
    openModal('viewSorteoModal');
}

    // Funciones globales
    window.openModal = function(modalId) {
        $(`#${modalId}`).addClass('show').css('display', 'flex');
        
        // Si es el modal de crear sorteo, establecer fechas mínimas
        if (modalId === 'createSorteoModal') {
            const today = new Date();
            const todayString = today.toISOString().split('T')[0];
            
            // Establecer fechas mínimas
            $('#fecha_inicio').attr('min', todayString);
            $('#fecha_cierre').attr('min', todayString);
            
            // Establecer fecha de inicio por defecto a hoy
            $('#fecha_inicio').val(todayString);
            
            // Establecer fecha de cierre por defecto a 30 días después
            const futureDate = new Date();
            futureDate.setDate(futureDate.getDate() + 30);
            const futureDateString = futureDate.toISOString().split('T')[0];
            $('#fecha_cierre').val(futureDateString);
            
            // Limpiar descripción y contadores
            $('#descripcion').val('');
            $('.char-counter').remove();
            
            console.log('Modal abierto con fechas:', {
                inicio: todayString,
                cierre: futureDateString
            });
        }
        
        // Si es el modal de inscribir empleado, cargar sorteos activos
        if (modalId === 'inscribirEmpleadoModal') {
            loadSorteosForSelect();
            
            // Establecer fecha final por defecto
            const futureDate = new Date();
            futureDate.setDate(futureDate.getDate() + 90); // 3 meses
            const futureDateString = futureDate.toISOString().split('T')[0];
            $('#fecha_final').val(futureDateString);
        }
    };

    window.closeModal = function(modalId) {
        $(`#${modalId}`).removeClass('show').css('display', 'none');
        
        // Limpiar formularios al cerrar
        if (modalId === 'createSorteoModal') {
            document.getElementById('createSorteoForm').reset();
            $('.char-counter').remove();
        }
        
        if (modalId === 'inscribirEmpleadoModal') {
            document.getElementById('inscribirEmpleadoForm').reset();
        }
    };

    window.closeAllModals = function() {
        $('.modal').removeClass('show').css('display', 'none');
        
        // Limpiar todos los formularios
        $('#createSorteoForm')[0].reset();
        $('#inscribirEmpleadoForm')[0].reset();
        $('.char-counter').remove();
    };

    window.showSection = showSection;

    window.logout = function() {
        if (confirm('¿Está seguro de que desea cerrar sesión?')) {
            window.location.href = 'logout.php';
        }
    };

    window.showAlert = function(message, type = 'info') {
        const alertContainer = $('#alertContainer');
        const alertId = 'alert-' + Date.now();
        
        const alert = $(`
            <div id="${alertId}" class="alert alert-${type}" style="display: none;">
                <div class="d-flex justify-content-between align-items-center">
                    <span>${message}</span>
                    <button type="button" class="btn-close" onclick="$('#${alertId}').fadeOut(300, function(){ $(this).remove(); })">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `);

        alertContainer.append(alert);
        alert.fadeIn(300);

        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if ($(`#${alertId}`).length) {
                $(`#${alertId}`).fadeOut(300, function() { $(this).remove(); });
            }
        }, 5000);
    };

    // Funciones específicas de sorteos
    // window.viewSorteo = function(id) {
    //     showAlert('Función de vista detallada en desarrollo', 'info');
    // };

    // window.editSorteo = function(id) {
    //     showAlert('Función de edición en desarrollo', 'info');
    // };

    window.closeSorteo = function(id) {
        if (confirm('¿Está seguro de que desea cerrar este sorteo?')) {
            $.ajax({
                url: 'api/close_sorteo.php',
                method: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('✅ Sorteo cerrado exitosamente', 'success');
                        if (sorteosTable) {
                            sorteosTable.ajax.reload();
                        }
                        loadDashboardData();
                    } else {
                        showAlert(response.message || 'Error al cerrar sorteo', 'danger');
                    }
                },
                error: function() {
                    showAlert('Error de conexión', 'danger');
                }
            });
        }
    };

    // Funciones específicas de empleados
    window.viewEmpleadoStats = function(id) {
        showAlert('Función de estadísticas en desarrollo', 'info');
    };

    window.editEmpleadoSorteo = function(id) {
        showAlert('Función de edición en desarrollo', 'info');
    };

    window.removeEmpleadoSorteo = function(id) {
        if (confirm('¿Está seguro de que desea remover este empleado del sorteo?')) {
            $.ajax({
                url: 'api/remove_empleado_sorteo.php',
                method: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('✅ Empleado removido exitosamente', 'success');
                        if (empleadosTable) {
                            empleadosTable.ajax.reload();
                        }
                        loadDashboardData();
                    } else {
                        showAlert(response.message || 'Error al remover empleado', 'danger');
                    }
                },
                error: function() {
                    showAlert('Error de conexión', 'danger');
                }
            });
        }
    };
});