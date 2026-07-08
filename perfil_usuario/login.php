<?php
date_default_timezone_set('America/Lima');
session_start();

// Si ya tiene sesión activa, redirigir
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

// Detectar redirección por cambio de contraseña
$password_changed = isset($_GET['password_changed']) && $_GET['password_changed'] === '1';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema TransporteUrbano</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #002E90;
            --primary-dark:  #002167;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(rgba(15,23,42,0.55), rgba(15,23,42,0.65)),
                        url('img/buses2.jpg') center center / cover no-repeat fixed;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px) saturate(140%);
            -webkit-backdrop-filter: blur(12px) saturate(140%);
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.35);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            animation: cardIn 0.6s cubic-bezier(0.22, 1, 0.36, 1);
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .login-header {
            background: linear-gradient(135deg, rgba(0,46,144,0.94) 0%, rgba(0,33,103,0.94) 100%);
            color: white;
            padding: 26px 30px;
            text-align: center;
        }

        .login-header .logo-badge {
            display: inline-block;
            background: #ffffff;
            padding: 14px 22px;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.20);
        }
        .login-header .logo-badge img { height: 116px; width: auto; display: block; max-width: 100%; }

        .login-body { padding: 40px 30px; }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 46, 144, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #001845 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 46, 144, 0.45);
        }

        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }

        .form-control   { border-left: none; box-shadow: none; }

        /* botón ver / ocultar contraseña */
        .toggle-pass {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-left: none;
            color: #6b7280;
            cursor: pointer;
        }
        .toggle-pass:hover { color: var(--primary-color); }

        .login-foot {
            text-align: center;
            padding: 4px 30px 24px;
            color: #6b7280;
            font-size: 12px;
        }

        .alert          { border-radius: 10px; }
    </style>
</head>
<body>
    <div class="login-container">

        <div class="login-header">
            <h1 class="h4 fw-bold mb-1">SIAPC</h1>
            <p class="mb-0 small" style="opacity:.8;">Sistema de Análisis de Paraderos Críticos</p>
        </div>

        <div class="login-body">

            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <?php if ($password_changed): ?>
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div>
                        <strong>¡Contraseña actualizada exitosamente!</strong><br>
                        Por favor, inicia sesión con tu nueva contraseña.
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="login_process.php">

                <div class="mb-4">
                    <label for="usuario" class="form-label fw-bold">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text"
                               class="form-control"
                               id="usuario"
                               name="usuario"
                               placeholder="Ingrese su usuario"
                               required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-bold">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password"
                               class="form-control"
                               id="password"
                               name="password"
                               placeholder="Ingrese su contraseña"
                               required>
                        <button type="button" class="toggle-pass input-group-text" id="togglePass" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
                            <i class="bi bi-eye" id="togglePassIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Iniciar Sesión
                </button>

            </form>
        </div>

        <div class="login-foot">
            TransporteUrbano S.A. &middot; <?= date('Y') ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            var btn = document.getElementById('togglePass'),
                ico = document.getElementById('togglePassIcon'),
                inp = document.getElementById('password');
            if (btn) {
                btn.addEventListener('click', function () {
                    var show = inp.type === 'password';
                    inp.type = show ? 'text' : 'password';
                    ico.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
                });
            }
        })();
    </script>
</body>
</html>