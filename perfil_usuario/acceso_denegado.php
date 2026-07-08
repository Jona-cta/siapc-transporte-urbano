<?php
require_once 'verificar_sesion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado - TransporteUrbano</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #0064C8;
            --primary-dark: #004a96;
        }
        
        body {
            background-color: #f5f7fa;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .access-denied-container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            max-width: 600px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .icon-denied {
            font-size: 120px;
            color: #dc3545;
            margin-bottom: 30px;
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .access-denied-container h1 {
            color: #dc3545;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 20px;
        }
        
        .access-denied-container p {
            color: #666;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .info-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: left;
        }
        
        .info-box i {
            color: #ffc107;
            font-size: 1.2rem;
            margin-right: 10px;
        }
        
        .info-box p {
            margin: 0;
            font-size: 0.95rem;
        }
        
        .btn-volver {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-volver:hover {
            background: var(--primary-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 100, 200, 0.3);
        }
        
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .user-info strong {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="access-denied-container">
        <!-- Icono de acceso denegado -->
        <i class="bi bi-shield-x icon-denied"></i>
        
        <!-- Título -->
        <h1>Acceso Denegado</h1>
        
        <!-- Información del usuario -->
        <div class="user-info">
            <i class="bi bi-person-circle me-2"></i>
            <strong><?php echo htmlspecialchars($usuario_nombre); ?></strong>
            <span class="text-muted">(<?php echo htmlspecialchars($usuario_rol); ?>)</span>
        </div>
        
        <!-- Mensaje principal -->
        <p>
            No tienes los permisos necesarios para acceder a este módulo o realizar esta acción.
        </p>
        
        <!-- Caja de información -->
        <div class="info-box">
            <i class="bi bi-info-circle"></i>
            <p>
                <strong>¿Por qué veo este mensaje?</strong><br>
                Tu cuenta de usuario no tiene los permisos configurados para acceder a esta sección del sistema. 
                Si crees que deberías tener acceso, contacta al administrador del sistema.
            </p>
        </div>
        
        <!-- Botones de acción -->
        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
            <a href="dashboard.php" class="btn-volver">
                <i class="bi bi-house-door me-2"></i>
                Volver al Inicio
            </a>
        </div>
        
        <!-- Información adicional -->
        <div class="mt-4 text-muted" style="font-size: 0.9rem;">
            <i class="bi bi-envelope me-1"></i>
            ¿Necesitas ayuda? Contacta al administrador del sistema
        </div>
    </div>
</body>
</html>