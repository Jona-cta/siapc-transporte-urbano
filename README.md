# SIAPC - Sistema Inteligente de Analisis de Paraderos Criticos

**Curso Integrador I | Universidad Tecnologica del Peru**
**Autor:** Vega Carazas, Jonathan Jose | U20247082

---

## Descripcion

Sistema web que analiza las validaciones acumuladas por carrera en el sistema IVU de TransporteUrbano S.A. para detectar cuando los buses alcanzan su capacidad maxima antes de llegar a paraderos criticos. Genera recomendaciones automaticas de buses adicionales por franja horaria y permite visualizar patrones historicos de saturacion.

**Empresa cliente:** TransporteUrbano S.A., Lima 2026
**Rutas cubiertas:** 301, 303, 305, 336
**Capacidad maxima por bus:** 80 pasajeros

---

## Tecnologias utilizadas

- PHP 8.2 (Apache) — contenerizado con Docker
- MariaDB 11.4
- Bootstrap 5.1
- JavaScript (Fetch API)
- Chart.js 4 · ApexCharts · Flatpickr
- Docker / Docker Compose (entorno reproducible)

---

## Estructura del proyecto

```
siapc-transporte-urbano/
├── docker-compose.yml          # Orquestacion: web (PHP+Apache) + db (MariaDB) + phpMyAdmin
├── Dockerfile                  # Imagen PHP 8.2 con mysqli
├── conexion.php                # Conexion a BD (lee variables de entorno)
├── .env.example                # Plantilla de configuracion
├── docker/
│   └── deny-sensitive.conf     # Bloquea archivos sensibles por HTTP
├── _dbinit/
│   ├── 00_demo.sql             # Estructura + admin demo (SIN datos reales)
│   └── LEEME.md
├── paradero_critico/           # MODULO PRINCIPAL DEL PROYECTO
│   ├── paradero_critico.php    # UI: analisis diario de paradero critico
│   ├── kpi_patrones.php        # UI: patrones e indicadores historicos
│   └── api/
│       ├── obtener_paraderos.php
│       ├── analisis_paradero_critico.php   # Algoritmo principal de saturacion
│       └── kpi_datos.php
└── perfil_usuario/             # Modulo compartido: login, roles y permisos
    ├── login.php  dashboard.php  _layout.php  verificar_permisos.php ...
    └── api/ ...
```

---

## Como levantar el proyecto (Docker)

Requisito: Docker Desktop.

```bash
git clone https://github.com/Jona-cta/siapc-transporte-urbano
cd siapc-transporte-urbano
docker compose up -d --build
```

Luego abrir: **http://localhost:8080/bd_op/perfil_usuario/login.php**

Credenciales de demostracion:

```
usuario: admin
clave:   admin123
```

> **Nota sobre los datos:** el repositorio incluye solo la estructura de la base
> y un usuario de demo (privacidad + limite de 100MB de GitHub). Por eso, tras
> clonar, el login y la navegacion funcionan, pero las **pantallas de analisis
> apareceran vacias** hasta cargar un volcado real de validaciones. Ver
> `_dbinit/LEEME.md`.

Otros servicios: phpMyAdmin en **http://localhost:8081** (servidor `db`).

---

## Modulos del sistema

### Analisis diario de paradero critico
Permite seleccionar fecha, ruta, sentido y paradero critico para obtener las
franjas horarias con saturacion detectada y la cantidad de buses adicionales
recomendados.

### Patrones e indicadores historicos
KPIs agregados sobre el periodo disponible: tasa de saturacion, mapa de calor por
dia de semana y franja horaria, tendencia mensual y comparativa por ruta.

---

## Seguridad

El acceso requiere autenticacion activa mediante el modulo de sesiones
(`/bd_op/perfil_usuario/login.php`). Todos los endpoints validan la sesion y el
permiso de modulo antes de ejecutar cualquier consulta. Las credenciales reales
no se versionan (ver `.gitignore`).

---

## Licencia

Proyecto academico desarrollado para el Curso Integrador I - UTP. Uso restringido
a fines educativos.
