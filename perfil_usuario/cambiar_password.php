<?php
require_once 'verificar_sesion.php';
require_once '../conexion.php';

$mensaje_error = '';
$mensaje_exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_actual = $_POST['password_actual'] ?? '';
    $password_nueva = $_POST['password_nueva'] ?? '';
    $password_confirmar = $_POST['password_confirmar'] ?? '';
    
    // Validaciones
    if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
        $mensaje_error = 'Todos los campos son obligatorios';
    } elseif ($password_nueva !== $password_confirmar) {
        $mensaje_error = 'La nueva contraseña y su confirmación no coinciden';
    } elseif (strlen($password_nueva) < 6) {
        $mensaje_error = 'La nueva contraseña debe tener al menos 6 caracteres';
    } else {
        // Verificar contraseña actual
        $stmt = $conexion->prepare("SELECT usu_password FROM 4usuario WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($fila = $resultado->fetch_assoc()) {
            if (password_verify($password_actual, $fila['usu_password'])) {
                // Contraseña actual correcta, actualizar a la nueva
                $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
                
                $stmt_update = $conexion->prepare("UPDATE 4usuario SET usu_password = ? WHERE id = ?");
                $stmt_update->bind_param("si", $password_hash, $usuario_id);
                
                if ($stmt_update->execute()) {
                    // Contraseña actualizada exitosamente
                    // Destruir la sesión por seguridad
                    $_SESSION = array();
                    if (isset($_COOKIE[session_name()])) {
                        setcookie(session_name(), '', time() - 3600, '/');
                    }
                    session_destroy();
                    
                    // Redirigir al login con mensaje de éxito
                    header("Location: login.php?password_changed=1");
                    exit();
                } else {
                    $mensaje_error = 'Error al actualizar la contraseña. Intente nuevamente.';
                }
                
                $stmt_update->close();
            } else {
                $mensaje_error = 'La contraseña actual es incorrecta';
            }
        }
        
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #0064C8;
            --primary-dark: #004a96;
            --primary-light: #3384d4;
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
        
        .user-greeting {
            color: white;
            font-size: 1rem;
        }
        
        .btn-user-dropdown {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 20px;
            padding: 8px 20px;
            font-weight: 500;
        }
        
        .btn-user-dropdown:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            border-color: rgba(255,255,255,0.4);
        }
        
        .dropdown-menu {
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            border: none;
            padding: 0.5rem 0;
        }
        
        .dropdown-header {
            font-weight: 700;
            color: var(--primary-color);
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
        }
        
        .dropdown-item {
            padding: 0.6rem 1.5rem;
            transition: all 0.2s;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
            padding-left: 1.8rem;
        }
        
        .dropdown-item.text-danger:hover {
            background-color: #ffe6e6;
        }
        
        .password-container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 40px;
        }
        
        .password-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .password-header i {
            font-size: 60px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .password-header h2 {
            color: #2d3748;
            font-weight: 700;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 100, 200, 0.25);
        }
        
        .btn-cambiar {
            background: var(--primary-color);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px;
            width: 100%;
            border-radius: 10px;
        }
        
        .btn-cambiar:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 100, 200, 0.3);
        }
        
        .btn-volver {
            background: #6c757d;
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px;
            width: 100%;
            border-radius: 10px;
            margin-top: 10px;
        }
        
        .btn-volver:hover {
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
        
        .password-requirements {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        
        .password-requirements ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
        
        .password-requirements li {
            color: #6c757d;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-house-door me-2"></i>Sistema TransporteUrbano
            </a>
            <div class="ms-auto d-flex align-items-center">
                <span class="user-greeting me-3">
                    <i class="bi bi-emoji-smile me-2"></i>
                    Bienvenido, <strong><?php echo htmlspecialchars(explode(' ', $usuario_nombre)[0]); ?></strong>
                </span>
                
                <!-- Dropdown de usuario -->
                <div class="dropdown">
                    <button class="btn btn-user-dropdown dropdown-toggle" type="button" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-2"></i>
                        Mi Cuenta
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser">
                        <li class="dropdown-header">
                            <i class="bi bi-person me-2"></i>
                            <?php echo htmlspecialchars($usuario_nombre); ?>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item active" href="cambiar_password.php">
                                <i class="bi bi-key me-2"></i>
                                Cambiar Contraseña
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="password-container">
            <div class="password-header">
                <i class="bi bi-shield-lock"></i>
                <h2>Cambiar Contraseña</h2>
                <p class="text-muted">Actualiza tu contraseña de acceso</p>
            </div>

            <?php if ($mensaje_error): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?php echo htmlspecialchars($mensaje_error); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="formPassword">
                <div class="mb-4">
                    <label for="password_actual" class="form-label fw-bold">
                        <i class="bi bi-lock me-2"></i>
                        Contraseña Actual
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" onclick="togglePassword('password_actual')">
                            <i class="bi bi-eye" id="icon_password_actual"></i>
                        </span>
                        <input type="password" 
                               class="form-control password-input" 
                               id="password_actual" 
                               name="password_actual" 
                               placeholder="Ingrese su contraseña actual"
                               required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password_nueva" class="form-label fw-bold">
                        <i class="bi bi-key me-2"></i>
                        Nueva Contraseña
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" onclick="togglePassword('password_nueva')">
                            <i class="bi bi-eye" id="icon_password_nueva"></i>
                        </span>
                        <input type="password" 
                               class="form-control password-input" 
                               id="password_nueva" 
                               name="password_nueva" 
                               placeholder="Ingrese su nueva contraseña"
                               required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password_confirmar" class="form-label fw-bold">
                        <i class="bi bi-check-circle me-2"></i>
                        Confirmar Nueva Contraseña
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" onclick="togglePassword('password_confirmar')">
                            <i class="bi bi-eye" id="icon_password_confirmar"></i>
                        </span>
                        <input type="password" 
                               class="form-control password-input" 
                               id="password_confirmar" 
                               name="password_confirmar" 
                               placeholder="Confirme su nueva contraseña"
                               required>
                    </div>
                </div>

                <div class="password-requirements">
                    <strong><i class="bi bi-info-circle me-2"></i>Requisitos de la contraseña:</strong>
                    <ul>
                        <li>Debe tener al menos 6 caracteres</li>
                        <li>Se recomienda usar una combinación de letras y números</li>
                        <li>Evita usar información personal fácil de adivinar</li>
                    </ul>
                </div>

                <button type="submit" class="btn btn-cambiar mt-4">
                    <i class="bi bi-shield-check me-2"></i>
                    Cambiar Contraseña
                </button>

                <a href="../peticiones/peticiones.php" class="btn btn-volver">
                    <i class="bi bi-arrow-left me-2"></i>
                    Volver a Peticiones
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

        // Validación en tiempo real
        document.getElementById('formPassword').addEventListener('submit', function(e) {
            const nueva = document.getElementById('password_nueva').value;
            const confirmar = document.getElementById('password_confirmar').value;

            if (nueva !== confirmar) {
                e.preventDefault();
                alert('La nueva contraseña y su confirmación no coinciden');
                return false;
            }

            if (nueva.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres');
                return false;
            }
        });
    </script>
</body>
</html>