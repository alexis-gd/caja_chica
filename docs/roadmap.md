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

Plan de migración al estándar del portafolio. Invocar el skill antes de trabajar en cualquier módulo pendiente.

| # | Módulo | Estado | Descripción |
|---|--------|--------|-------------|
| 1 | Config & Carpetas | ✅ Completado | `config.php`, `conexion.php`, `sesiones.php` movidos a `config/`. Todas las referencias actualizadas. Commit `be21320` |
| 2 | Conexión PDO | ✅ Completado | Migración completa de `mysqli` a PDO en todos los archivos. Commit `168550a` |
| 3 | Whitelist inputs | ✅ Completado | Validar `$tabla`/`$modelo` de `$_POST` contra whitelist. 9 puntos en 7 archivos. 13 tablas general + 8 chica |
| 4 | Sesiones & Auth | ✅ Completado | `session_status()` check en `sesiones.php` y en 12 llamadas de functions/. Helper `tiene_nivel()` agregado. |
| 5 | Templates | ✅ Completado | `header.php` (fusión header1+header2), `navbar.php`, `sidebar.php`, `footer.php` (fusión footer5+footer6), `footer_table.php` (fusión footer5+footer_table). 7 páginas actualizadas. Archivos viejos pendientes de eliminar en Módulo 8. |
| 6 | Functions CRUD | ✅ Completado | Reorganizar `functions/` en subcarpetas: `functions/caja_general/` (insert, update, select, delete) y `functions/caja_chica/` (insert, update, select, delete). 18 referencias JS + 8 `require_once` actualizados. `delete_general.php` copiado a ambas subcarpetas. |
| 7 | JavaScript | ⏳ Pendiente | Eliminar `fetchFillSelect2` y `handleNewOptionAdd2` de `global.js`. Unificar en función única con `path` como parámetro |
| 8 | Limpieza | ⏳ Pendiente | Eliminar `demo.js`, `footer_table copy.php`, comentarios muertos. Verificar referencia rota a `chart.min.js` en `dashboard.php` |

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
