<?php
require_once 'verificar_sesion.php';
require_once 'verificar_permisos.php';

// Solo admins pueden crear usuarios
soloAdmin($usuario_rol);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #0064C8;
            --primary-dark: #004a96;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-custom {
            background: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-custom .navbar-brand {
            color: white;
            font-weight: 700;
            font-size: 1.4rem;
        }
        
        .form-container {
            max-width: 700px;
            margin: 50px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 40px;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header i {
            font-size: 60px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 100, 200, 0.25);
        }
        
        .btn-crear {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            color: white;
            font-weight: 700;
            padding: 12px;
            width: 100%;
            border-radius: 10px;
        }
        
        .btn-crear:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(17, 153, 142, 0.3);
        }
        
        .btn-crear:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-cancelar {
            background: #6c757d;
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px;
            width: 100%;
            border-radius: 10px;
            margin-top: 10px;
        }
        
        .btn-cancelar:hover {
            background: #5a6268;
            color: white;
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
            cursor: pointer;
        }
        
        .form-control.password-input {
            border-left: none;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-house-door me-2"></i>
                Sistema TransporteUrbano
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <i class="bi bi-person-plus-fill"></i>
                <h2>Crear Nuevo Usuario</h2>
                <p class="text-muted">Complete el formulario para registrar un nuevo usuario</p>
            </div>

            <!-- Mensajes de alerta -->
            <div id="alertContainer"></div>

            <form id="formCrearUsuario">
                <div class="mb-4">
                    <label for="usu_usuario" class="form-label fw-bold">
                        <i class="bi bi-person me-2"></i>
                        Nombre de Usuario <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="usu_usuario" 
                           name="usu_usuario" 
                           placeholder="Ej: jperez, mrodriguez"
                           required>
                    <small class="text-muted">Sin espacios, solo letras y números</small>
                </div>

                <div class="mb-4">
                    <label for="nombre" class="form-label fw-bold">
                        <i class="bi bi-card-text me-2"></i>
                        Nombre Completo <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="nombre" 
                           name="nombre" 
                           placeholder="Ej: Juan Pérez García"
                           required>
                </div>

                <div class="mb-4">
                    <label for="rol" class="form-label fw-bold">
                        <i class="bi bi-shield-check me-2"></i>
                        Rol del Usuario <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="rol" name="rol" required>
                        <option value="">Seleccione un rol...</option>
                        <option value="admin">Admin - Acceso Total</option>
                        <option value="encargado">Encargado - Gestión de Peticiones</option>
                        <option value="supervisor">Supervisor - Acceso Limitado</option>
                        <option value="operador">Operador - Solo Lectura</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-bold">
                        <i class="bi bi-lock me-2"></i>
                        Contraseña <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" onclick="togglePassword('password')">
                            <i class="bi bi-eye" id="icon_password"></i>
                        </span>
                        <input type="password" 
                               class="form-control password-input" 
                               id="password" 
                               name="password" 
                               placeholder="Mínimo 6 caracteres"
                               required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password_confirm" class="form-label fw-bold">
                        <i class="bi bi-check-circle me-2"></i>
                        Confirmar Contraseña <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" onclick="togglePassword('password_confirm')">
                            <i class="bi bi-eye" id="icon_password_confirm"></i>
                        </span>
                        <input type="password" 
                               class="form-control password-input" 
                               id="password_confirm" 
                               name="password_confirm" 
                               placeholder="Repita la contraseña"
                               required>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="activo" 
                               name="activo" 
                               checked>
                        <label class="form-check-label" for="activo">
                            <strong>Usuario Activo</strong>
                            <small class="text-muted d-block">Si está marcado, el usuario podrá iniciar sesión</small>
                        </label>
                    </div>
                </div>

                <div class="info-box">
                    <strong><i class="bi bi-info-circle me-2"></i>Información sobre Roles:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>Admin:</strong> Acceso completo al sistema, gestión de usuarios</li>
                        <li><strong>Encargado:</strong> Puede gestionar peticiones y módulos operativos</li>
                        <li><strong>Supervisor:</strong> Acceso de solo lectura a reportes</li>
                        <li><strong>Operador:</strong> Acceso básico limitado</li>
                    </ul>
                </div>

                <button type="submit" class="btn btn-crear mt-4" id="btnSubmit">
                    <i class="bi bi-check-circle me-2"></i>
                    Crear Usuario
                </button>

                <a href="gestion_usuarios.php" class="btn btn-cancelar">
                    <i class="bi bi-x-circle me-2"></i>
                    Cancelar
                </a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById('icon_' + fieldId);
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        // Validar que el username no tenga espacios
        document.getElementById('usu_usuario').addEventListener('input', function(e) {
            this.value = this.value.replace(/\s/g, '').toLowerCase();
        });

        // Enviar formulario
        document.getElementById('formCrearUsuario').addEventListener('submit', async function(e) {
            e.preventDefault();

            const password = document.getElementById('password').value;
            const confirm = document.getElementById('password_confirm').value;
            const btnSubmit = document.getElementById('btnSubmit');
            const alertContainer = document.getElementById('alertContainer');

            // Validaciones
            if (password !== confirm) {
                mostrarAlerta('Las contraseñas no coinciden', 'danger');
                return;
            }

            if (password.length < 6) {
                mostrarAlerta('La contraseña debe tener al menos 6 caracteres', 'danger');
                return;
            }

            // Deshabilitar botón
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creando...';

            // Preparar datos
            const formData = new FormData(this);

            try {
                const response = await fetch('api/crear_usuario.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    mostrarAlerta('✅ Usuario creado exitosamente. Configurando permisos...', 'success');
                    setTimeout(() => {
                        window.location.href = `permisos_usuario.php?usuario_id=${data.usuario_id}`;
                    }, 1500);
                } else {
                    mostrarAlerta('❌ ' + data.message, 'danger');
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="bi bi-check-circle me-2"></i>Crear Usuario';
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarAlerta('❌ Error al comunicarse con el servidor', 'danger');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-check-circle me-2"></i>Crear Usuario';
            }
        });

        function mostrarAlerta(mensaje, tipo) {
            const alertContainer = document.getElementById('alertContainer');
            alertContainer.innerHTML = `
                <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                    ${mensaje}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            alertContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    </script>
</body>
</html>