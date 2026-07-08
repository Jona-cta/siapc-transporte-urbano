<?php
require_once 'verificar_sesion.php';

// Solo admins pueden gestionar permisos
if ($usuario_rol !== 'admin') {
    header("Location: dashboard.php?error=sin_permisos");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Permisos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #0064C8;
            --primary-dark: #004a96;
            --success-color: #28a745;
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
        
        .main-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .header-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .header-card h2 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .selector-usuario {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .permisos-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            display: none;
        }
        
        .permisos-card.active {
            display: block;
        }
        
        .modulo-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary-color);
        }
        
        .modulo-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .modulo-header i {
            font-size: 24px;
            color: var(--primary-color);
            margin-right: 15px;
        }
        
        .modulo-nombre {
            font-weight: 700;
            font-size: 1.1rem;
            color: #333;
        }
        
        .modulo-descripcion {
            font-size: 0.9rem;
            color: #6c757d;
            margin-left: 39px;
            margin-bottom: 15px;
        }
        
        .permisos-checkboxes {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-left: 39px;
        }
        
        .form-check-custom {
            flex: 0 0 auto;
        }
        
        .form-check-custom .form-check-input:checked {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .form-check-custom label {
            font-weight: 600;
            margin-left: 5px;
        }
        
        .btn-guardar {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            color: white;
            font-weight: 700;
            padding: 12px 40px;
            border-radius: 10px;
            font-size: 1.1rem;
        }
        
        .btn-guardar:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(17, 153, 142, 0.3);
            color: white;
        }
        
        .btn-cancelar {
            background: #6c757d;
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 40px;
            border-radius: 10px;
        }
        
        .btn-cancelar:hover {
            background: #5a6268;
            color: white;
        }
        
        .alert-info-custom {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
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
        
        .no-permisos {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .info-usuario {
            background: #e8f4f8;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .info-usuario strong {
            color: var(--primary-color);
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

    <div class="main-container">
        <!-- Header -->
        <div class="header-card">
            <h2><i class="bi bi-shield-lock me-2"></i>Gestión de Permisos de Usuario</h2>
            <p class="text-muted mb-0">Asigna permisos personalizados para cada usuario del sistema</p>
        </div>

        <!-- Selector de Usuario -->
        <div class="selector-usuario">
            <h5 class="mb-3"><i class="bi bi-person-check me-2"></i>Seleccionar Usuario</h5>
            <div class="row">
                <div class="col-md-8">
                    <select class="form-select form-select-lg" id="selectUsuario">
                        <option value="">-- Seleccione un usuario --</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <a href="gestion_usuarios.php" class="btn btn-cancelar w-100">
                        <i class="bi bi-arrow-left me-2"></i>Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Card de Permisos -->
        <div class="permisos-card" id="permisosCard">
            <div class="info-usuario" id="infoUsuario"></div>
            
            <div class="alert-info-custom">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Instrucciones:</strong> Marca los checkboxes según los permisos que deseas otorgar a este usuario. 
                Puedes combinar diferentes niveles de acceso para cada módulo.
            </div>

            <form id="formPermisos">
                <div id="modulosContainer">
                    <div class="loading">
                        <i class="bi bi-arrow-clockwise"></i>
                        <p class="mt-3">Cargando módulos...</p>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-guardar">
                        <i class="bi bi-check-circle me-2"></i>Guardar Permisos
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let usuarioSeleccionado = null;
        let todosLosModulos = [];
        let permisosActuales = [];
        let usuarioIdInicial = null;

        // Cargar usuarios al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar si viene usuario_id en URL
            const urlParams = new URLSearchParams(window.location.search);
            usuarioIdInicial = urlParams.get('usuario_id');
            
            cargarUsuarios();
        });

        // Cuando se selecciona un usuario
        document.getElementById('selectUsuario').addEventListener('change', function() {
            const usuarioId = this.value;
            if (usuarioId) {
                const option = this.options[this.selectedIndex];
                usuarioSeleccionado = {
                    id: usuarioId,
                    nombre: option.dataset.nombre,
                    usuario: option.text,
                    rol: option.dataset.rol
                };
                cargarPermisosUsuario();
            } else {
                document.getElementById('permisosCard').classList.remove('active');
            }
        });

        // Cargar lista de usuarios
        async function cargarUsuarios() {
            try {
                const response = await fetch('api/obtener_usuarios.php');
                const data = await response.json();
                
                if (data.success) {
                    const select = document.getElementById('selectUsuario');
                    select.innerHTML = '<option value="">-- Seleccione un usuario --</option>';
                    
                    data.usuarios.forEach(usuario => {
                        const option = document.createElement('option');
                        option.value = usuario.id;
                        option.textContent = `${usuario.usu_usuario} - ${usuario.nombre} (${usuario.rol})`;
                        option.dataset.nombre = usuario.nombre;
                        option.dataset.rol = usuario.rol;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error al cargar usuarios:', error);
                alert('Error al cargar la lista de usuarios');
            }
        }

        // Cargar permisos del usuario seleccionado
        async function cargarPermisosUsuario() {
            document.getElementById('permisosCard').classList.add('active');
            document.getElementById('modulosContainer').innerHTML = `
                <div class="loading">
                    <i class="bi bi-arrow-clockwise"></i>
                    <p class="mt-3">Cargando permisos...</p>
                </div>
            `;

            // Mostrar info del usuario
            document.getElementById('infoUsuario').innerHTML = `
                <strong>Usuario:</strong> ${usuarioSeleccionado.nombre} (${usuarioSeleccionado.usuario}) - 
                <strong>Rol:</strong> ${usuarioSeleccionado.rol}
            `;

            try {
                // Cargar módulos disponibles
                const responseModulos = await fetch('api/obtener_modulos.php');
                const dataModulos = await responseModulos.json();
                
                if (!dataModulos.success) {
                    throw new Error('Error al cargar módulos');
                }
                
                todosLosModulos = dataModulos.modulos;

                // Cargar permisos actuales del usuario
                const responsePermisos = await fetch(`api/obtener_permisos.php?usuario_id=${usuarioSeleccionado.id}`);
                const dataPermisos = await responsePermisos.json();
                
                if (!dataPermisos.success) {
                    throw new Error('Error al cargar permisos');
                }
                
                permisosActuales = dataPermisos.permisos;

                // Renderizar módulos con permisos
                renderizarModulos();

            } catch (error) {
                console.error('Error:', error);
                document.getElementById('modulosContainer').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Error al cargar los datos. Por favor, intente nuevamente.
                    </div>
                `;
            }
        }

        // Renderizar módulos con checkboxes
        function renderizarModulos() {
            const container = document.getElementById('modulosContainer');
            
            if (todosLosModulos.length === 0) {
                container.innerHTML = `
                    <div class="no-permisos">
                        <i class="bi bi-inbox" style="font-size: 48px;"></i>
                        <p>No hay módulos disponibles</p>
                    </div>
                `;
                return;
            }

            let html = '';
            
            todosLosModulos.forEach(modulo => {
                // Buscar permisos actuales de este módulo
                const permisoActual = permisosActuales.find(p => parseInt(p.modulo_id) === parseInt(modulo.id)) || {
                    puede_ver: 0,
                    puede_crear: 0,
                    puede_editar: 0,
                    puede_eliminar: 0
                };

                html += `
                    <div class="modulo-item">
                        <div class="modulo-header">
                            <i class="${modulo.icono}"></i>
                            <div>
                                <div class="modulo-nombre">${modulo.nombre}</div>
                            </div>
                        </div>
                        <div class="modulo-descripcion">${modulo.descripcion}</div>
                        <div class="permisos-checkboxes">
                            <div class="form-check-custom">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="ver_${modulo.id}" 
                                       data-modulo="${modulo.id}"
                                       data-permiso="ver"
                                       ${permisoActual.puede_ver ? 'checked' : ''}>
                                <label class="form-check-label" for="ver_${modulo.id}">
                                    <i class="bi bi-eye text-primary"></i> Ver
                                </label>
                            </div>
                            <div class="form-check-custom">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="crear_${modulo.id}" 
                                       data-modulo="${modulo.id}"
                                       data-permiso="crear"
                                       ${permisoActual.puede_crear ? 'checked' : ''}>
                                <label class="form-check-label" for="crear_${modulo.id}">
                                    <i class="bi bi-plus-circle text-success"></i> Crear
                                </label>
                            </div>
                            <div class="form-check-custom">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="editar_${modulo.id}" 
                                       data-modulo="${modulo.id}"
                                       data-permiso="editar"
                                       ${permisoActual.puede_editar ? 'checked' : ''}>
                                <label class="form-check-label" for="editar_${modulo.id}">
                                    <i class="bi bi-pencil text-warning"></i> Editar
                                </label>
                            </div>
                            <div class="form-check-custom">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="eliminar_${modulo.id}" 
                                       data-modulo="${modulo.id}"
                                       data-permiso="eliminar"
                                       ${permisoActual.puede_eliminar ? 'checked' : ''}>
                                <label class="form-check-label" for="eliminar_${modulo.id}">
                                    <i class="bi bi-trash text-danger"></i> Eliminar
                                </label>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Guardar permisos
        document.getElementById('formPermisos').addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!usuarioSeleccionado) {
                alert('Debe seleccionar un usuario');
                return;
            }

            // Recopilar todos los permisos marcados
            const permisos = [];
            
            todosLosModulos.forEach(modulo => {
                const puede_ver = document.getElementById(`ver_${modulo.id}`).checked ? 1 : 0;
                const puede_crear = document.getElementById(`crear_${modulo.id}`).checked ? 1 : 0;
                const puede_editar = document.getElementById(`editar_${modulo.id}`).checked ? 1 : 0;
                const puede_eliminar = document.getElementById(`eliminar_${modulo.id}`).checked ? 1 : 0;

                permisos.push({
                    modulo_id: modulo.id,
                    puede_ver: puede_ver,
                    puede_crear: puede_crear,
                    puede_editar: puede_editar,
                    puede_eliminar: puede_eliminar
                });
            });

            // Enviar al servidor
            const formData = new FormData();
            formData.append('usuario_id', usuarioSeleccionado.id);
            formData.append('permisos', JSON.stringify(permisos));

            try {
                const response = await fetch('api/gestionar_permisos.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert('✅ Permisos guardados exitosamente');
                    // Recargar permisos para mostrar cambios
                    cargarPermisosUsuario();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('❌ Error al guardar los permisos');
            }
        });
    </script>
</body>
</html>