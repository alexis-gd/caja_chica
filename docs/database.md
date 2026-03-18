# Base de datos — Caja Chica

**Nombre de la BD**: `grupour1_caja_chica`
**Motor**: MySQL
**Acceso desde PHP**: PDO con prepared statements
**Función de conexión**: `conectar()` en `config/conexion.php`

---

## ERD ASCII

```
modelo_cargado ──┐
modelo_area ─────┤
modelo_empresa ──┤
modelo_autoriza ─┤
modelo_tipo_folio┤           ┌── caja_archivos
modelo_tipo_ingreso┤  caja ──┤
modelo_tipo_gasto┤           └── caja_totales (por fecha)
modelo_entrega ──┤
modelo_recibe ───┤
modelo_comprobante┘

modelo_cargado ──┐
modelo_area ─────┤
modelo_tipo_gasto┤           ┌── caja_chica_archivos
modelo_recibe ───┤  caja_chica─┤
modelo_chica_comprobante┘    └── caja_chica_totales (por fecha)

modelo_archivo ─── caja_archivos.type_file_id
modelo_chica_archivo ─── caja_chica_archivos.type_file_id

vista_caja ← JOIN de caja + todos sus modelos (para DataTables)
vista_caja_chica ← JOIN de caja_chica + todos sus modelos (para DataTables)
```

---

## Tablas principales

### `caja` — Caja General

| Columna | Tipo | Notas |
|---------|------|-------|
| `id_caja` | INT PK AUTO_INCREMENT | |
| `fecha` | DATETIME | Fecha del movimiento (America/Mexico_City) |
| `id_cargado` | INT FK | → `modelo_cargado.id` |
| `id_area` | INT FK | → `modelo_area.id` |
| `id_empresa` | INT FK | → `modelo_empresa.id` |
| `id_autoriza` | INT FK | → `modelo_autoriza.id` |
| `folio` | VARCHAR | Número de folio libre |
| `id_folio` | INT FK | → `modelo_tipo_folio.id` |
| `id_tipo_ingreso` | INT FK | → `modelo_tipo_ingreso.id` |
| `id_tipo_gasto` | INT FK | → `modelo_tipo_gasto.id` |
| `concepto` | TEXT | Descripción libre |
| `id_entrega` | INT FK | → `modelo_entrega.id` |
| `id_recibe` | INT FK | → `modelo_recibe.id` |
| `id_comprobante` | INT FK | → `modelo_comprobante.id` |
| `id_unidad` | VARCHAR | Identificador de vehículo (puede ir vacío) |
| `id_razon_social` | VARCHAR | Nombre físico o moral |
| `ingreso` | DECIMAL(10,2) | Solo uno de ingreso/egreso > 0 |
| `egreso` | DECIMAL(10,2) | Solo uno de ingreso/egreso > 0 |
| `saldo` | DECIMAL(10,2) | Saldo acumulado del día |
| `editado` | TIMESTAMP | Última modificación |
| `creado_por` | VARCHAR | Usuario que creó el registro |
| `creado` | TIMESTAMP | Fecha de creación |
| `band_eliminar` | TINYINT | `1` = activo, `0` = eliminado (soft delete) |

### `caja_chica` — Caja Chica

Misma estructura que `caja`, con campos opcionales adicionales:

| Columna | Tipo | Notas |
|---------|------|-------|
| `id_proveedor` | INT FK | Proveedor (si aplica) |
| `id_operador` | INT FK | Operador (si aplica) |
| `id_factura` | INT FK | Referencia de factura |
| `id_comprobante` | INT FK | → `modelo_chica_comprobante.id` |

Campos menos requeridos: `id_empresa`, `id_entrega`, `id_tipo_ingreso`, `id_autoriza` son opcionales en Caja Chica.

### `caja_totales` — Saldo diario Caja General

| Columna | Tipo | Notas |
|---------|------|-------|
| `monto_total` | DECIMAL(10,2) | Saldo acumulado del día |
| `fecha` | DATETIME | Fecha del día (`YYYY-MM-DD 00:00:00`) |

Un registro por día. Se crea en el primer movimiento del día y se actualiza en los siguientes.

### `caja_chica_totales` — Saldo diario Caja Chica

Misma estructura que `caja_totales`.

### `caja_archivos` — Archivos adjuntos Caja General

| Columna | Tipo | Notas |
|---------|------|-------|
| `id` | INT PK AUTO_INCREMENT | |
| `id_caja` | INT FK | → `caja.id_caja` |
| `file_name` | VARCHAR | Nombre único del archivo |
| `file_path` | VARCHAR | Ruta relativa desde raíz del proyecto |
| `type_file_id` | INT FK | → `modelo_archivo.id` |
| `comments` | TEXT | Comentario del archivo |
| `uploaded_at` | TIMESTAMP | Fecha de subida |

