<?php
require_once 'verificar_sesion.php';
require_once 'verificar_permisos.php';
require_once '../conexion.php';

// Solo admins pueden editar usuarios
soloAdmin($usuario_rol);

// Obtener ID del usuario a editar
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: gestion_usuarios.php?error=id_invalido");
    exit();
}

// Obtener datos del usuario
$stmt = $conexion->prepare("SELECT id, usu_usuario, nombre, rol, activo FROM 4usuario WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: gestion_usuarios.php?error=usuario_no_encontrado");
    exit();
}

$usuario = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
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
        
        .user-info-box {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid var(--primary-color);
        }
        
        .password-section {
            background: #fff9e6;
            padding: 20px;
            border-radius: 10px;
            margin: 25px 0;
            border-left: 4px solid #ffc107;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 100, 200, 0.25);
        }
        
        .btn-actualizar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 700;
            padding: 12px;
            width: 100%;
            border-radius: 10px;
        }
        
        .btn-actualizar:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-actualizar:disabled {
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
                <i class="bi bi-pencil-square"></i>
                <h2>Editar Usuario</h2>
                <p class="text-muted">Modifica la información del usuario</p>
            </div>

            <!-- Información del usuario actual -->
            <div class="user-info-box">
                <strong><i class="bi bi-info-circle me-2"></i>Editando:</strong><br>
                <strong>ID:</strong> <?php echo htmlspecialchars($usuario['id']); ?> | 
                <strong>Usuario:</strong> <?php echo htmlspecialchars($usuario['usu_usuario']); ?>
            </div>

            <!-- Mensajes de alerta -->
            <div id="alertContainer"></div>

            <form id="formEditarUsuario">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario['id']); ?>">
                
                <div class="mb-4">
                    <label for="nombre" class="form-label fw-bold">
                        <i class="bi bi-card-text me-2"></i>
                        Nombre Completo <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="nombre" 
                           name="nombre" 
                           value="<?php echo htmlspecialchars($usuario['nombre']); ?>"
                           required>
                </div>

                <div class="mb-4">
                    <label for="rol" class="form-label fw-bold">
                        <i class="bi bi-shield-check me-2"></i>
                        Rol del Usuario <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="rol" name="rol" required>
                        <option value="admin" <?php echo $usuario['rol'] === 'admin' ? 'selected' : ''; ?>>Admin - Acceso Total</option>
                        <option value="encargado" <?php echo $usuario['rol'] === 'encargado' ? 'selected' : ''; ?>>Encargado - Gestión de Peticiones</option>
                        <option value="supervisor" <?php echo $usuario['rol'] === 'supervisor' ? 'selected' : ''; ?>>Supervisor - Acceso Limitado</option>
                        <option value="operador" <?php echo $usuario['rol'] === 'operador' ? 'selected' : ''; ?>>Operador - Solo Lectura</option>
                    </select>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="activo" 
                               name="activo" 
                               <?php echo $usuario['activo'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="activo">
                            <strong>Usuario Activo</strong>
                            <small class="text-muted d-block">Si está marcado, el usuario podrá iniciar sesión</small>
                        </label>
                    </div>
                </div>

                <!-- Sección de cambio de contraseña -->
                <div class="password-section">
                    <h5 class="mb-3">
                        <i class="bi bi-key me-2"></i>
                        Cambiar Contraseña (Opcional)
                    </h5>
                    <p class="text-muted small mb-3">
                        Solo completa estos campos si deseas cambiar la contraseña del usuario. 
                        De lo contrario, déjalos vacíos.
                    </p>

                    <div class="form-check mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="cambiar_password" 
                               name="cambiar_password">
                        <label class="form-check-label" for="cambiar_password">
                            <strong>Deseo cambiar la contraseña</strong>
                        </label>
                    </div>

                    <div id="passwordFields" style="display: none;">
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">
                                Nueva Contraseña
                            </label>
                            <div class="input-group">
                                <span class="input-group-text" onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="icon_password"></i>
                                </span>
                                <input type="password" 
                                       class="form-control password-input" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Mínimo 6 caracteres">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirm" class="form-label fw-bold">
                                Confirmar Nueva Contraseña
                            </label>
                            <div class="input-group">
                                <span class="input-group-text" onclick="togglePassword('password_confirm')">
                                    <i class="bi bi-eye" id="icon_password_confirm"></i>
                                </span>
                                <input type="password" 
                                       class="form-control password-input" 
                                       id="password_confirm" 
                                       name="password_confirm" 
                                       placeholder="Repita la contraseña">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-actualizar mt-4" id="btnSubmit">
                    <i class="bi bi-check-circle me-2"></i>
                    Guardar Cambios
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
        // Mostrar/ocultar campos de contraseña
        document.getElementById('cambiar_password').addEventListener('change', function() {
            const passwordFields = document.getElementById('passwordFields');
            if (this.checked) {
                passwordFields.style.display = 'block';
                document.getElementById('password').required = true;
                document.getElementById('password_confirm').required = true;
            } else {
                passwordFields.style.display = 'none';
                document.getElementById('password').required = false;
                document.getElementById('password_confirm').required = false;
                document.getElementById('password').value = '';
                document.getElementById('password_confirm').value = '';
            }
        });

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

        // Enviar formulario
        document.getElementById('formEditarUsuario').addEventListener('submit', async function(e) {
            e.preventDefault();

            const cambiarPassword = document.getElementById('cambiar_password').checked;
            const btnSubmit = document.getElementById('btnSubmit');

            // Validar contraseñas si se va a cambiar
            if (cambiarPassword) {
                const password = document.getElementById('password').value;
                const confirm = document.getElementById('password_confirm').value;

                if (password !== confirm) {
                    mostrarAlerta('Las contraseñas no coinciden', 'danger');
                    return;
                }

                if (password.length < 6) {
                    mostrarAlerta('La contraseña debe tener al menos 6 caracteres', 'danger');
                    return;
                }
            }

            // Deshabilitar botón
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

            // Preparar datos
            const formData = new FormData(this);
            formData.append('cambiar_password', cambiarPassword ? '1' : '0');

            try {
                const response = await fetch('api/editar_usuario.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    mostrarAlerta('✅ Usuario actualizado exitosamente. Redirigiendo...', 'success');
                    setTimeout(() => {
                        window.location.href = 'gestion_usuarios.php?accion=actualizado';
                    }, 1500);
                } else {
                    mostrarAlerta('❌ ' + data.message, 'danger');
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="bi bi-check-circle me-2"></i>Guardar Cambios';
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarAlerta('❌ Error al comunicarse con el servidor', 'danger');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-check-circle me-2"></i>Guardar Cambios';
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