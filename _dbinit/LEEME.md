# Inicialización de la base de datos (Docker)

Los archivos `.sql` de esta carpeta se ejecutan **automáticamente** la primera
vez que arranca el contenedor de MariaDB (cuando el volumen está vacío).

## `00_demo.sql`  — lo que trae el repositorio

Contiene **solo**:
- La estructura de las 7 tablas del sistema.
- Datos mínimos: catálogos (rutas y sentidos), **un usuario admin de demo** y el
  registro de los 2 módulos.

Credenciales de demo:

```
usuario: admin
clave:   admin123
```

> No se incluyen datos reales (validaciones, paraderos, usuarios) por privacidad
> y por tamaño. Por eso, al levantar el repo tal cual, las pantallas de **análisis
> aparecerán vacías** ("sin datos"): el login y la navegación funcionan, pero los
> KPIs/recomendaciones necesitan un volcado real.

## Cargar datos reales (opcional, entorno privado)

Si tienes un volcado real (p. ej. `siapc_subset.sql`), colócalo en esta carpeta
junto al `00_demo.sql` **antes** del primer arranque, o cárgalo luego con:

```bash
docker exec -i siapc_db mariadb -uroot -proot turbano_bd_op < tu_volcado.sql
```

Para re-inicializar desde cero: `docker compose down -v` y luego `docker compose up -d`.
