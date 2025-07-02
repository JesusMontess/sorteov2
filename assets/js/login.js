// // assets/js/login.js
// document.addEventListener('DOMContentLoaded', function() {
//     const loginForm = document.getElementById('loginForm');
//     const alertContainer = document.getElementById('alertContainer');

//     // Verificar si hay mensajes de URL
//     const urlParams = new URLSearchParams(window.location.search);
//     if (urlParams.get('timeout')) {
//         showAlert('Su sesión ha expirado. Por favor, inicie sesión nuevamente.', 'warning');
//     }

//     if (urlParams.get('logout')) {
//         showAlert('Sesión cerrada correctamente.', 'success');
//     }

//     if (urlParams.get('error')) {
//         showAlert(decodeURIComponent(urlParams.get('error')), 'danger');
//     }

//     // Manejar envío del formulario
//     if (loginForm) {
//         loginForm.addEventListener('submit', function(e) {
//             e.preventDefault();
//             handleLogin();
//         });
//     }

//     function handleLogin() {
//         const formData = new FormData(loginForm);
//         const submitButton = loginForm.querySelector('button[type="submit"]');
        
//         // Validaciones básicas
//         const documento = formData.get('numero_documento');
//         const password = formData.get('password');

//         if (!documento || !password) {
//             showAlert('Por favor complete todos los campos', 'danger');
//             return;
//         }

//         if (documento.length < 6) {
//             showAlert('Número de documento inválido', 'danger');
//             return;
//         }

//         // Deshabilitar botón durante el proceso
//         setLoading(submitButton, true);

//         // Verificar si fetch está disponible
//         if (typeof fetch === 'undefined') {
//             // Fallback: envío tradicional del formulario
//             console.log('Fetch no disponible, usando envío tradicional');
//             setLoading(submitButton, false);
//             loginForm.submit();
//             return;
//         }

//         // Enviar petición AJAX
//         fetch('process_login.php', {
//             method: 'POST',
//             body: formData,
//             headers: {
//                 'X-Requested-With': 'XMLHttpRequest'
//             }
//         })
//         .then(response => {
//             if (!response.ok) {
//                 throw new Error('Error de red: ' + response.status);
//             }
//             return response.json();
//         })
//         .then(data => {
//             if (data.success) {
//                 showAlert('Ingreso exitoso. Redirigiendo...', 'success');
//                 setTimeout(() => {
//                     window.location.href = data.redirect || 'dashboard.php';
//                 }, 1000);
//             } else {
//                 showAlert(data.message || 'Error de autenticación', 'danger');
//                 setLoading(submitButton, false);
//             }
//         })
//         .catch(error => {
//             console.error('Error:', error);
//             // Fallback: envío tradicional si AJAX falla
//             showAlert('Intentando conexión alternativa...', 'info');
//             setTimeout(() => {
//                 loginForm.submit();
//             }, 1000);
//         });
//     }

//     function setLoading(button, loading) {
//         if (loading) {
//             button.disabled = true;
//             button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ingresando...';
//         } else {
//             button.disabled = false;
//             button.innerHTML = '<i class="fas fa-sign-in-alt"></i> Iniciar Sesión';
//         }
//     }

//     function showAlert(message, type) {
//         if (!alertContainer) return;
        
//         const alert = document.createElement('div');
//         alert.className = `alert alert-${type}`;
//         alert.style.margin = '10px 0';
//         alert.style.padding = '10px';
//         alert.style.borderRadius = '5px';
//         alert.style.border = '1px solid';
        
//         // Colores según el tipo
//         const colors = {
//             'success': { bg: '#d4edda', border: '#c3e6cb', color: '#155724' },
//             'danger': { bg: '#f8d7da', border: '#f5c6cb', color: '#721c24' },
//             'warning': { bg: '#fff3cd', border: '#ffeaa7', color: '#856404' },
//             'info': { bg: '#d1ecf1', border: '#bee5eb', color: '#0c5460' }
//         };
        
//         const typeColors = colors[type] || colors['info'];
//         alert.style.backgroundColor = typeColors.bg;
//         alert.style.borderColor = typeColors.border;
//         alert.style.color = typeColors.color;
        
//         alert.innerHTML = `
//             <div style="display: flex; align-items: center; justify-content: space-between;">
//                 <span>${message}</span>
//                 <button onclick="this.parentElement.parentElement.remove()" 
//                         style="background: none; border: none; font-size: 18px; cursor: pointer; color: inherit;">&times;</button>
//             </div>
//         `;

//         alertContainer.appendChild(alert);

//         // Auto-remover después de 5 segundos
//         setTimeout(() => {
//             if (alert.parentElement) {
//                 alert.remove();
//             }
//         }, 5000);
//     }

//     // Validación en tiempo real del número de documento
//     const documentoInput = document.getElementById('numero_documento');
//     if (documentoInput) {
//         documentoInput.addEventListener('input', function() {
//             // Solo permitir números
//             this.value = this.value.replace(/\D/g, '');
            
//             // Máximo 15 caracteres
//             if (this.value.length > 15) {
//                 this.value = this.value.substring(0, 15);
//             }
//         });
//     }

