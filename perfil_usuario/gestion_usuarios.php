<?php
require_once 'verificar_sesion.php';
require_once 'verificar_permisos.php';

// Solo admins pueden ver usuarios
soloAdmin($usuario_rol);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
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
        
        .page-title {
            color: #2d3748;
            font-weight: 700;
            margin: 30px 0;
        }
        
        .header-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 20px;
            overflow-x: auto;
        }
        
        .table thead th {
            background: var(--primary-color);
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px 10px;
            vertical-align: middle;
            text-align: center;
        }
        
        .table tbody td {
            vertical-align: middle;
            padding: 12px 8px;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .btn-crear {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            color: white;
            font-weight: 700;
            padding: 12px 30px;
            border-radius: 10px;
        }
        
        .btn-crear:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(17, 153, 142, 0.3);
        }
        
        .btn-permisos {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 700;
            padding: 12px 30px;
            border-radius: 10px;
        }
        
        .btn-permisos:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-editar {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .btn-editar:hover {
            background: var(--primary-dark);
            color: white;
        }
        
        .btn-eliminar {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .btn-eliminar:hover {
            background: #c82333;
            color: white;
        }
        
        .badge-rol {
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .badge-admin {
            background: #dc3545;
            color: white;
        }
        
        .badge-encargado {
            background: var(--primary-color);
            color: white;
        }
        
        .badge-supervisor {
            background: #ffc107;
            color: #000;
        }
        
        .badge-operador {
            background: #6c757d;
            color: white;
        }
        
        .badge-activo {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        
        .badge-inactivo {
            background: #6c757d;
            color: white;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        
        .btn-back {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
        
        .btn-back:hover {
            background: var(--primary-dark);
            color: white;
        }
        
        .loading {
            text-align: center;
            padding: 60px;
        }
        
        .loading i {
            font-size: 48px;
            color: var(--primary-color);
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
            <div class="ms-auto d-flex align-items-center">
                <span class="user-greeting me-3">
                    <i class="bi bi-emoji-smile me-2"></i>
                    Bienvenido, <strong><?php echo htmlspecialchars(explode(' ', $usuario_nombre)[0]); ?></strong>
                </span>
                
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
                            <a class="dropdown-item" href="cambiar_password.php">
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

    <div class="container-fluid px-4">
        <h1 class="page-title text-center">
            <i class="bi bi-people-fill me-2"></i>
            Gestión de Usuarios
        </h1>

        <!-- Mensajes de alerta -->
        <div id="alertContainer"></div>

        <!-- Header con botones -->
        <div class="header-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h5 class="mb-1">
                        <i class="bi bi-list-ul me-2"></i>
                        Lista de Usuarios del Sistema
                    </h5>
                    <p class="text-muted mb-0">
                        Total de usuarios: <strong id="totalUsuarios">-</strong>
                    </p>
                </div>
                <div class="mt-2 mt-md-0">
                    <a href="permisos_usuario.php" class="btn btn-permisos me-2">
                        <i class="bi bi-shield-lock me-2"></i>
                        Gestionar Permisos
                    </a>
                    <a href="crear_usuario.php" class="btn btn-crear me-2">
                        <i class="bi bi-person-plus-fill me-2"></i>
                        Crear Nuevo Usuario
                    </a>
                    <a href="dashboard.php" class="btn btn-back">
                        <i class="bi bi-arrow-left me-2"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="table-container">
            <div id="tablaContainer">
                <div class="loading">
                    <i class="bi bi-arrow-clockwise"></i>
                    <p class="mt-3">Cargando usuarios...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div class="modal fade" id="modalEliminar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar al usuario <strong id="usuarioEliminar"></strong>?</p>
                    <p class="text-danger mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        <strong>Esta acción no se puede deshacer.</strong>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnConfirmarEliminar" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>
                        Eliminar Usuario
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const usuarioActualId = <?php echo $usuario_id; ?>;
        let usuarioEliminarId = null;

        // Cargar usuarios al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            cargarUsuarios();
            verificarMensajes();
        });

        // Verificar mensajes en URL
        function verificarMensajes() {
            const params = new URLSearchParams(window.location.search);
            const accion = params.get('accion');
            
            if (accion === 'creado') {
                mostrarAlerta('✅ Usuario creado exitosamente', 'success');
            } else if (accion === 'actualizado') {
                mostrarAlerta('✅ Usuario actualizado exitosamente', 'success');
            } else if (accion === 'eliminado') {
                mostrarAlerta('✅ Usuario eliminado correctamente', 'success');
            } else if (accion === 'error') {
                mostrarAlerta('❌ Hubo un error al procesar la solicitud', 'danger');
            }
            
            // Limpiar URL
            if (accion) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        }

        // Cargar usuarios desde API
        async function cargarUsuarios() {
            try {
                const response = await fetch('api/obtener_usuarios.php');
                const data = await response.json();

                if (data.success) {
                    renderizarTabla(data.usuarios);
                    document.getElementById('totalUsuarios').textContent = data.usuarios.length;
                } else {
                    mostrarError('Error al cargar usuarios');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error al comunicarse con el servidor');
            }
        }

        // Renderizar tabla de usuarios
        function renderizarTabla(usuarios) {
            const container = document.getElementById('tablaContainer');
            
            if (usuarios.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #6c757d;"></i>
                        <p class="text-muted mt-2">No hay usuarios registrados</p>
                    </div>
                `;
                return;
            }

            let html = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th width="120">Usuario</th>
                                <th width="200">Nombre Completo</th>
                                <th width="120">Rol</th>
                                <th width="100">Estado</th>
                                <th width="150">Fecha Creación</th>
                                <th width="150">Último Acceso</th>
                                <th width="150">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            usuarios.forEach(usuario => {
                const rolClass = usuario.rol.toLowerCase();
                const estadoClass = usuario.activo == 1 ? 'badge-activo' : 'badge-inactivo';
                const estadoTexto = usuario.activo == 1 ? 'Activo' : 'Inactivo';
                const fechaCreacion = formatearFecha(usuario.fecha_creacion);
                const ultimoAcceso = usuario.ultimo_acceso ? formatearFechaHora(usuario.ultimo_acceso) : '<span class="text-muted">Nunca</span>';
                
                html += `
                    <tr>
                        <td class="text-center"><strong>${usuario.id}</strong></td>
                        <td><code>${usuario.usu_usuario}</code></td>
                        <td>${usuario.nombre}</td>
                        <td class="text-center">
                            <span class="badge-rol badge-${rolClass}">
                                ${usuario.rol.toUpperCase()}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="${estadoClass}">${estadoTexto}</span>
                        </td>
                        <td class="text-center">${fechaCreacion}</td>
                        <td class="text-center">${ultimoAcceso}</td>
                        <td class="text-center">
                            <a href="editar_usuario.php?id=${usuario.id}" class="btn btn-editar btn-sm me-1" title="Editar">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            ${usuario.id != usuarioActualId ? `
                                <button class="btn btn-eliminar btn-sm" onclick="confirmarEliminar(${usuario.id}, '${usuario.usu_usuario}')" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            ` : ''}
                        </td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            container.innerHTML = html;
        }

        // Confirmar eliminación
        function confirmarEliminar(id, usuario) {
            usuarioEliminarId = id;
            document.getElementById('usuarioEliminar').textContent = usuario;
            new bootstrap.Modal(document.getElementById('modalEliminar')).show();
        }

        // Eliminar usuario
        document.getElementById('btnConfirmarEliminar').addEventListener('click', async function() {
            if (!usuarioEliminarId) return;

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Eliminando...';

            const formData = new FormData();
            formData.append('id', usuarioEliminarId);

            try {
                const response = await fetch('api/eliminar_usuario.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalEliminar')).hide();
                    mostrarAlerta('✅ ' + data.message, 'success');
                    cargarUsuarios(); // Recargar lista
                } else {
                    mostrarAlerta('❌ ' + data.message, 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarAlerta('❌ Error al comunicarse con el servidor', 'danger');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-trash me-2"></i>Eliminar Usuario';
            }
        });

        // Funciones auxiliares
        function formatearFecha(fecha) {
            const date = new Date(fecha);
            return date.toLocaleDateString('es-PE');
        }

        function formatearFechaHora(fecha) {
            const date = new Date(fecha);
            return date.toLocaleDateString('es-PE') + ' ' + date.toLocaleTimeString('es-PE', {hour: '2-digit', minute: '2-digit'});
        }

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

        function mostrarError(mensaje) {
            document.getElementById('tablaContainer').innerHTML = `
                <div class="alert alert-danger text-center">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    ${mensaje}
                </div>
            `;
        }
    </script>
</body>
</html>