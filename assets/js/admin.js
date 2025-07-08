// assets/js/admin.js - Versión completa corregida

$(document).ready(function() {
    // Variables globales
    let currentSection = 'dashboard';
    let sorteosTable, empleadosTable, usuariosTable;

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
        $('#registrarUsuarioForm').on('submit', handleRegistrarUsuario);
        $('#editSorteoForm').on('submit', handleEditSorteo); // ← AGREGAR ESTA LÍNEA
        
        // Agregar event listeners para los formularios
        $('#editUsuarioForm').on('submit', handleEditUsuario);
        $('#resetPasswordForm').on('submit', handleResetPassword);
        
        // Event listener para cerrar modales con ESC
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAllModals();
            }
        });

        // Modales
        $('.modal-close, .modal').on('click', function(e) {
            if (e.target === this) {
                closeAllModals();
            }
        });

        // ===== 11. FUNCIÓN PARA CERRAR TODOS LOS MODALES =====
        window.closeAllModals = function() {
            $('.modal').removeClass('show').css('display', 'none');
            
            // Limpiar formularios
            $('#editUsuarioForm')[0].reset();
            $('#resetPasswordForm')[0].reset();
        };

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
                // console.log("=== CARGANDO SECCIÓN USUARIOS ===");
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

    // function loadSorteosData() {
    //     if (sorteosTable) {
    //         sorteosTable.destroy();
    //     }

    //     sorteosTable = $('#sorteosTable').DataTable({
    //         ajax: {
    //             url: 'api/sorteos.php',
    //             type: 'GET',
    //             dataSrc: 'data'
    //         },
    //         columns: [
    //             { data: 'id' },
    //             { data: 'descripcion' },
    //             { 
    //                 data: 'fecha_inicio_sorteo',
    //                 render: function(data) {
    //                     return formatDate(data);
    //                 }
    //             },
    //             { 
    //                 data: 'fecha_cierre_sorteo',
    //                 render: function(data) {
    //                     return formatDate(data);
    //                 }
    //             },
    //             { 
    //                 data: 'participantes',
    //                 defaultContent: '0'
    //             },
    //             { 
    //                 data: 'estado_texto',
    //                 render: function(data, type, row) {
    //                     const badgeClass = {
    //                         'Activo': 'success',
    //                         'Finalizado': 'info',
    //                         'Inactivo': 'danger'
    //                     };
    //                     return `<span class="badge badge-${badgeClass[data] || 'secondary'}">${data}</span>`;
    //                 }
    //             },
    //             {
    //                 data: null,
    //                 render: function(data, type, row) {
    //                     return `
    //                         <div class="btn-group">
    //                             <button class="btn btn-sm btn-primary" onclick="viewSorteo(${row.id})" title="Ver">
    //                                 <i class="fas fa-eye"></i>
    //                             </button>
    //                             <button class="btn btn-sm btn-warning" onclick="editSorteo(${row.id})" title="Editar">
    //                                 <i class="fas fa-edit"></i>
    //                             </button>
    //                             ${row.estado == 1 ? `
    //                             <button class="btn btn-sm btn-danger" onclick="closeSorteo(${row.id})" title="Cerrar">
    //                                 <i class="fas fa-stop"></i>
    //                             </button>
    //                             ` : ''}
    //                         </div>
    //                     `;
    //                 }
    //             }
    //         ],
    //         language: {
    //             url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
    //         },
    //         responsive: true,
    //         order: [[0, 'desc']]
    //     });

    //     // Cargar sorteos para el select de inscripción
    //     loadSorteosForSelect();
    //     loadEmpleadoForSelect();
    //     loadModeradorForSelect();
    // }

    // function loadEmpleadosData() {
    //     if (empleadosTable) {
    //         empleadosTable.destroy();
    //     }

    //     empleadosTable = $('#empleadosTable').DataTable({
    //         ajax: {
    //             url: 'api/empleados_sorteo.php',
    //             type: 'GET',
    //             dataSrc: 'data',
    //             error: function(xhr, error, code) {
    //             console.error('Error cargando empleados:', xhr.responseText);
    //             showAlert('Error al cargar los datos de empleados', 'danger');
    //         }

    //         },
    //         columns: [
    //             { data: 'numero_documento' },
    //             { data: 'nombre_completo' },
    //             { data: 'cargo' },
    //             { data: 'sorteo_descripcion' },
    //             { data: 'cantidad_elecciones' },
    //             { data: 'elecciones_usadas' },
    //             { data: 'numero_balota' },
    //             { 
    //                 data: 'estado',
    //                 render: function(data) {
    //                     //console.log(data);
    //                     return data == 1 ? 
    //                         '<span class="badge badge-success">Activo</span>' : 
    //                         '<span class="badge badge-danger">Inactivo</span>';
    //                 }
    //             },
    //             {
    //                 data: null,
    //                 render: function(data, type, row) {
    //                     return `
    //                         <div class="btn-group">
    //                             <button class="btn btn-sm btn-info" onclick="viewEmpleadoStats(${row.id})" title="Estadísticas">
    //                                 <i class="fas fa-chart-bar"></i>
    //                             </button>
    //                             <button class="btn btn-sm btn-warning" onclick="editEmpleadoSorteo(${row.id})" title="Editar">
    //                                 <i class="fas fa-edit"></i>
    //                             </button>
    //                             ${row.estado == 1 ? `
    //                             <button class="btn btn-sm btn-danger" onclick="removeEmpleadoSorteo(${row.id})" title="Remover">
    //                                 <i class="fas fa-trash"></i>
    //                             </button>
    //                             ` : ''}
    //                         </div>
    //                     `;
    //                 }
    //             }
    //         ],
    //         language: {
    //             url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
    //         },
    //         responsive: true,
    //         order: [[1, 'asc']]
    //     });
    // }
    // function loadEmpleadosData() {
    //     if (empleadosTable) {
    //         empleadosTable.destroy();
    //     }

    //     empleadosTable = $('#empleadosTable').DataTable({
    //         ajax: {
    //             url: 'api/empleados_sorteo.php',
    //             type: 'GET',
    //             dataSrc: 'data',
    //             error: function(xhr, error, code) {
    //                 console.error('Error cargando empleados:', xhr.responseText);
    //                 showAlert('Error al cargar los datos de empleados', 'danger');
    //             }
    //         },
    //         // ===== BOTONES DE EXPORTACIÓN =====
    //         dom: 'Bfrtip', // Esto incluye los botones en el layout
    //         buttons: [
    //             {
    //                 extend: 'copy',
    //                 text: '<i class="fas fa-copy"></i> Copiar',
    //                 className: 'btn btn-sm btn-secondary',
    //                 exportOptions: {
    //                     columns: [0, 1, 2, 3, 4, 5, 6, 7] // Excluir la columna de acciones (índice 8)
    //                 }
    //             },
    //             {
    //                 extend: 'csv',
    //                 text: '<i class="fas fa-file-csv"></i> CSV',
    //                 className: 'btn btn-sm btn-success',
    //                 filename: 'empleados_sorteo_' + new Date().getTime(),
    //                 exportOptions: {
    //                     columns: [0, 1, 2, 3, 4, 5, 6, 7],
    //                     modifier: {
    //                         // Exportar datos procesados (sin HTML)
    //                         stripHtml: false
    //                     }
    //                 },
    //                 customize: function(csv) {
    //                     // Limpiar badges HTML del CSV
    //                     return csv.replace(/<[^>]*>/g, '');
    //                 }
    //             },
    //             {
    //                 extend: 'excel',
    //                 text: '<i class="fas fa-file-excel"></i> Excel',
    //                 className: 'btn btn-sm btn-primary',
    //                 filename: 'empleados_sorteo_' + new Date().getTime(),
    //                 exportOptions: {
    //                     columns: [0, 1, 2, 3, 4, 5, 6, 7]
    //                 },
    //                 customize: function(xlsx) {
    //                     // Personalizar el archivo Excel
    //                     var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        
    //                     // Agregar estilo a la cabecera
    //                     $('row:first c', sheet).attr('s', '32');
                        
    //                     // Limpiar HTML de las celdas
    //                     $('c[r^="H"] t', sheet).each(function() {
    //                         var text = $(this).text();
    //                         $(this).text(text.replace(/Activo|Inactivo/, function(match) {
    //                             return match;
    //                         }));
    //                     });
    //                 }
    //             },
    //             {
    //                 extend: 'pdf',
    //                 text: '<i class="fas fa-file-pdf"></i> PDF',
    //                 className: 'btn btn-sm btn-danger',
    //                 filename: 'empleados_sorteo_' + new Date().getTime(),
    //                 orientation: 'landscape', // Orientación horizontal
    //                 pageSize: 'A4',
    //                 exportOptions: {
    //                     columns: [0, 1, 2, 3, 4, 5, 6, 7]
    //                 },
    //                 customize: function(doc) {
    //                     // Personalizar el PDF
    //                     doc.content[1].table.widths = ['12%', '20%', '15%', '20%', '8%', '8%', '10%', '7%'];
                        
    //                     // Estilo del título
    //                     doc.content.splice(0, 1, {
    //                         text: 'Reporte de Empleados en Sorteo',
    //                         style: 'title',
    //                         alignment: 'center',
    //                         margin: [0, 0, 0, 20]
    //                     });
                        
    //                     // Agregar fecha de generación
    //                     doc.content.splice(1, 0, {
    //                         text: 'Generado el: ' + new Date().toLocaleString('es-CO'),
    //                         style: 'subheader',
    //                         alignment: 'right',
    //                         margin: [0, 0, 0, 10]
    //                     });
                        
    //                     // Estilos personalizados
    //                     doc.styles.title = {
    //                         fontSize: 16,
    //                         bold: true,
    //                         color: '#2563eb'
    //                     };
    //                     doc.styles.subheader = {
    //                         fontSize: 10,
    //                         color: '#6b7280'
    //                     };
                        
    //                     // Limpiar HTML de las celdas
    //                     var table = doc.content[2].table;
    //                     for (var i = 1; i < table.body.length; i++) {
    //                         for (var j = 0; j < table.body[i].length; j++) {
    //                             if (typeof table.body[i][j].text === 'string') {
    //                                 table.body[i][j].text = table.body[i][j].text.replace(/<[^>]*>/g, '');
    //                             }
    //                         }
    //                     }
    //                 }
    //             },
    //             {
    //                 extend: 'print',
    //                 text: '<i class="fas fa-print"></i> Imprimir',
    //                 className: 'btn btn-sm btn-info',
    //                 exportOptions: {
    //                     columns: [0, 1, 2, 3, 4, 5, 6, 7]
    //                 },
    //                 customize: function(win) {
    //                     // Personalizar la ventana de impresión
    //                     $(win.document.body)
    //                         .css('font-size', '12px')
    //                         .prepend(
    //                             '<div style="text-align:center; margin-bottom: 20px;">' +
    //                             '<h2>Reporte de Empleados en Sorteo</h2>' +
    //                             '<p>Generado el: ' + new Date().toLocaleString('es-CO') + '</p>' +
    //                             '</div>'
    //                         );
                        
    //                     // Limpiar badges HTML
    //                     $(win.document.body).find('table tbody td').each(function() {
    //                         var text = $(this).html();
    //                         if (text.includes('badge')) {
    //                             $(this).html(text.replace(/<[^>]*>/g, ''));
    //                         }
    //                     });
                        
    //                     // Estilos adicionales para impresión
    //                     $(win.document.body).find('table')
    //                         .addClass('compact')
    //                         .css('font-size', '11px');
    //                 }
    //             }
    //         ],
    //         columns: [
    //             { 
    //                 data: 'numero_documento',
    //                 title: 'Documento'
    //             },
    //             { 
    //                 data: 'nombre_completo',
    //                 title: 'Nombre Completo'
    //             },
    //             { 
    //                 data: 'cargo',
    //                 title: 'Cargo'
    //             },
    //             { 
    //                 data: 'sorteo_descripcion',
    //                 title: 'Sorteo'
    //             },
    //             { 
    //                 data: 'cantidad_elecciones',
    //                 title: 'Elecciones'
    //             },
    //             { 
    //                 data: 'elecciones_usadas',
    //                 title: 'Usadas'
    //             },
    //             { 
    //                 data: 'numero_balota',
    //                 title: 'Número Balota',
    //                 render: function(data) {
    //                     return data ? data : '<span class="text-muted">Sin elegir</span>';
    //                 }
    //             },
    //             { 
    //                 data: 'estado',
    //                 title: 'Estado',
    //                 render: function(data) {
    //                     return data == 1 ? 
    //                         '<span class="badge badge-success">Activo</span>' : 
    //                         '<span class="badge badge-danger">Inactivo</span>';
    //                 }
    //             },
    //             {
    //                 data: null,
    //                 title: 'Acciones',
    //                 orderable: false,
    //                 searchable: false,
    //                 render: function(data, type, row) {
    //                     return `
    //                         <div class="btn-group">
    //                             <button class="btn btn-sm btn-info" onclick="viewEmpleadoStats(${row.id})" title="Estadísticas">
    //                                 <i class="fas fa-chart-bar"></i>
    //                             </button>
    //                             <button class="btn btn-sm btn-warning" onclick="editEmpleadoSorteo(${row.id})" title="Editar">
    //                                 <i class="fas fa-edit"></i>
    //                             </button>
    //                             ${row.estado == 1 ? `
    //                             <button class="btn btn-sm btn-danger" onclick="removeEmpleadoSorteo(${row.id})" title="Remover">
    //                                 <i class="fas fa-trash"></i>
    //                             </button>
    //                             ` : ''}
    //                         </div>
    //                     `;
    //                 }
    //             }
    //         ],
    //         language: {
    //             url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json',
    //             buttons: {
    //                 copy: 'Copiar',
    //                 csv: 'CSV',
    //                 excel: 'Excel',
    //                 pdf: 'PDF',
    //                 print: 'Imprimir',
    //                 copyTitle: 'Copiado al portapapeles',
    //                 copySuccess: {
    //                     1: "Copiada 1 fila al portapapeles",
    //                     _: "Copiadas %d filas al portapapeles"
    //                 }
    //             }
    //         },
    //         responsive: true,
    //         order: [[1, 'asc']],
    //         // ===== CONFIGURACIONES ADICIONALES =====
    //         pageLength: 25, // Mostrar más registros por página
    //         lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
    //         processing: true, // Mostrar indicador de procesamiento
    //         // Estilo de los botones
    //         initComplete: function() {
    //             // Personalizar los botones después de la inicialización
    //             $('.dt-buttons').addClass('mb-3');
    //             $('.dt-button').removeClass('dt-button');
    //         }
    //     });
    // }

    function loadEmpleadosData() {
            if (empleadosTable) {
                empleadosTable.destroy();
            }

            empleadosTable = $('#empleadosTable').DataTable({
                ajax: {
                    url: 'api/empleados_sorteo.php',
                    type: 'GET',
                    dataSrc: 'data',
                    error: function(xhr, error, code) {
                        console.error('Error cargando empleados:', xhr.responseText);
                        showAlert('Error al cargar los datos de empleados', 'danger');
                    }
                },
                // ===== LAYOUT CORREGIDO =====
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"B>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                
                // ===== BOTONES DE EXPORTACIÓN =====
                buttons: [
                    {
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i> Copiar',
                        className: 'btn btn-sm btn-secondary mr-1',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7] // Excluir la columna de acciones
                        }
                    },
                    {
                        extend: 'csv',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'btn btn-sm btn-success mr-1',
                        filename: 'empleados_sorteo_' + new Date().toISOString().slice(0,10),
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        },
                        customize: function(csv) {
                            // Limpiar badges HTML del CSV
                            return csv.replace(/<span[^>]*>/g, '').replace(/<\/span>/g, '');
                        }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-sm btn-primary mr-1',
                        filename: 'empleados_sorteo_' + new Date().toISOString().slice(0,10),
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-sm btn-danger mr-1',
                        filename: 'empleados_sorteo_' + new Date().toISOString().slice(0,10),
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        },
                        customize: function(doc) {
                            // Título del documento
                            doc.content[1].table.headerRows = 1;
                            doc.content[1].table.widths = ['12%', '22%', '15%', '18%', '8%', '8%', '10%', '7%'];
                            
                            // Agregar título
                            doc.content.splice(0, 1, {
                                text: 'Reporte de Empleados en Sorteo',
                                style: 'title',
                                alignment: 'center',
                                margin: [0, 0, 0, 20]
                            });
                            
                            // Fecha de generación
                            doc.content.splice(1, 0, {
                                text: 'Fecha: ' + new Date().toLocaleDateString('es-CO'),
                                alignment: 'right',
                                margin: [0, 0, 0, 10]
                            });
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        className: 'btn btn-sm btn-info mr-1',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    }
                ],
                
                columns: [
                    { 
                        data: 'numero_documento',
                        title: 'Documento'
                    },
                    { 
                        data: 'nombre_completo',
                        title: 'Nombre Completo'
                    },
                    { 
                        data: 'cargo',
                        title: 'Cargo'
                    },
                    { 
                        data: 'sorteo_descripcion',
                        title: 'Sorteo'
                    },
                    { 
                        data: 'cantidad_elecciones',
                        title: 'Elecciones'
                    },
                    { 
                        data: 'elecciones_usadas',
                        title: 'Usadas'
                    },
                    { 
                        data: 'numero_balota',
                        title: 'Número Balota',
                        render: function(data) {
                            return data ? data : '<span class="text-muted">Sin elegir</span>';
                        }
                    },
                    { 
                        data: 'estado',
                        title: 'Estado',
                        render: function(data, type, row) {
                            // Mapeo de estados numéricos a configuración visual
                            const estadoConfig = {
                                1: { clase: 'bg-success', texto: 'Activo', icono: 'fas fa-check-circle' },
                                0: { clase: 'bg-danger', texto: 'Inactivo', icono: 'fas fa-times-circle' },
                                2: { clase: 'bg-warning', texto: 'Pendiente', icono: 'fas fa-clock' },
                                3: { clase: 'bg-info', texto: 'Pausado', icono: 'fas fa-pause-circle' }
                            };
                            
                            // Obtener configuración del estado
                            const config = estadoConfig[data] || { 
                                clase: 'bg-secondary', 
                                texto: 'Desconocido', 
                                icono: 'fas fa-question-circle' 
                            };
                            
                            return `<span class="badge ${config.clase} text-white">
                                        <i class="${config.icono}" style="margin-right: 4px;"></i>
                                        ${config.texto}
                                    </span>`;
                        },
                        defaultContent: '<span class="badge bg-secondary text-white"><i class="fas fa-question-circle" style="margin-right: 4px;"></i>Desconocido</span>'
                    },
                    {
                        data: null,
                        title: 'Acciones',
                        orderable: false,
                        searchable: false,
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
                
                // ===== CONFIGURACIÓN DE IDIOMA =====
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json',
                    buttons: {
                        copy: 'Copiar',
                        csv: 'CSV',
                        excel: 'Excel',
                        pdf: 'PDF',
                        print: 'Imprimir'
                    }
                },
                
                // ===== CONFIGURACIONES BÁSICAS =====
                responsive: true,
                order: [[1, 'asc']],
                pageLength: 5, // Registros por página por defecto
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]], // Opciones del selector
                processing: true,
                searching: true, // Habilitar búsqueda
                paging: true, // Habilitar paginación
                info: true, // Mostrar información
                
                // ===== ESTILOS Y PERSONALIZACIÓN =====
                initComplete: function() {
                    // Personalizar botones después de inicialización
                    $('.dt-buttons').addClass('mb-3');
                    
                    // Mejorar el estilo del selector de longitud
                    $('.dataTables_length select').addClass('form-control form-control-sm d-inline-block w-auto');
                    
                    // Mejorar el estilo del campo de búsqueda
                    $('.dataTables_filter input').addClass('form-control form-control-sm');
                    
                    // Agregar clases Bootstrap a los elementos de paginación
                    $('.dataTables_paginate .paginate_button').addClass('page-link');
                    $('.dataTables_paginate .paginate_button.current').addClass('active');
                },
                
                // ===== CALLBACKS PARA MANTENER ESTILOS =====
                drawCallback: function() {
                    // Reaplica estilos después de cada redibujado
                    $('.dataTables_paginate .paginate_button').addClass('page-link');
                    $('.dataTables_paginate .paginate_button.current').addClass('active');
                }
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

    function handleRegistrarUsuario(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');

        setButtonLoading(submitBtn, true);

        $.ajax({
            url: 'api/registrar_usuario.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert('✅ Usuario Registrado exitosamente', 'success');
                    closeModal('registrarUsuarioModal');
                    document.getElementById('registrarUsuarioForm').reset();
                    
                    
                    if (usuariosTable) {
                        usuariosTable.ajax.reload();
                    }
                    loadDashboardData();
                } else {
                    
                    showAlert(response.message || 'Error al registrar Usuario', 'danger');
                }
            },
            error: function() {
                showAlert('Error de conexión al registrar Usuario js', 'danger');
            },
            complete: function() {
                setButtonLoading(submitBtn, false);
            }
        });
    }

    // function loadSorteosForSelect() {
    //     $.ajax({
    //         url: 'api/sorteos_activos.php',
    //         method: 'GET',
    //         dataType: 'json',
    //         success: function(data) {
    //             const select = $('#sorteo_select');
    //             select.empty().append('<option value="">Seleccione un sorteo</option>');
                
    //             if (data.success && data.sorteos) {
    //                 data.sorteos.forEach(sorteo => {
    //                     select.append(`<option value="${sorteo.id}">${sorteo.descripcion}</option>`);
    //                 });
    //             }
    //         }
    //     });
    // }

    // Función para cargar sorteos 
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
                    
                    // Actualizar Select2 después de cargar las opciones
                    select.trigger('change');
                }
            });
        }

        // Función para cargar sorteos (tu función original)
        function loadEmpleadoForSelect() {
            $.ajax({
                url: 'api/empleados_activos.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    const select = $('#numero_documento_select');
                    select.empty().append('<option value="">Seleccione un empleado</option>');
                    
                    if (data.success && data.numero_documento_selects) {
                        data.numero_documento_selects.forEach(numero_documento_select => {
                            select.append(`<option value="${numero_documento_select.numero_documento}">${numero_documento_select.nombre_completo}</option>`);
                        });
                    }
                    
                    // Actualizar Select2 después de cargar las opciones
                    select.trigger('change');
                }
            });
        }

        function loadModeradorForSelect() {
            $.ajax({
                url: 'api/empleado_select_moderador.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    const select = $('#id_empleado_select');
                    select.empty().append('<option value="">Seleccione un empleado</option>');
                    
                    if (data.success && data.id_empleado_select) {
                        data.id_empleado_select.forEach(id_empleado_select => {
                            select.append(`<option value="${id_empleado_select.id}">${id_empleado_select.nombre_completo}</option>`);
                        });
                    }
                    
                    // Actualizar Select2 después de cargar las opciones
                    select.trigger('change');
                }
            });
        }

    

    // Funciones para secciones no implementadas
    function loadReportesData() {
        console.log('Cargando reportes...');
    }

    function loadUsuariosData() {
         //console.log("🚀 === INICIANDO loadUsuariosData ===");
    
    // Verificar que el elemento tabla existe
    const tableElement = $('#usuariosTable');
    //console.log("📋 Elemento tabla encontrado:", tableElement.length > 0);
    //console.log("📋 ID de la tabla:", tableElement.attr('id'));
    
    if (tableElement.length === 0) {
        console.error("❌ ERROR: No se encontró la tabla #usuariosTable");
        showAlert('Error: Tabla de usuarios no encontrada en el DOM', 'danger');
        return;
    }
    
    // ✅ CORRECCIÓN: Verificar si DataTable ya existe antes de destruir
    if (usuariosTable && typeof usuariosTable.destroy === 'function') {
        console.log("🗑️ Destruyendo DataTable existente...");
        try {
            usuariosTable.destroy();
        } catch (e) {
            console.warn("⚠️ Warning al destruir DataTable:", e);
        }
        usuariosTable = null;
    } else if ($.fn.DataTable.isDataTable('#usuariosTable')) {
        // Si existe como DataTable pero la variable no está disponible
        console.log("🗑️ Destruyendo DataTable detectado...");
        $('#usuariosTable').DataTable().destroy();
    }

    //console.log("⚙️ Inicializando DataTable...");
    
    try {
        usuariosTable = $('#usuariosTable').DataTable({
            ajax: {
                url: 'api/usuarios.php',
                type: 'GET',
                // beforeSend: function() {
                //     console.log("📡 Enviando petición a api/usuarios.php");
                // },
                dataSrc: function(response) {
                    //console.log("📥 Respuesta recibida:", response);
                    
                    if (response.success) {
                        // console.log("✅ Respuesta exitosa. Datos:", response.data);
                        // console.log("📊 Número de usuarios:", response.data.length);
                        return response.data;
                    } else {
                        console.error("❌ Error del servidor:", response.message);
                        showAlert('Error al cargar usuarios: ' + response.message, 'danger');
                        return [];
                    }
                },
                error: function(xhr, error, code) {
                    console.error('💥 Error AJAX:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error,
                        code: code
                    });
                    
                    let errorMessage = 'Error de conexión al cargar usuarios';
                    
                    if (xhr.status === 404) {
                        errorMessage = 'Archivo api/usuarios.php no encontrado';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Error interno del servidor al cargar usuarios';
                    } else if (xhr.status === 403) {
                        errorMessage = 'Sin permisos para acceder a usuarios';
                    }
                    
                    showAlert(errorMessage, 'danger');
                }
            },
            columns: [
                { 
                    data: 'numero_documento',
                    title: 'Usuario',
                    defaultContent: 'N/A'
                },
                { 
                    data: 'nombre_completo',
                    title: 'Nombre Completo',
                    defaultContent: 'N/A'
                },
                { 
                    data: 'cargo',
                    title: 'Cargo',
                    defaultContent: 'No especificado'
                },
                { 
                    data: 'rol_nombre',
                    title: 'Rol',
                    render: function(data, type, row) {
                        if (!data) return 'Sin rol';
                        const badgeClass = row.nivel_permiso == 1 ? 'success' : 'info';
                        return `<span class="badge badge-${badgeClass}">${data}</span>`;
                    },
                    defaultContent: 'Sin rol'
                },
                { 
                    // data: 'estado',
                    // title: 'Estado',
                    // render: function(data, type, row) {
                    //     // Como ahora devuelves texto ('Activo', 'Inactivo'), comparar con string
                    //     if (data === 'Activo') {
                    //         return '<span class="badge badge-success">Activo</span>';
                    //     } else if (data === 'Inactivo') {
                    //         return '<span class="badge badge-danger">Inactivo</span>';
                    //     } else {
                    //         return '<span class="badge badge-secondary">Desconocido</span>';
                    //     }
                    // },
                    // defaultContent: '<span class="badge badge-secondary">Desconocido</span>'

                    data: 'estado',
                    title: 'Estado',
                    render: function(data, type, row) {
                        // Mapeo de estados numéricos a configuración visual
                        const estadoConfig = {
                            1: { clase: 'bg-success', texto: 'Activo', icono: 'fas fa-check-circle' },
                            0: { clase: 'bg-danger', texto: 'Inactivo', icono: 'fas fa-times-circle' },
                            2: { clase: 'bg-warning', texto: 'Pendiente', icono: 'fas fa-clock' },
                            3: { clase: 'bg-info', texto: 'Pausado', icono: 'fas fa-pause-circle' }
                        };
                        
                        // Obtener configuración del estado
                        const config = estadoConfig[data] || { 
                            clase: 'bg-secondary', 
                            texto: 'Desconocido', 
                            icono: 'fas fa-question-circle' 
                        };
                        
                        return `<span class="badge ${config.clase} text-white">
                                    <i class="${config.icono}" style="margin-right: 4px;"></i>
                                    ${config.texto}
                                </span>`;
                    },
                    defaultContent: '<span class="badge bg-secondary text-white"><i class="fas fa-question-circle" style="margin-right: 4px;"></i>Desconocido</span>'
                },
                                {
                //     data: null,
                //     title: 'Acciones',
                //     orderable: false,
                //     searchable: false,
                //     render: function(data, type, row) {
                //         return `
                //             <div class="btn-group">
                //                 <button class="btn btn-sm btn-warning" onclick="editUsuario(${row.id})" title="Editar Usuario">
                //                     <i class="fas fa-edit"></i>
                //                 </button>
                //                 <button class="btn btn-sm btn-info" onclick="resetPasswordUsuario(${row.id})" title="Resetear Contraseña">
                //                     <i class="fas fa-key"></i>
                //                 </button>
                //                 ${row.estado == 'Activo' ? `
                //                 <button class="btn btn-sm btn-danger" onclick="inactivarUsuario(${row.id})" title="Inactivar Usuario">
                //                     <i class="fas fa-ban"></i>
                //                 </button>   
                //                 ` : `
                //                 <button class="btn btn-sm btn-success" onclick="activarUsuario(${row.id})" title="Activar Usuario">
                //                     <i class="fas fa-check"></i>
                //                 </button>
                //                 `}
                //             </div>
                //         `;
                //     }
                // }
                data: null,
                title: 'Acciones',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    // Determinar estado (funciona con números o texto)
                    const esActivo = row.estado === 1 || row.estado === '1' || row.estado === 'Activo';
                    
                    return `
                        <div class="btn-group">
                            <button class="btn btn-sm btn-warning" onclick="editUsuario(${row.id})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-info" onclick="resetPasswordUsuario(${row.id})" title="Reset Password">
                                <i class="fas fa-key"></i>
                            </button>
                            ${esActivo ? `
                            <button class="btn btn-sm btn-danger" onclick="inactivarUsuarioNuevo(${row.id})" title="Inactivar">
                                <i class="fas fa-ban"></i>
                                <span class="d-none d-sm-inline"> Inactivar</span>
                            </button>
                            ` : `
                            <button class="btn btn-sm btn-success" onclick="activarUsuarioNuevo(${row.id})" title="Activar">
                                <i class="fas fa-check"></i>
                                <span class="d-none d-sm-inline"> Activar</span>
                            </button>
                            `}
                        </div>
                    `;
                }
            }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json',
                loadingRecords: "Cargando usuarios...",
                processing: "Procesando usuarios...",
                emptyTable: "No hay usuarios registrados",
                zeroRecords: "No se encontraron usuarios"
            },
            responsive: true,
            order: [[0, 'asc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
            processing: true,
            searching: true,
            paging: true,
            info: true,
            
            // Callbacks para debug
            initComplete: function(settings, json) {
                //console.log("🎉 DataTable inicializado correctamente!");
                //console.log("📊 Registros cargados:", this.api().rows().count());
            },
            
            drawCallback: function(settings) {
                //console.log("🎨 DataTable redibujado");
                //console.log("📊 Registros mostrados:", this.api().rows().count());
            }
        });
        
        //console.log("✅ DataTable configurado exitosamente");
        
    } catch (error) {
        console.error("💥 Error inicializando DataTable:", error);
        showAlert('Error inicializando tabla de usuarios: ' + error.message, 'danger');
    }

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
                        // Mapeo completo de estados con colores e iconos
                        const estadoConfig = {
                            'Activo': { 
                                clase: 'success', 
                                icono: 'fas fa-check-circle' 
                            },
                            'Finalizado': { 
                                clase: 'info', 
                                icono: 'fas fa-flag-checkered' 
                            },
                            'Inactivo': { 
                                clase: 'danger', 
                                icono: 'fas fa-times-circle' 
                            },
                            'Pendiente': { 
                                clase: 'warning', 
                                icono: 'fas fa-clock' 
                            },
                            'Pausado': { 
                                clase: 'secondary', 
                                icono: 'fas fa-pause-circle' 
                            }
                        };
                        
                        // Obtener configuración del estado
                        const config = estadoConfig[data] || { 
                            clase: 'secondary', 
                            icono: 'fas fa-question-circle' 
                        };
                        
                        // Crear el badge con icono y texto
                        return `<span class="badge bg-${config.clase} text-white">
                                    <i class="${config.icono}" style="margin-right: 4px;"></i>
                                    ${data || 'Desconocido'}
                                </span>`;
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
                                <button class="btn btn-sm btn-close-modern" onclick="closeSorteo(${row.id})" title="Cerrar Sorteo">
                                    <i class="fas fa-times-circle me-1"></i>
                                    Cerrar
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

        loadEmpleadoForSelect();

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

    // // Función para mostrar modal de vista de sorteo
    // function openViewSorteoModal(sorteo, participantes, estadisticas) {
    //     $('#view_sorteo_descripcion').text(sorteo.descripcion);
    //     $('#view_sorteo_fecha_inicio').text(formatDateCorrect(sorteo.fecha_inicio_sorteo));
    //     $('#view_sorteo_fecha_cierre').text(formatDateCorrect(sorteo.fecha_cierre_sorteo));
    //     $('#view_sorteo_estado').html(`<span class="badge badge-${sorteo.estado == 1 ? 'success' : 'danger'}">${sorteo.estado == 1 ? 'Activo' : 'Inactivo'}</span>`);
        
    //     $('#view_total_participantes').text(estadisticas.total_participantes || 0);
    //     $('#view_numeros_elegidos').text(estadisticas.numeros_elegidos || 0);
    //     $('#view_numeros_disponibles').text(estadisticas.numeros_disponibles || 0);
        
    //     let participantesHtml = '';
    //     if (participantes && participantes.length > 0) {
    //         participantesHtml = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Documento</th><th>Nombre</th><th># Balota</th><th>Elecciones</th><th>Usadas</th></tr></thead><tbody>';
    //         participantes.forEach(p => {
    //             participantesHtml += `
    //                 <tr>
    //                     <td>${p.numero_documento}</td>
    //                     <td>${p.nombre_completo}</td>
    //                     <td>${p.numero_balota ? p.numero_balota : '<span class="text-muted">Sin elegir</span>'}</td>
    //                     <td>${p.cantidad_elecciones}</td>
    //                     <td>${p.elecciones_usadas}</td>
    //                 </tr>
    //             `;
    //         });
    //         participantesHtml += '</tbody></table></div>';
    //     } else {
    //         participantesHtml = '<p class="text-muted">No hay participantes inscritos</p>';
    //     }
    //     $('#view_participantes_list').html(participantesHtml);
        
    //     openModal('viewSorteoModal');
    // }

    // Variable global para controlar la instancia del DataTable
        let participantesDataTable = null;

        // Función para mostrar modal de vista de sorteo con DataTable
        function openViewSorteoModal(sorteo, participantes, estadisticas) {
            // Llenar datos del sorteo
            $('#view_sorteo_descripcion').text(sorteo.descripcion);
            $('#view_sorteo_fecha_inicio').text(formatDateCorrect(sorteo.fecha_inicio_sorteo));
            $('#view_sorteo_fecha_cierre').text(formatDateCorrect(sorteo.fecha_cierre_sorteo));
            $('#view_sorteo_estado').html(`<span class="badge badge-${sorteo.estado == 1 ? 'success' : 'danger'}">${sorteo.estado == 1 ? 'Activo' : 'Inactivo'}</span>`);
            
            // Llenar estadísticas
            $('#view_total_participantes').text(estadisticas.total_participantes || 0);
            $('#view_numeros_elegidos').text(estadisticas.numeros_elegidos || 0);
            $('#view_numeros_disponibles').text(estadisticas.numeros_disponibles || 0);
            
            // Configurar DataTable para participantes
            setupParticipantesDataTable(participantes, sorteo);
            
            // Abrir modal
            openModal('viewSorteoModal');
        }

        // Función para configurar el DataTable
        function setupParticipantesDataTable(participantes, sorteo) {
            // Destruir DataTable existente si existe
            if (participantesDataTable) {
                participantesDataTable.destroy();
                participantesDataTable = null;
            }
            
            // Crear el HTML base de la tabla si no existe
            let tableHtml = `
                <div class="table-responsive">
                    <table id="participantesTable" class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th># Balota</th>
                                <th>Elecciones</th>
                                <th>Usadas</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            `;
            
            // Verificar si hay participantes
            if (participantes && participantes.length > 0) {
                $('#view_participantes_list').html(tableHtml);
                
                // Inicializar DataTable
                participantesDataTable = $('#participantesTable').DataTable({
                    data: participantes,
                    columns: [
                        { data: 'numero_documento' },
                        { data: 'nombre_completo' },
                        { 
                            data: 'numero_balota',
                            render: function(data, type, row) {
                                return data ? data : '<span class="text-muted">Sin elegir</span>';
                            }
                        },
                        { data: 'cantidad_elecciones' },
                        { data: 'elecciones_usadas' }
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                    },
                    pageLength: 5,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
                    responsive: true,
                    order: [[1, 'asc']], // Ordenar por nombre
                    search: {
                        placeholder: "Buscar participantes..."
                    },
                    
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'excel',
                            text: 'Exportar Excel',
                            filename: function() {
                                const descripcion = (sorteo && sorteo.descripcion) ? 
                                    sorteo.descripcion.replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '_') : 
                                    'sorteo';
                                const fecha = new Date().toISOString().slice(0,10);
                                return `${descripcion}_${fecha}`;
                            },
                            className: 'btn btn-success btn-sm'
                        },
                        {
                            extend: 'pdf',
                            text: 'Exportar PDF',
                            filename: function() {
                                const descripcion = (sorteo && sorteo.descripcion) ? 
                                    sorteo.descripcion.replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '_') : 
                                    'sorteo';
                                const fecha = new Date().toISOString().slice(0,10);
                                return `${descripcion}_${fecha}`;
                            },
                            className: 'btn btn-danger btn-sm'
                        }
                    ]
                });
            } else {
                $('#view_participantes_list').html('<p class="text-muted text-center">No hay participantes inscritos</p>');
            }
        }

        // Función para limpiar el DataTable cuando se cierre el modal
        function cleanupDataTable() {
            if (participantesDataTable) {
                participantesDataTable.destroy();
                participantesDataTable = null;
            }
        }

        // Evento para limpiar cuando se cierre el modal
        $(document).ready(function() {
            $('#viewSorteoModal').on('hidden.bs.modal', function () {
                cleanupDataTable();
            });
        });

        // Versión alternativa más simple sin botones de exportación
        function setupParticipantesDataTableSimple(participantes) {
            // Destruir DataTable existente si existe
            if (participantesDataTable) {
                participantesDataTable.destroy();
                participantesDataTable = null;
            }
            
            let tableHtml = `
                <div class="table-responsive">
                    <table id="participantesTable" class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th># Balota</th>
                                <th>Elecciones</th>
                                <th>Usadas</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            `;
            
            if (participantes && participantes.length > 0) {
                $('#view_participantes_list').html(tableHtml);
                
                participantesDataTable = $('#participantesTable').DataTable({
                    data: participantes,
                    columns: [
                        { data: 'numero_documento' },
                        { data: 'nombre_completo' },
                        { 
                            data: 'numero_balota',
                            render: function(data, type, row) {
                                return data ? data : '<span class="text-muted">Sin elegir</span>';
                            }
                        },
                        { data: 'cantidad_elecciones' },
                        { data: 'elecciones_usadas' }
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                    },
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
                    responsive: true,
                    order: [[1, 'asc']]
                });
            } else {
                $('#view_participantes_list').html('<p class="text-muted text-center">No hay participantes inscritos</p>');
            }
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
            loadEmpleadoForSelect();
            
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

        if (modalId === 'registrarUsuarioModal') {
            document.getElementById('registrarUsuarioForm').reset();
        }
    };

    window.closeAllModals = function() {
        $('.modal').removeClass('show').css('display', 'none');
        
        // Limpiar todos los formularios
        $('#createSorteoForm')[0].reset();
        $('#inscribirEmpleadoForm')[0].reset();
        $('#registrarUsuarioForm')[0].reset();
        $('.char-counter').remove();
    };

    window.showSection = showSection;

    window.logout = function() {
        if (confirm('¿Está seguro de que desea cerrar sesión?')) {
            window.location.href = 'logout.php';
        }
    };

    // window.showAlert = function(message, type = 'info') {
    //     const alertContainer = $('#alertContainer');
    //     const alertId = 'alert-' + Date.now();
        
    //     const alert = $(`
    //         <div id="${alertId}" class="alert alert-${type}" style="display: none;">
    //             <div class="d-flex justify-content-between align-items-center">
    //                 <span>${message}</span>
    //                 <button type="button" class="btn-close" onclick="$('#${alertId}').fadeOut(300, function(){ $(this).remove(); })">
    //                     <i class="fas fa-times"></i>
    //                 </button>
    //             </div>
    //         </div>
    //     `);

    //     alertContainer.append(alert);
    //     alert.fadeIn(300);

    //     // Auto-remover después de 5 segundos
    //     setTimeout(() => {
    //         if ($(`#${alertId}`).length) {
    //             $(`#${alertId}`).fadeOut(300, function() { $(this).remove(); });
    //         }
    //     }, 5000);
    // };
    window.showAlert = function(message, type = 'info', duration = 5000) {
        // Crear contenedor si no existe
        let alertContainer = $('#alertContainer');
        if (alertContainer.length === 0) {
            $('body').append('<div id="alertContainer" class="alert-container"></div>');
            alertContainer = $('#alertContainer');
        }

        // Iconos para cada tipo de alerta
        const alertIcons = {
            'success': '<i class="fas fa-check-circle alert-icon"></i>',
            'danger': '<i class="fas fa-exclamation-circle alert-icon"></i>',
            'warning': '<i class="fas fa-exclamation-triangle alert-icon"></i>',
            'info': '<i class="fas fa-info-circle alert-icon"></i>'
        };

        const alertId = 'alert-' + Date.now();
        const icon = alertIcons[type] || alertIcons['info'];

        const alert = $(`
            <div id="${alertId}" class="alert alert-${type} style="display: none;">
                <div class="alert-content">
                    ${icon}
                    <div class="alert-message">${message}</div>
                    <button type="button" class="alert-close" onclick="closeAlert('${alertId}')" title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="alert-progress"></div>
            </div>
        `);

        alertContainer.append(alert);
        
        // Animación de entrada
        alert.slideDown(400);

        // Auto-remover después de la duración especificada
        const timeout = setTimeout(() => {
            closeAlert(alertId);
        }, duration);

        // Guardar timeout para poder cancelarlo
        alert.data('timeout', timeout);
    };
    // Función para cerrar alertas
    window.closeAlert = function(alertId) {
        const alert = $(`#${alertId}`);
        if (alert.length === 0) return;

        // Cancelar timeout automático
        const timeout = alert.data('timeout');
        if (timeout) {
            clearTimeout(timeout);
        }

        // Animación de salida
        alert.slideUp(300, function() {
            alert.remove();
        });
    };

    // Función para limpiar todas las alertas
    window.clearAllAlerts = function() {
        $('.alert-improved').each(function() {
            const alertId = $(this).attr('id');
            if (alertId) {
                closeAlert(alertId);
            }
        });
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

    // Funciones placeholder para las acciones (las implementaremos después)
        window.editUsuario = function(id) {
            console.log('Editando usuario ID:', id);
    
            $.ajax({
                url: 'api/get_usuario.php',
                method: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    console.log('Respuesta get_usuario:', response);
                    
                    if (response.success) {
                        openEditUsuarioModal(response.usuario);
                    } else {
                        showAlert('Error al cargar datos del usuario: ' + response.message, 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX al cargar usuario:', {xhr, status, error});
                    showAlert('Error de conexión al cargar usuario', 'danger');
                }
            });

        };

        window.resetPasswordUsuario = function(id) {
            console.log('=== RESET PASSWORD USUARIO ===');
            console.log('ID:', id);
            
            // Obtener datos del usuario
            $.ajax({
                url: 'api/get_usuario.php',
                method: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        openResetPasswordModal(response.usuario);
                    } else {
                        showAlert('Error al cargar datos del usuario: ' + response.message, 'danger');
                    }
                },
                error: function(xhr) {
                    console.error('Error cargando usuario:', xhr.responseText);
                    showAlert('Error de conexión al cargar usuario', 'danger');
                }
            });
        };

        // window.inactivarUsuario = function(id) {
        //     showAlert('Función de inactivar usuario en desarrollo. ID: ' + id, 'info');
        // };

        // window.activarUsuario = function(id) {
        //     showAlert('Función de activar usuario en desarrollo. ID: ' + id, 'info');
        // };

        // ===== 1. FUNCIÓN PARA EDITAR USUARIO =====
        window.editUsuario = function(id) {
            $.ajax({
                url: 'api/get_usuario.php',
                method: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        openEditUsuarioModal(response.usuario);
                    } else {
                        showAlert('Error al cargar datos del usuario: ' + response.message, 'danger');
                    }
                },
                error: function(xhr) {
                    console.error('Error cargando usuario:', xhr.responseText);
                    showAlert('Error de conexión al cargar usuario', 'danger');
                }
            });
        };

        
        // Función para abrir modal de reset password
        function openResetPasswordModal(usuario) {
            console.log('Abriendo modal reset password para:', usuario);
            
            // Verificar que el modal existe
            if ($('#resetPasswordModal').length === 0) {
                console.error('Modal resetPasswordModal no encontrado');
                showAlert('Error: Modal de reset password no encontrado', 'danger');
                return;
            }
            
            $('#reset_usuario_id').val(usuario.id);
            $('#reset_usuario_nombre').text(usuario.nombre_completo);
            $('#reset_usuario_documento').text(usuario.numero_documento);
            $('#reset_usuario_rol').text(usuario.rol_nombre);
            $('#reset_nueva_clave').val('');
            
            openModal('resetPasswordModal');
        }
        // ===== 2. FUNCIÓN PARA ABRIR MODAL DE EDICIÓN =====
        function openEditUsuarioModal(usuario) {
            // Llenar campos del modal
            $('#edit_usuario_id').val(usuario.id);
            $('#edit_usuario_nombre').text(usuario.nombre_completo);
            $('#edit_usuario_documento_badge').text(usuario.numero_documento);
            $('#edit_usuario_cargo').text(usuario.cargo || 'No especificado');
            $('#edit_nivel_permiso').val(usuario.nivel_permiso);
            $('#edit_usuario_estado').val(usuario.estado);
            
            // Limpiar campo de contraseña
            $('#edit_nueva_clave').val('');
            
            // Abrir modal
            openModal('editUsuarioModal');
        }

        // ===== 3. MANEJAR FORMULARIO DE EDICIÓN =====
        function handleEditUsuario(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = $(this).find('button[type="submit"]');
            
            // Validaciones básicas
            const nivelPermiso = formData.get('nivel_permiso');
            const estado = formData.get('estado');
            const nuevaClave = formData.get('nueva_clave');
            
            if (!nivelPermiso || !estado) {
                showAlert('Nivel de permiso y estado son obligatorios', 'warning');
                return;
            }
            
            if (nuevaClave && nuevaClave.length < 6) {
                showAlert('La contraseña debe tener al menos 6 caracteres', 'warning');
                return;
            }
            
            setButtonLoading(submitBtn, true);

            $.ajax({
                url: 'api/actualizar_usuario.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('✅ Usuario actualizado exitosamente', 'success');
                        closeModal('editUsuarioModal');
                        
                        // Recargar tabla de usuarios
                        if (usuariosTable) {
                            usuariosTable.ajax.reload();
                        }
                    } else {
                        showAlert(response.message || 'Error al actualizar usuario', 'danger');
                    }
                },
                error: function(xhr) {
                    console.error('Error actualizando usuario:', xhr.responseText);
                    showAlert('Error de conexión al actualizar usuario', 'danger');
                },
                complete: function() {
                    setButtonLoading(submitBtn, false);
                }
            });
        }

        // ===== 4. FUNCIÓN PARA RESETEAR CONTRASEÑA =====
        window.resetPasswordUsuario = function(id) {
            // Primero obtener datos del usuario
            $.ajax({
                url: 'api/get_usuario.php',
                method: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        openResetPasswordModal(response.usuario);
                    } else {
                        showAlert('Error al cargar datos del usuario', 'danger');
                    }
                },
                error: function() {
                    showAlert('Error de conexión', 'danger');
                }
            });
        };

        // ===== 5. FUNCIÓN PARA ABRIR MODAL DE RESET PASSWORD =====
        function openResetPasswordModal(usuario) {
            $('#reset_usuario_id').val(usuario.id);
            $('#reset_usuario_nombre').text(usuario.nombre_completo);
            $('#reset_usuario_documento').text(usuario.numero_documento);
            $('#reset_usuario_rol').text(usuario.rol_nombre);
            $('#reset_nueva_clave').val('');
            
            openModal('resetPasswordModal');
        }

        // ===== 6. MANEJAR FORMULARIO DE RESET PASSWORD =====
        function handleResetPassword(e) {
            e.preventDefault();
    
            const formData = new FormData(this);
            const submitBtn = $(this).find('button[type="submit"]');
            const nuevaPassword = formData.get('nueva_password');
            
            // Validaciones
            if (!nuevaPassword || nuevaPassword.length < 6) {
                showAlert('La contraseña debe tener al menos 6 caracteres', 'warning');
                return;
            }
            
            if (nuevaPassword.length > 50) {
                showAlert('La contraseña no puede exceder 50 caracteres', 'warning');
                return;
            }
            
            // Confirmación
            if (!confirm('¿Está seguro de que desea resetear la contraseña de este usuario?')) {
                return;
            }
            
            setButtonLoading(submitBtn, true);
            
            $.ajax({
                url: 'api/reset_password.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    console.log('Respuesta reset password:', response);
                    
                    if (response.success) {
                        showAlert('✅ Contraseña actualizada correctamente', 'success');
                        closeModal('resetPasswordModal');
                        
                        // Mostrar la contraseña al admin (opcional)
                        //const passwordMostrar = $('#nueva_password').val();
                        //showAlert(`Nueva contraseña: ${passwordMostrar}`, 'info');
                        
                    } else {
                        showAlert(response.message || 'Error al actualizar contraseña', 'danger');
                    }
                },
                error: function(xhr) {
                    console.error('Error reset password:', xhr.responseText);
                    showAlert('Error de conexión al actualizar contraseña', 'danger');
                },
                complete: function() {
                    setButtonLoading(submitBtn, false);
                }
            });
        }

        // Toggle visibilidad de contraseña
        window.togglePasswordVisibility = function(inputId) {
            const input = document.getElementById(inputId);
            const button = input.parentElement.querySelector('.password-toggle-btn i');
            
            if (input.type === 'password') {
                input.type = 'text';
                button.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                button.className = 'fas fa-eye';
            }
        };

        // Generar contraseña automática
        window.generarPasswordAutomatica = function() {
            console.log('=== GENERANDO CONTRASEÑA AUTOMÁTICA ===');
            
            const caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%&*';
            let password = '';
            
            for (let i = 0; i < 8; i++) {
                password += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
            }
            
            // Verificar que el campo existe
            const campo = $('#nueva_password');
            if (campo.length === 0) {
                console.error('Campo #nueva_password no encontrado');
                alert('Error: Campo de contraseña no encontrado');
                return;
            }
            
            // Asignar contraseña
            campo.val(password);
            campo.attr('type', 'text'); // Mostrar temporalmente
            
            // Ocultar después de 3 segundos
            setTimeout(() => {
                campo.attr('type', 'password');
            }, 3000);
            
            // Mostrar alerta
            if (typeof showAlert === 'function') {
                showAlert('Contraseña generada automáticamente (visible por 3 segundos)', 'info');
            } else {
                alert('Contraseña generada: ' + password);
            }
        };


        // ===== 7. FUNCIÓN PARA ACTIVAR/INACTIVAR USUARIO =====
        window.toggleUsuarioEstado = function(id, estadoActual) {
            const nuevoEstado = estadoActual == 1 ? 0 : 1;
            const accion = nuevoEstado == 1 ? 'activar' : 'inactivar';
            
            if (confirm(`¿Está seguro de que desea ${accion} este usuario?`)) {
                $.ajax({
                    url: 'api/actualizar_usuario.php',
                    method: 'POST',
                    data: {
                        id: id,
                        estado: nuevoEstado
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const mensaje = nuevoEstado == 1 ? 'Usuario activado' : 'Usuario inactivado';
                            showAlert(`✅ ${mensaje} correctamente`, 'success');
                            
                            if (usuariosTable) {
                                usuariosTable.ajax.reload();
                            }
                        } else {
                            showAlert(response.message || `Error al ${accion} usuario`, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error de conexión', 'danger');
                    }
                });
            }
        };

        // Mantener compatibilidad con funciones separadas
        // window.inactivarUsuario = function(id) {
        //     console.log('Inactivando usuario ID:', id);
    
        //     if (confirm('¿Está seguro de que desea inactivar este usuario?')) {
        //         $.ajax({
        //             url: 'api/actualizar_usuario.php',
        //             method: 'POST',
        //             data: {
        //                 id: id,
        //                 estado: 0
        //             },
        //             dataType: 'json',
        //             success: function(response) {
        //                 if (response.success) {
        //                     showAlert('✅ Usuario inactivado correctamente', 'success');
        //                     usuariosTable.ajax.reload();
        //                 } else {
        //                     showAlert(response.message || 'Error al inactivar usuario', 'danger');
        //                 }
        //             },
        //             error: function() {
        //                 showAlert('Error de conexión', 'danger');
        //             }
        //         });
        //     }
        // };
        // window.inactivarUsuario = function(id) {
        //     console.log('=== INACTIVAR USUARIO ===');
        //     console.log('ID del usuario:', id);
        //     console.log('Tipo de ID:', typeof id);
            
        //     if (confirm('¿Está seguro de que desea inactivar este usuario?')) {
        //         console.log('Usuario confirmó inactivación');
                
        //         const datosEnviar = {
        //             id: id,
        //             estado: 0
        //         };
                
        //         console.log('Datos a enviar:', datosEnviar);
                
        //         $.ajax({
        //             url: 'api/actualizar_usuario.php',
        //             method: 'POST',
        //             data: datosEnviar,
        //             dataType: 'json',
        //             beforeSend: function() {
        //                 console.log('Enviando petición de inactivación...');
        //             },
        //             success: function(response) {
        //                 console.log('Respuesta recibida (inactivar):', response);
                        
        //                 if (response.success) {
        //                     showAlert('✅ Usuario inactivado correctamente', 'success');
        //                     usuariosTable.ajax.reload();
        //                 } else {
        //                     console.error('Error del servidor:', response.message);
        //                     showAlert(response.message || 'Error al inactivar usuario', 'danger');
        //                 }
        //             },
        //             error: function(xhr, status, error) {
        //                 console.error('Error AJAX (inactivar):', {
        //                     status: xhr.status,
        //                     statusText: xhr.statusText,
        //                     responseText: xhr.responseText,
        //                     error: error
        //                 });
        //                 showAlert('Error de conexión', 'danger');
        //             }
        //         });
        //     } else {
        //         console.log('Usuario canceló inactivación');
        //     }
        // };

        // window.activarUsuario = function(id) {
        //     console.log('Activando usuario ID:', id);
    
        //     if (confirm('¿Está seguro de que desea activar este usuario?')) {
        //         $.ajax({
        //             url: 'api/actualizar_usuario.php',
        //             method: 'POST',
        //             data: {
        //                 id: id,
        //                 estado: 1
        //             },
        //             dataType: 'json',
        //             success: function(response) {
        //                 if (response.success) {
        //                     showAlert('✅ Usuario activado correctamente', 'success');
        //                     usuariosTable.ajax.reload();
        //                 } else {
        //                     showAlert(response.message || 'Error al activar usuario', 'danger');
        //                 }
        //             },
        //             error: function() {
        //                 showAlert('Error de conexión', 'danger');
        //             }
        //         });
        //     }
        // };
 
        // ===== 8. FUNCIÓN PARA MOSTRAR/OCULTAR CONTRASEÑA =====
        window.togglePassword = function(inputId) {
            const input = document.getElementById(inputId);
            const button = input.parentElement.querySelector('.btn-toggle-password i');
            
            if (input.type === 'password') {
                input.type = 'text';
                button.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                button.className = 'fas fa-eye';
            }
        };

        // Función para activar usuario
        window.activarUsuarioNuevo = function(id) {
            console.log('=== ACTIVAR USUARIO NUEVO ===');
            console.log('ID:', id);
            
            if (!confirm('¿Está seguro de que desea activar este usuario?')) {
                return;
            }
            
            cambiarEstadoUsuario(id, 1, 'activar');
        };

        // Función para inactivar usuario  
        window.inactivarUsuarioNuevo = function(id) {
            console.log('=== INACTIVAR USUARIO NUEVO ===');
            console.log('ID:', id);
            
            if (!confirm('¿Está seguro de que desea inactivar este usuario?')) {
                return;
            }
            
            cambiarEstadoUsuario(id, 0, 'inactivar');
        };

        // Función central para cambiar estado
        function cambiarEstadoUsuario(id, estado, accion) {
        console.log(`Cambiando estado usuario ${id} a ${estado} (${accion})`);
        
        // Preparar datos
        const datos = {
            id: parseInt(id),
            estado: parseInt(estado)
        };
        
        console.log('Datos a enviar:', datos);
        
        // Petición AJAX
        $.ajax({
            url: 'api/cambiar_estado_usuario.php',  // ← CAMBIAR ESTA URL
            method: 'POST',
            data: datos,
            dataType: 'json',
            beforeSend: function() {
                console.log(`Enviando petición para ${accion}...`);
            },
            success: function(response) {
                console.log(`Respuesta ${accion}:`, response);
                
                if (response.success) {
                    const mensaje = estado === 1 ? 
                        '✅ Usuario activado correctamente' : 
                        '✅ Usuario inactivado correctamente';
                    
                    showAlert(mensaje, 'success');
                    
                    // Recargar tabla
                    if (usuariosTable) {
                        console.log('Recargando tabla de usuarios...');
                        usuariosTable.ajax.reload(function() {
                            console.log('Tabla recargada exitosamente');
                        });
                    }
                    
                } else {
                    console.error(`Error en ${accion}:`, response.message);
                    showAlert(response.message || `Error al ${accion} usuario`, 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error(`Error AJAX ${accion}:`, {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                
                showAlert(`Error de conexión al ${accion} usuario`, 'danger');
            },
            complete: function() {
                console.log(`Petición ${accion} completada`);
            }
        });
    }

        // ===== 9. ACTUALIZAR BOTONES EN EL DATATABLE =====
        // Reemplaza la función render de la columna "Acciones" en loadUsuariosData() con:

        // function renderAccionesUsuarios(data, type, row) {
        //     return `
        //         <div class="btn-group">
        //             <button class="btn btn-sm btn-warning" onclick="editUsuario(${row.id})" title="Editar Usuario">
        //                 <i class="fas fa-edit"></i>
        //             </button>
        //             <button class="btn btn-sm btn-info" onclick="resetPasswordUsuario(${row.id})" title="Resetear Contraseña">
        //                 <i class="fas fa-key"></i>
        //             </button>
        //             ${row.estado == 1 ? `
        //             <button class="btn btn-sm btn-danger" onclick="inactivarUsuario(${row.id})" title="Inactivar Usuario">
        //                 <i class="fas fa-ban"></i>
        //             </button>
        //             ` : `
        //             <button class="btn btn-sm btn-success" onclick="activarUsuario(${row.id})" title="Activar Usuario">
        //                 <i class="fas fa-check"></i>
        //             </button>
        //             `}
        //         </div>
        //     `;
        // }

    // Inicializar Select2 con configuración básica
            $('#sorteo_select').select2({
                placeholder: 'Seleccione un sorteo',
                allowClear: true,
                width: '100%'
            });
            $('#numero_documento_select').select2({
                placeholder: 'Seleccione un empleado',
                allowClear: true,
                width: '100%'
            });
            $('#id_empleado_select').select2({
                placeholder: 'Seleccione un empleado',
                allowClear: true,
                width: '100%'
            });


            // Cargar los sorteos
            loadSorteosForSelect();
            loadEmpleadoForSelect();
            loadModeradorForSelect();
});