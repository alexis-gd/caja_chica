# Scope — Qué hace el sistema

## Descripción general

**Caja Chica** es un sistema de control de flujo de efectivo para Grupo Uribe. Registra movimientos de ingreso y egreso en dos fondos separados: la **Caja General** (con mayor detalle contable) y la **Caja Chica** (operaciones menores simplificadas).

Permite al personal capturar gastos con comprobantes, gestionar catálogos de clasificación y visualizar el estado financiero mensual y anual en un dashboard.

---

## Módulos activos

### 1. Caja General (`lista-caja-general.php`)

Registro de todos los movimientos de efectivo de la empresa con trazabilidad completa:

- Fecha del movimiento
- Quién lo cargó, quién lo entregó, quién lo recibió, quién lo autorizó
- Área, empresa, tipo de folio, folio, tipo de ingreso, tipo de gasto
- Concepto (descripción libre)
- Unidad (vehículo o vacío)
- Razón social (nombre físico o moral)
- Ingreso / Egreso / Saldo diario acumulado
- Comprobante (tipo + archivos adjuntos: JPG, PNG, PDF)

### 2. Caja Chica (`lista-caja-chica.php`)

Versión simplificada para gastos menores. Campos reducidos respecto a Caja General:

- Sin folio ni tipo de folio
- Sin empresa ni autoriza (opcional)
- Sin tipo de ingreso (opcional)
- Misma lógica de saldo diario pero en `caja_chica_totales`
- Misma capacidad de adjuntar comprobantes

### 3. Dashboard (`dashboard.php`)

Vista ejecutiva con:
- Total de registros en Caja General
- Ingresos, egresos y saldo del mes actual (Caja General)
- Gráfica de barras: ingresos vs egresos por mes del año en curso

### 4. Catálogos — Caja General (`lista-generica-modelo-general.php`)

Administración de las listas desplegables de Caja General:
- `modelo_cargado` — Personas que cargan el gasto
- `modelo_area` — Estaciones de trabajo / ranchos
- `modelo_empresa` — Empresas del grupo
- `modelo_autoriza` — Personas autorizadoras
- `modelo_tipo_folio` — Tipos de folio
- `modelo_tipo_ingreso` — Tipos de ingreso
- `modelo_tipo_gasto` — Tipos de gasto
- `modelo_entrega` — Personas que entregan
- `modelo_recibe` — Personas que reciben
- `modelo_comprobante` — Tipos de comprobante

### 5. Catálogos — Caja Chica (`lista-generica-modelo-chica.php`)

Igual que los anteriores pero para Caja Chica:
- `modelo_chica_comprobante` — Tipos de comprobante específicos de Caja Chica
- `modelo_archivo` / `modelo_chica_archivo` — Tipos de archivo adjunto
- Comparte los catálogos de personas/áreas con Caja General (mismas tablas)

### 6. Editar perfil (`editar-admin.php`)

Permite al usuario autenticado cambiar su nombre de usuario, nombre completo y contraseña. Llama a la API externa de usuarios.

---

## Roles y permisos

| Rol | `$_SESSION['nivel']` | Puede |
|-----|----------------------|-------|
| **Admin** | `1` | CRUD completo en catálogos, editar/eliminar cualquier registro |
| **Capturista** | `!= 1` | Agregar nuevos registros, editar propios, imprimir, descargar comprobantes |

El acceso al sistema requiere que la API retorne `access.petty_cash == 1`.
El nivel específico se toma de `roles.petty_cash` retornado por la API.

---

## Reglas de negocio

1. **Ingreso o egreso, nunca ambos**: Un registro solo puede tener ingreso > 0 **o** egreso > 0, jamás los dos.
2. **Saldo diario**: El campo `saldo` de cada registro refleja el saldo acumulado del día hasta ese movimiento. Ver cálculo en [docs/database.md](database.md#lógica-de-saldo-diario).
3. **Soft delete**: Los registros eliminados se marcan con `band_eliminar = 0`. Nunca se borran físicamente de las tablas principales.
4. **Comprobantes**: Solo egresos reciben comprobante; el botón de subir comprobante es condicional (debe ser egreso y estar pendiente de comprobante).
5. **Archivos permitidos**: Solo JPG, PNG y PDF. Validación por MIME type en servidor.
6. **Catálogos solo Admin**: Crear, editar o eliminar ítems de catálogos requiere `nivel == 1`.
7. **Sin duplicados en catálogos**: `insertModelsGeneric()` verifica existencia antes de insertar.
8. **Zona horaria**: America/Mazatlan para timestamps generados en servidor.

---

## Glosario de negocio

| Término | Significado |
|---------|-------------|
| Cargado a | Persona que solicita o carga el gasto |
| Área | Estación de trabajo / rancho (ej. Rancho Maravillas) |
| Tipo de folio | Categoría del folio contable |
| Folio | Número de folio del movimiento |
| Entrega | Persona que entrega el efectivo |
| Recibe | Persona que recibe el efectivo |
| Autoriza | Persona que autoriza el movimiento |
| Comprobante | Tipo de documento de respaldo (factura, nota, recibo, etc.) |
| Unidad | Vehículo o grúa asociada al gasto (puede ir vacío) |
| Razón social | Nombre físico o moral al que se factura |
| Saldo | Saldo acumulado del día del movimiento |
