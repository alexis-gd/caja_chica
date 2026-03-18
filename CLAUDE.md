# CLAUDE.md — Caja Chica (Grupo Uribe)

Referencia rápida para Claude Code. Sin datos de negocio detallados aquí — ver `docs/`.

---

## Stack

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 7.x, PDO |
| Base de datos | MySQL — `grupour1_caja_chica` |
| UI Framework | AdminLTE 3 + Bootstrap 4 |
| JS principal | jQuery 3.4.1 |
| Tablas | DataTables.js (server-side) + SearchPanes |
| Gráficas | Chart.js |
| Date picker | Flatpickr |
| Select | Selectize.js |
| Alertas | SweetAlert2 |

## URLs por entorno

| Entorno | Host | Login |
|---------|------|-------|
| dev | `grupouribe.local` | `http://grupouribe.local/caja_chica/login.php` |
| qa | `nodosmx.com` | `https://nodosmx.com/caja_chica/login.php` |
| prod | `grupouribe.org` | `https://grupouribe.org/caja_chica/login.php` |

El entorno se detecta automáticamente por `$_SERVER['HTTP_HOST']` en `config/config.php`.
**No exponer credenciales** — `config/config.php` está en `.gitignore`.

---

## Archivos críticos

| Archivo | Propósito |
|---------|-----------|
| `config/config.php` | Configuración de entornos y BD (**en .gitignore**) |
| `config/conexion.php` | Función `conectar()` — PDO |
| `config/sesiones.php` | Guard de sesión (`usuario_autenticado()`) |
| `functions/login-admin.php` | Autenticación vía API externa |
| `functions/select_general.php` | Endpoints GET — Caja General |
| `functions/select_chica_general.php` | Endpoints GET — Caja Chica |
| `functions/insert_general.php` | Endpoints INSERT + lógica de saldo diario |
| `functions/insert_chica_general.php` | Igual, para Caja Chica |
| `functions/update_general.php` | Endpoints UPDATE |
| `functions/update_chica_general.php` | Igual, para Caja Chica |
| `functions/delete_general.php` | Endpoints DELETE (modelos y archivos) |
| `ServerSide/serversideUsuarios.php` | DataTables endpoint — `vista_caja` |
| `ServerSide/serversideUsuariosChica.php` | DataTables endpoint — `vista_caja_chica` |
| `lista-caja-general.php` | Página principal Caja General |
| `lista-caja-chica.php` | Página principal Caja Chica |
| `dashboard.php` | Dashboard con métricas y gráfica anual |

---

## Patrones clave

- **Routing**: `switch($opcion)` en cada archivo `functions/`. El parámetro llega por `$_POST['opcion']`.
- **Respuesta JSON estándar**: `{ type, action, response|data, message }` — ver `docs/arquitectura.md`.
- **Saldo diario**: calculado por `getDailyBalance()` en `insert_general.php` / `insert_chica_general.php`, se acumula en `caja_totales` / `caja_chica_totales` por fecha.
- **Soft delete**: `band_eliminar = 1` significa activo, `0` eliminado. Todos los SELECTs filtran `WHERE band_eliminar = 1`.
- **Admin check**: `$_SESSION['nivel'] != 1` bloquea operaciones de catálogos.
- **Transacciones PDO**: toda escritura usa `beginTransaction / commit / rollBack`.

---

## Reglas de desarrollo

- Nunca hacer `SELECT *` sin `WHERE band_eliminar = 1` en tablas con soft delete.
- Las tablas de catálogos tienen prefijo `modelo_` (ej. `modelo_area`, `modelo_tipo_gasto`).
- Los archivos subidos van a `documents/comprobante/comprobante_{id}/` (Caja General) o `documents_chica/comprobante/comprobante_{id}/` (Caja Chica) — ambas carpetas en `.gitignore`.
- No hardcodear rutas absolutas; usar rutas relativas desde la raíz del proyecto.
- Validar MIME con `mime_content_type()`, nunca solo la extensión.

---

## Migración en curso

Este proyecto está siendo estandarizado con el skill **`project-standards`**.
Para cualquier tarea de estructura, convenciones, seguridad o modernización, invocar ese skill antes de proponer cambios.

| Módulo | Estado | Descripción |
|--------|--------|-------------|
| Módulo 1 — config/ | ✓ Completado | config.php, conexion.php, sesiones.php en carpeta config/ |
| Módulo 2 — PDO | ✓ Completado | Migración completa de mysqli a PDO en todos los archivos |
| Módulo 3 — Whitelist inputs | 🔄 Plan listo | 9 puntos en 7 archivos. Whitelists definidas (13 tablas general, 8 chica). Ver plan en esta conversación antes de implementar. |

---

## Documentación detallada

- [docs/scope.md](docs/scope.md) — Qué hace el sistema, módulos, roles, reglas de negocio
- [docs/database.md](docs/database.md) — Esquema completo, ERD, queries clave
- [docs/arquitectura.md](docs/arquitectura.md) — Árbol de carpetas, flujo de página, patrones, cómo agregar módulo
- [docs/autenticacion.md](docs/autenticacion.md) — Login, sesión, protección de páginas, roles
- [docs/roadmap.md](docs/roadmap.md) — Completados, pendientes, mejoras técnicas
