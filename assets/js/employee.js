// assets/js/employee.js

$(document).ready(function() {
    // Variables globales
    let currentTab = 'elegir';
    let selectedNumber = null;
    let availableNumbers = [];

    // Inicialización
    initializeApp();

    function initializeApp() {
        loadEmployeeData();
        generateSuggestedNumbers();
        initializeEventListeners();
        
        // Cargar primera tab
        showTab('elegir');
    }

    function initializeEventListeners() {
        // Tabs navigation
        $('.tab-btn').on('click', function() {
            const tab = $(this).data('tab');
            showTab(tab);
        });

        // Formulario elegir número
        $('#elegirNumeroForm').on('submit', handleElegirNumero);

        // Input número con validación en tiempo real
        $('#numero_balota').on('input', function() {
            validateNumberInput(this);
        });

        // Números sugeridos
        $(document).on('click', '.suggested-number', function() {
            const number = $(this).data('number');
            $('#numero_balota').val(number);
            validateNumberInput(document.getElementById('numero_balota'));
        });

        // Modal confirmación
        $('#confirmModal .btn-primary').on('click', confirmarEleccion);

        // Cerrar modales
        $('.modal-close, .modal').on('click', function(e) {
            if (e.target === this) {
                closeAllModals();
            }
        });

        // Actualizar datos cada 30 segundos
        setInterval(loadEmployeeData, 30000);
    }

    function loadEmployeeData() {
        $.ajax({
            url: 'api/employee_data.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    updateEmployeeStats(data.stats);
                    updateSorteoInfo(data.sorteo_info);
                    
                    if (data.mis_numeros) {
                        renderMisNumeros(data.mis_numeros);
                    }
                    
                    if (data.historial) {
                        renderHistorial(data.historial);
                    }
                }
            },
            error: function() {
                showAlert('Error al cargar datos', 'danger');
            }
        });
    }

    function updateEmployeeStats(stats) {
        $('#eleccionesDisponibles').text(stats.elecciones_restantes || 0);
        $('#numerosElegidos').text(stats.numeros_elegidos || 0);
        $('#fechaInicioSorteo').text(stats.fecha_inicio_sorteo || 0);
        $('#fechaCierreSorteo').text(stats.fecha_cierre_sorteo || 0);
        
        // Calcular tiempo restante
        if (stats.fecha_cierre) {
            updateTimeRemaining(stats.fecha_cierre);
        }

        // Actualizar botón si no hay elecciones disponibles
        const hasElecciones = (stats.elecciones_restantes || 0) > 0;
        $('#elegirBtn').prop('disabled', !hasElecciones);
        
        if (!hasElecciones) {
            $('#elegirBtn').html('<i class="fas fa-ban"></i> Sin Elecciones Disponibles');
        }
    }

    function updateSorteoInfo(info) {
        $('#sorteoActivo').text(info.nombre || 'No disponible');
        $('#numerosDisponibles').text(info.numeros_disponibles || 0);
        $('#totalParticipantes').text(info.total_participantes || 0);
        
    }

    function updateTimeRemaining(fechaCierre) {
        const now = new Date();
        const end = new Date(fechaCierre);
        const diff = end - now;

        if (diff <= 0) {
            $('#tiempoRestante').text('Finalizado');
            $('#elegirBtn').prop('disabled', true).html('<i class="fas fa-clock"></i> Sorteo Finalizado');
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        
        if (days > 0) {
            $('#tiempoRestante').text(`${days}d ${hours}h`);
        } else {
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            $('#tiempoRestante').text(`${hours}h ${minutes}m`);
        }
    }

    function showTab(tabName) {
        // Actualizar navegación
        $('.tab-btn').removeClass('active');
        $(`.tab-btn[data-tab="${tabName}"]`).addClass('active');

        // Mostrar panel
        $('.tab-panel').removeClass('active');
        $(`#${tabName}-tab`).addClass('active');

        currentTab = tabName;

        // Cargar datos específicos si es necesario
        if (tabName === 'mis-numeros') {
            loadMisNumeros();
        } else if (tabName === 'historial') {
            loadHistorial();
        }
    }

    function generateSuggestedNumbers() {
        const container = $('#numerosSugeridos');
        container.empty();

        // Generar 6 números aleatorios entre 100 y 800
        const numbers = [];
        while (numbers.length < 6) {
            const num = Math.floor(Math.random() * 701) + 100; // 100-800
            if (!numbers.includes(num)) {
                numbers.push(num);
            }
        }

        numbers.forEach(num => {
            const div = $(`
                <div class="suggested-number" data-number="${num}">
                    ${num}
                </div>
            `);
            container.append(div);
        });
    }

    function validateNumberInput(input) {
        const value = parseInt(input.value);
        const isValid = value >= 100 && value <= 800;

        // Mostrar/ocultar preview
        if (isValid) {
            selectedNumber = value;
            showNumberPreview(value);
            $('#elegirBtn').prop('disabled', false);
        } else {
            selectedNumber = null;
            hideNumberPreview();
            $('#elegirBtn').prop('disabled', true);
        }

        // Actualizar estilo del input
        if (input.value && !isValid) {
            $(input).addClass('is-invalid').removeClass('is-valid');
        } else if (isValid) {
            $(input).addClass('is-valid').removeClass('is-invalid');
        } else {
            $(input).removeClass('is-invalid is-valid');
        }
    }

    function showNumberPreview(number) {
        // Calcular representación binaria
        const binary = number.toString(2);
        
        $('#numeroSeleccionado').text(number);
        $('#binarioSeleccionado').text(binary);
        $('#numeroPreview').fadeIn(300);
    }

    function hideNumberPreview() {
        $('#numeroPreview').fadeOut(300);
    }

    function handleElegirNumero(e) {
        e.preventDefault();

        if (!selectedNumber) {
            showAlert('Por favor seleccione un número válido', 'warning');
            return;
        }

        // Mostrar modal de confirmación
        $('#numeroConfirmar').text(selectedNumber);
        openModal('confirmModal');
    }

    function confirmarEleccion() {
        if (!selectedNumber) return;

        const submitBtn = $('#confirmModal .btn-primary');
        setButtonLoading(submitBtn, true);

        $.ajax({
            url: 'api/elegir_numero.php',
            method: 'POST',
            data: {
                numero_balota: selectedNumber
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert('¡Número elegido exitosamente!', 'success');
                    closeModal('confirmModal');
                    
                    // Limpiar formulario
                    $('#numero_balota').val('').removeClass('is-valid');
                    hideNumberPreview();
                    selectedNumber = null;
                    
                    // Recargar datos
                    loadEmployeeData();
                    generateSuggestedNumbers();
                    
                    // Cambiar a tab de mis números
                    setTimeout(() => showTab('mis-numeros'), 1000);
                } else {
                    showAlert(response.message || 'Error al elegir número', 'danger');
                }
            },
            error: function() {
                showAlert('Error de conexión', 'danger');
            },
            complete: function() {
                setButtonLoading(submitBtn, false);
            }
        });
    }

    function loadMisNumeros() {
        $.ajax({
            url: 'api/mis_numeros.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    renderMisNumeros(data.numeros);
                }
            },
            error: function() {
                showAlert('Error al cargar números', 'danger');
            }
        });
    }

    function loadHistorial() {
        $.ajax({
            url: 'api/historial.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    renderHistorial(data.historial);
                }
            },
            error: function() {
                showAlert('Error al cargar historial', 'danger');
            }
        });
    }

    function renderMisNumeros(numeros) {
        const container = $('#misNumerosContainer');

        if (!numeros || numeros.length === 0) {
            container.html(`
                <div class="text-center py-5">
                    <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No has elegido números aún</h4>
                    <p class="text-muted">Ve a la pestaña "Elegir Número" para seleccionar tu número de la suerte</p>
                    <button class="btn btn-primary" onclick="showTab('elegir')">
                        <i class="fas fa-plus"></i> Elegir Número
                    </button>
                </div>
            `);
            return;
        }

        let html = '<div class="numbers-grid">';
        numeros.forEach(numero => {
            const date = new Date(numero.fecha_eleccion);
            html += `
                <div class="number-card" data-number="${numero.numero_balota}">
                    <div class="number">${numero.numero_balota}</div>
                    <div class="date">${formatDateTime(date)}</div>
                    <div class="binary-small">
                        <small>Binario: ${numero.equivalencia_binaria}</small>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        container.html(html);
    }

    function renderHistorial(historial) {
        const container = $('#historialContainer');

        if (!historial || historial.length === 0) {
            container.html(`
                <div class="text-center py-5">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay historial disponible</h4>
                    <p class="text-muted">Tu historial de participaciones aparecerá aquí</p>
                </div>
            `);
            return;
        }

        // Agrupar por sorteo
        const groupedByLottery = historial.reduce((acc, item) => {
            const sorteo = item.sorteo_nombre || 'Sorteo Desconocido';
            if (!acc[sorteo]) {
                acc[sorteo] = [];
            }
            acc[sorteo].push(item);
            return acc;
        }, {});

        let html = '';
        Object.keys(groupedByLottery).forEach(sorteoNombre => {
            const numeros = groupedByLottery[sorteoNombre];
            html += `
                <div class="historial-group">
                    <h5 class="historial-title">
                        <i class="fas fa-trophy"></i>
                        ${sorteoNombre}
                        <span class="badge badge-secondary">${numeros.length} números</span>
                    </h5>
                    <div class="numbers-grid">
            `;
            
            numeros.forEach(numero => {
                const date = new Date(numero.fecha_eleccion);
                html += `
                    <div class="number-card small">
                        <div class="number">${numero.numero_balota}</div>
                        <div class="date">${formatDateTime(date)}</div>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
        });

        container.html(html);
    }

    // Funciones auxiliares
    function formatDateTime(date) {
        return date.toLocaleDateString('es-CO', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
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

    // Funciones globales
    window.openModal = function(modalId) {
        $(`#${modalId}`).addClass('show').css('display', 'flex');
    };

    window.closeModal = function(modalId) {
        $(`#${modalId}`).removeClass('show').css('display', 'none');
    };

    window.closeAllModals = function() {
        $('.modal').removeClass('show').css('display', 'none');
    };

    window.showTab = showTab;

    window.confirmarEleccion = confirmarEleccion;

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

    // Agregar estilos CSS específicos dinámicamente
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .is-invalid {
                border-color: #dc3545 !important;
                box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
            }
            .is-valid {
                border-color: #28a745 !important;
                box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
            }
            .binary-small {
                margin-top: 0.5rem;
                opacity: 0.7;
            }
            .historial-group {
                margin-bottom: 2rem;
                padding: 1rem;
                background: white;
                border-radius: 0.5rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .historial-title {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin-bottom: 1rem;
                padding-bottom: 0.5rem;
                border-bottom: 1px solid #e5e7eb;
                color: #374151;
            }
            .number-card.small .number {
                font-size: 1.25rem;
            }
            .btn-close {
                background: none;
                border: none;
                font-size: 1.2rem;
                cursor: pointer;
                color: inherit;
                opacity: 0.7;
                transition: opacity 0.15s ease-in-out;
            }
            .btn-close:hover {
                opacity: 1;
            }
        `)
        .appendTo('head');
});