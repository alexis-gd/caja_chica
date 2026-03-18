# Roadmap — Caja Chica

## Features completados

### Core

- [x] Módulo Caja General — CRUD completo con todos los campos contables
- [x] Módulo Caja Chica — CRUD completo con campos simplificados
- [x] Dashboard con métricas mensuales y gráfica anual
- [x] Catálogos Caja General — CRUD de todas las listas desplegables
- [x] Catálogos Caja Chica — CRUD de listas específicas
- [x] Soft delete en registros principales y catálogos
- [x] Saldo diario acumulado (`caja_totales` / `caja_chica_totales`)
- [x] Tablas DataTables con server-side processing y SearchPanes
- [x] Botón de subida de comprobante (condicional: solo egresos pendientes)
- [x] Historial de comprobantes por registro
- [x] Adjuntar archivos JPG/PNG/PDF con validación MIME
- [x] Editar perfil de usuario
- [x] Permisos: Admin vs Capturista

### Infraestructura

- [x] Migración de `mysqli` a PDO
- [x] Centralización de config en `config/` (config.php, conexion.php, sesiones.php)
- [x] Detección automática de entorno (dev/qa/prod) por hostname
- [x] Transacciones PDO en todas las operaciones de escritura
- [x] Autenticación delegada a API externa compartida

---

## Estandarización — skill `project-standards`

**✅ Migración completada.** Todos los módulos aplicados. Commits documentados.

| # | Módulo | Estado | Descripción |
|---|--------|--------|-------------|
| 1 | Config & Carpetas | ✅ | `config.php`, `conexion.php`, `sesiones.php` movidos a `config/`. Commit `be21320` |
| 2 | Conexión PDO | ✅ | Migración completa de `mysqli` a PDO. Commit `168550a` |
| 3 | Whitelist inputs | ✅ | Validar `$tabla`/`$modelo` contra whitelist. 9 puntos en 7 archivos. |
| 4 | Sesiones & Auth | ✅ | `session_status()` check + helper `tiene_nivel()`. Commit `6d2715d` |
| 5 | Templates | ✅ | `header.php`, `navbar.php`, `sidebar.php`, `footer.php`, `footer_table.php`. 7 páginas actualizadas. Commit `6d2715d` |
| 6 | Functions CRUD | ✅ | `functions/caja_general/` y `functions/caja_chica/` con insert, update, select, delete. Commit `c412901` |
| 7 | JavaScript | ✅ | `fetchFillSelect` unificada con param `base`. Eliminadas `*2`. Commit `381c74c` |
| 8 | Limpieza | ✅ | Eliminados 7 templates legacy + `demo.js`. `login.php` sin dependencia de `header1.php`. Commit `d1724d2` |

### Desviaciones documentadas (aceptadas)

Revisadas con el skill `project-standards` al finalizar la migración:

| Desviación | Estándar del skill | Decisión |
|---|---|---|
| `functions/caja_general/select.php` (subcarpetas) | `functions/select_caja_general.php` (plano) | **Aceptada** — dos módulos en un proyecto justifican subcarpetas. Documentar en nuevos proyectos similares. |
| `band_eliminar = 1` → activo | `band_eliminar = 0` → activo | **No migrar** — requeriría migración de datos en producción. Convención invertida heredada. |
| `fetchFillSelect(option, id_item, ..., base)` | `fetchFillSelect(selectId, url, path, ...)` | **Aceptada** — implementación más rica (Selectize + Select2 + pre-selección). Anti-patrón de duplicación sí se eliminó. |
| `type: 'SUCCESS'` (mayúsculas) | `type: 'success'` (minúsculas) | **No migrar** — convención anterior a la migración, cambiarla rompería los JS existentes. |

---

## Pendientes identificados

### UI / UX

- [ ] **Formatear montos en modales** — Los campos de ingreso/egreso no muestran formato de moneda al editar; actualmente se muestran como número plano.

### Lógica de negocio