### `caja_chica_archivos` — Archivos adjuntos Caja Chica

Misma estructura que `caja_archivos`. FK apunta a `caja_chica.id_caja`. `type_file_id` → `modelo_chica_archivo.id`.

---

## Tablas de catálogos (`modelo_*`)

Todas comparten la misma estructura base:

| Columna | Tipo | Notas |
|---------|------|-------|
| `id` | INT PK AUTO_INCREMENT | |
| `nombre` | VARCHAR | Nombre del ítem |
| `band_eliminar` | TINYINT | `1` = activo, `0` = eliminado |

| Tabla | Descripción |
|-------|-------------|
| `modelo_cargado` | Personas que cargan/solicitan gastos |
| `modelo_area` | Estaciones de trabajo / ranchos |
| `modelo_empresa` | Empresas del grupo |
| `modelo_autoriza` | Personas autorizadoras |
| `modelo_tipo_folio` | Tipos de folio contable |
| `modelo_tipo_ingreso` | Tipos de ingreso |
| `modelo_tipo_gasto` | Tipos de gasto (administrativo, operativo, personal…) |
| `modelo_entrega` | Personas que entregan efectivo |
| `modelo_recibe` | Personas que reciben efectivo |
| `modelo_comprobante` | Tipos de comprobante Caja General |
| `modelo_chica_comprobante` | Tipos de comprobante Caja Chica |
| `modelo_archivo` | Tipos de archivo adjunto Caja General |
| `modelo_chica_archivo` | Tipos de archivo adjunto Caja Chica |

---

## Vistas

### `vista_caja`

JOIN de `caja` con todos sus catálogos. Usada por `ServerSide/serversideUsuarios.php` para DataTables. Columnas expuestas:

```
id_caja, fecha, cargado, area, empresa, autoriza, folio, tipo_folio,
tipo_ingreso, tipo_gasto, concepto, entrega, recibe, comprobante,
unidad, razon_social, ingreso, egreso, saldo
```

### `vista_caja_chica`

Equivalente para `caja_chica`. Columnas:

```
id_caja, fecha, cargado, area, tipo_gasto, concepto, recibe,
unidad, comprobante, razon_social, ingreso, egreso, saldo
```

---

## Lógica de saldo diario

Función `getDailyBalance()` en `functions/insert_general.php` y `functions/insert_chica_general.php`:

```
1. Solo ingreso > 0 ó solo egreso > 0 (nunca ambos)
2. Busca registro en caja_totales WHERE DATE(fecha) = fecha_del_movimiento
3. Si existe:
   - ingreso > 0 → monto_total += ingreso
   - egreso > 0  → monto_total -= egreso
   - UPDATE caja_totales
4. Si no existe:
   - INSERT caja_totales con monto_total = ingreso (primer movimiento del día)
5. Retorna monto_total → se guarda en caja.saldo
```

Todo dentro de una transacción PDO. Si falla, rollback.

> **Nota pendiente**: Revisar si el saldo debe calcularse por día del registro o por fecha actual. Ver [docs/roadmap.md](roadmap.md).

---

## Queries clave

### Totales mensuales (dashboard)

```sql
SELECT
    SUM(ingreso) AS total_ingreso,
    SUM(egreso)  AS total_egreso
FROM caja
WHERE band_eliminar = 1
  AND fecha BETWEEN '2026-03-01' AND '2026-03-31';
```

### Gráfica anual

```sql
SELECT
    MONTH(fecha)     AS mes,
    MONTHNAME(fecha) AS nombre_mes,
    SUM(ingreso)     AS total_ingreso,
    SUM(egreso)      AS total_egreso
FROM caja
WHERE band_eliminar = 1
  AND YEAR(fecha) = YEAR(CURDATE())
GROUP BY MONTH(fecha)
ORDER BY MONTH(fecha);
```

### Catálogos activos

```sql
SELECT id, nombre FROM modelo_area WHERE band_eliminar = 1 ORDER BY nombre ASC;
```

### Archivos de un registro

```sql
SELECT c.id_comprobante, mc.nombre AS comprobante_nombre,
       a.id, a.file_name, a.file_path, a.comments, a.uploaded_at
FROM caja c
LEFT JOIN modelo_comprobante mc ON c.id_comprobante = mc.id
LEFT JOIN caja_archivos a ON a.id_caja = c.id_caja
WHERE c.id_caja = ?;
```

---

## Tipos de datos importantes

- Fechas se almacenan como `DATETIME` en formato `Y-m-d H:i:s`
- Montos como `DECIMAL(10,2)` — nunca `FLOAT`
- `band_eliminar`: `1` = activo, `0` = eliminado (contraintuitivo — `1` es "vivo")
- Nombres en catálogos: normalizados (`ucfirst(strtolower(...))`) sin caracteres especiales al insertar