//     // Enter en cualquier campo envía el formulario
//     const inputs = loginForm ? loginForm.querySelectorAll('input') : [];
//     inputs.forEach(input => {
//         input.addEventListener('keypress', function(e) {
//             if (e.key === 'Enter') {
//                 e.preventDefault();
//                 handleLogin();
//             }
//         });
//     });

//     // Función global para mostrar alertas (para usar desde otros scripts)
//     window.showAlert = showAlert;
// });

// assets/js/login.js
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const alertContainer = document.getElementById('alertContainer');

    // Verificar si hay mensajes de URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('timeout')) {
        showAlert('Su sesión ha expirado. Por favor, inicie sesión nuevamente.', 'warning');
    }
    if (urlParams.get('logout')) {
        showAlert('Sesión cerrada correctamente.', 'success');
    }
    if (urlParams.get('error')) {
        showAlert(decodeURIComponent(urlParams.get('error')), 'danger');
    }

    // Manejar envío del formulario
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleLogin();
        });
    }

    function handleLogin() {
        const formData = new FormData(loginForm);
        const submitButton = loginForm.querySelector('button[type="submit"]');
        
        // Validaciones básicas
        const documento = formData.get('numero_documento');
        const password = formData.get('password');

        if (!documento || !password) {
            showAlert('Por favor complete todos los campos', 'danger');
            return;
        }

        if (documento.length < 6) {
            showAlert('Número de documento inválido', 'danger');
            return;
        }

        // Deshabilitar botón durante el proceso
        setLoading(submitButton, true);

        // Verificar si fetch está disponible
        if (typeof fetch === 'undefined') {
            console.log('Fetch no disponible, usando envío tradicional');
            setLoading(submitButton, false);
            loginForm.submit();
            return;
        }

        // Debug: mostrar que estamos intentando AJAX
        console.log('Intentando login AJAX...');

        // Enviar petición AJAX con mejor manejo de errores
        fetch('process_login.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Respuesta recibida:', response.status, response.statusText);
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
            }
            
            // Verificar si la respuesta es JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('La respuesta no es JSON válido. Content-Type: ' + contentType);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            
            if (data && data.success) {
                showAlert('✅ Ingreso exitoso. Redirigiendo...', 'success');
                setTimeout(() => {
                    window.location.href = data.redirect || 'dashboard.php';
                }, 800);
            } else {
                showAlert(data.message || 'Error de autenticación', 'danger');
                setLoading(submitButton, false);
            }
        })
        .catch(error => {
            console.error('Error AJAX:', error);
            
            // Determinar el tipo de error
            if (error.message.includes('JSON')) {
                showAlert('⚠️ Error de formato. Usando método alternativo...', 'warning');
            } else if (error.message.includes('HTTP')) {
                showAlert('⚠️ Error de servidor. Usando método alternativo...', 'warning');
            } else {
                showAlert('⚠️ Error de red. Usando método alternativo...', 'warning');
            }
            
            // Fallback: envío tradicional
            setTimeout(() => {
                setLoading(submitButton, false);
                loginForm.submit();
            }, 1000);
        });
    }

    function setLoading(button, loading) {
        if (loading) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ingresando...';
        } else {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-sign-in-alt"></i> Iniciar Sesión';
        }
    }

    function showAlert(message, type) {
        if (!alertContainer) return;
        
        // Remover alertas previas del mismo tipo
        const existingAlerts = alertContainer.querySelectorAll(`.alert-${type}`);
        existingAlerts.forEach(alert => alert.remove());
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.style.cssText = `
            margin: 10px 0;
            padding: 12px 16px;
            border-radius: 6px;
            border: 1px solid;
            font-size: 14px;
            line-height: 1.4;
            animation: slideIn 0.3s ease-out;
        `;
        
        // Colores según el tipo
        const colors = {
            'success': { bg: '#d4edda', border: '#c3e6cb', color: '#155724' },
            'danger': { bg: '#f8d7da', border: '#f5c6cb', color: '#721c24' },
            'warning': { bg: '#fff3cd', border: '#ffeaa7', color: '#856404' },
            'info': { bg: '#d1ecf1', border: '#bee5eb', color: '#0c5460' }
        };
        
        const typeColors = colors[type] || colors['info'];
        alert.style.backgroundColor = typeColors.bg;
        alert.style.borderColor = typeColors.border;
        alert.style.color = typeColors.color;
        
        alert.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" 
                        style="background: none; border: none; font-size: 18px; cursor: pointer; color: inherit; padding: 0 5px;">&times;</button>
            </div>
        `;

        alertContainer.appendChild(alert);

        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (alert.parentElement) {
                alert.remove();
            }
        }, 5000);
    }

    // Validación en tiempo real del número de documento
    const documentoInput = document.getElementById('numero_documento');
    if (documentoInput) {
        documentoInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length > 15) {
                this.value = this.value.substring(0, 15);
            }
        });
    }

    // Enter en cualquier campo envía el formulario
    const inputs = loginForm ? loginForm.querySelectorAll('input') : [];
    inputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleLogin();
            }
        });
    });

    // CSS para animación
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);

    // Función global para mostrar alertas
    window.showAlert = showAlert;
});