- [ ] **Definir cálculo de saldo**: Revisar si el campo `saldo` debe reflejar el saldo diario (comportamiento actual), mensual, o global. El diseño actual acumula por día del *registro seleccionado*, no por fecha actual — confirmar con negocio si es correcto.
- [ ] **Fecha de totales**: Verificar si `caja_totales` debe comparar por fecha del movimiento registrado o por fecha en que se captura. Actualmente usa la fecha del campo `fecha` del formulario.

### Performance

- [ ] **Optimizar queries de catálogos** — `getModelGeneric()` ejecuta `SELECT *` sin índice optimizado; en tablas grandes puede ser lento. Considerar cache o índice en `nombre`.

### Funcionalidad

- [ ] **Sumatoria filtrada en tabla** — Ya implementada para filas visibles; verificar que funcione correctamente cuando SearchPanes aplica filtros combinados.
- [ ] **Logout explícito** — No hay página/botón de cierre de sesión documentado.

---

## Smoke tests (pendiente — prioritario antes de deploy)

Después del Módulo 8 armar un script de pruebas básicas que valide los endpoints sin abrir el navegador.

### Qué cubrir

| Área | Qué probar |
|------|-----------|
| Endpoints PHP | `POST` a cada `select.php`, `insert.php`, `update.php`, `delete.php` con opción válida → respuesta `{"type":"SUCCESS"}` |
| Selects Caja General | `getModelGeneric` con cada `modelo_*` → array no vacío |
| Selects Caja Chica | Misma prueba con `modelo_chica_*` apuntando a `functions/caja_chica/select.php` |
| Insert genérico | `insertModelsGeneric` en ambos módulos → `newId` en respuesta |
| Sesión guard | Request sin sesión a cualquier `functions/*.php` → respuesta de error, no datos |

### Herramienta sugerida

Script PHP CLI (sin dependencias externas) que haga `curl` contra `http://grupouribe.local/caja_chica/functions/...` y afirme los tipos de respuesta. Alternativamente Postman collection exportada como JSON.

---

## Mejoras técnicas sugeridas

### Seguridad

- [ ] Rotar el `TOKEN` de la API (actualmente `'12345'` en todos los entornos)
- [ ] Agregar protección CSRF en formularios de modales
- [ ] Agregar rate limiting al endpoint de login

### Código

- [ ] La función `deleteFile()` en `delete_general.php` referencia `vehiculo_archivos` — probable legacy del proyecto inventario. Revisar y corregir a `caja_archivos`.
- [ ] Unificar `functions/select_general.php` y `functions/select_chica_general.php` en funciones parametrizadas para reducir duplicación.
- [ ] Centralizar `getDailyBalance()` — actualmente duplicada en `insert_general.php` e `insert_chica_general.php`.

### Base de datos

- [ ] Agregar índices en columnas de búsqueda frecuente: `caja.fecha`, `caja.band_eliminar`, `caja_totales.fecha`
- [ ] Documentar estructura exacta de columnas de `vista_caja` y `vista_caja_chica` (actualmente solo inferida del código)

### Migración PHP 8

- [ ] Auditar uso de funciones deprecadas en PHP 8 (el proyecto usa PHP 7.x actualmente)
- [ ] Revisar `mime_content_type()` — disponible pero considerar `finfo_file()` como alternativa más robusta
- [ ] Agregar declaraciones de tipos en funciones críticas
- [ ] Revisar `$_SESSION` en funciones sin `session_start()` explícito (algunas funciones en `functions/` hacen `session_start()` solo si necesitan sesión)

---

## Historial de versiones relevante

| Commit | Descripción |
|--------|-------------|
| `168550a` | Migración de mysqli a PDO (Módulo 2) |
| `be21320` | Mover config/, conexion y sesiones a carpeta config/ |
| `225851e` | Mejoras en formato de moneda |
| `9c702b2` | Botón de comprobante en Caja Chica |
| `6a90d70` | Reporte de Caja Chica: saldo total + mejora de impresión |
