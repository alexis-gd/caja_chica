# Arquitectura — Caja Chica

## Árbol de carpetas

```
caja_chica/
├── config/
│   ├── config.php              ← Configuración por entorno (en .gitignore)
│   ├── conexion.php            ← Función conectar() — PDO
│   └── sesiones.php            ← Guard: usuario_autenticado()
│
├── functions/                  ← Lógica de negocio (endpoints AJAX)
│   ├── login-admin.php         ← Autenticación vía API externa
│   ├── select_general.php      ← GET operations — Caja General
│   ├── select_chica_general.php← GET operations — Caja Chica
│   ├── insert_general.php      ← INSERT + lógica saldo diario — Caja General
│   ├── insert_chica_general.php← INSERT + lógica saldo diario — Caja Chica
│   ├── update_general.php      ← UPDATE — Caja General
│   ├── update_chica_general.php← UPDATE — Caja Chica
│   ├── update_user.php         ← UPDATE perfil de usuario
│   └── delete_general.php      ← DELETE archivos y modelos (ambos módulos)
│
├── ServerSide/                 ← DataTables server-side processing
│   ├── serverside.php          ← Clase SSP de DataTables
│   ├── serversideConexion.php  ← Credenciales de BD para SSP
│   ├── serversideUsuarios.php  ← Endpoint — vista_caja
│   ├── serversideUsuariosChica.php ← Endpoint — vista_caja_chica
│   └── serversideCaja.php      ← Endpoint alternativo (legacy)
│
├── templates/
│   ├── header1.php, header2.php← <head> HTML (variantes de título)
│   ├── footer5.php, footer6.php← Scripts JS (variantes por página)
│   ├── barra3.php              ← Barra de navegación superior
│   ├── navegacion4.php         ← Menú lateral (sidebar AdminLTE)
│   ├── not_found.php           ← Página 404
│   ├── modals/                 ← Modales HTML Caja General
│   │   ├── modal_add_caja.php
│   │   ├── modal_edit_caja.php
│   │   ├── modal_add_comprobante.php
│   │   ├── modal_add_catalogo.php
│   │   ├── modal_edit_catalogo.php
│   │   └── modal_add_model.php
│   └── modals_chica/           ← Modales HTML Caja Chica (misma estructura)
│       ├── modal_add_caja.php
│       ├── modal_edit_caja.php
│       ├── modal_add_comprobante.php
│       ├── modal_add_catalogo.php
│       ├── modal_edit_catalogo.php
│       └── modal_add_model.php
│
├── js/
│   ├── global.js               ← Utilidades globales (helpers DOM)
│   ├── login-ajax.js           ← Manejo del formulario de login
│   ├── admin-ajax.js           ← Operaciones de perfil
│   ├── dashboard.js            ← Gráfica Chart.js
│   ├── catalogos.js            ← Gestión de catálogos (CRUD)
│   ├── init_caja.js            ← Inicialización DataTables Caja General
│   ├── caja.js                 ← Formularios y AJAX Caja General
│   ├── init_caja_chica.js      ← Inicialización DataTables Caja Chica
│   ├── caja_chica.js           ← Formularios y AJAX Caja Chica
│   ├── upload_file.js          ← Subida de archivos Caja General
│   └── upload_chica_file.js    ← Subida de archivos Caja Chica
│
├── documents/
│   └── comprobante/
│       └── comprobante_{id}/   ← Archivos de Caja General (en .gitignore)
│
├── documents_chica/
│   └── comprobante/
│       └── comprobante_{id}/   ← Archivos de Caja Chica (en .gitignore)
│
├── index.php                   ← Redirect a login.php
├── login.php                   ← Página de login
├── dashboard.php               ← Dashboard principal
├── editar-admin.php            ← Editar perfil de usuario
├── lista-caja-general.php      ← Módulo Caja General
├── lista-caja-chica.php        ← Módulo Caja Chica
├── lista-generica-modelo-general.php ← Catálogos Caja General (solo Admin)
├── lista-generica-modelo-chica.php   ← Catálogos Caja Chica (solo Admin)
├── CLAUDE.md                   ← Referencia rápida para Claude Code
├── docs/                       ← Documentación técnica
├── .gitignore
├── glosario.md                 ← Notas de campo y glosario de BD
└── pendientes.md               ← Historial de tareas
```

---

## Flujo de una página típica

### PHP (servidor)

```
lista-caja-general.php
  ├── require config/sesiones.php    → session_start() + guard redirect
  ├── require config/config.php      → define constantes y DB_CONFIG
  ├── require templates/header1.php  → <html><head>...</head><body>
  ├── require templates/barra3.php   → navbar top
  ├── require templates/navegacion4.php → sidebar
  │
  ├── [contenido de página]
  │   ├── Tabla HTML (DataTables vacía, se llena por AJAX)
  │   └── require templates/modals/*.php → modales HTML
  │
  └── require templates/footer5.php  → scripts JS + cierre </body></html>
```

### JS (cliente)

```
footer5.php carga:
  ├── jQuery, Bootstrap, AdminLTE
  ├── DataTables + plugins
  ├── global.js
  └── init_caja.js + caja.js

Al cargar la página:
  init_caja.js → new DataTable('#tablaCaja', {
    ajax: { url: 'ServerSide/serversideUsuarios.php?tabla=caja' },
    serverSide: true,
    ...columnas y configuración
  })

Al abrir modal de agregar:
  caja.js → fetch('functions/select_general.php', { body: 'opcion=getModelGeneric&option_value=modelo_area' })
           → pobla selects del modal

Al submit del modal:
  caja.js → fetch('functions/insert_general.php', { body: formData + 'opcion=insertCaja' })
           → respuesta JSON { type, action, message }
           → SweetAlert2 según resultado
           → DataTable.ajax.reload()
```

---

## Patrón de routing en `functions/`

Cada archivo `functions/*.php` funciona como un mini-router:

```php
require_once '../config/conexion.php';
$opcion = $_POST['opcion'];  // o $_GET['opcion']
switch ($opcion) {
    case 'operacion1': echo operacion1(); break;
    case 'operacion2': echo operacion2(); break;
    default: break;
}
function operacion1() {
    $conexion = conectar();
    // ... lógica
    echo json_encode(['type' => 'SUCCESS', 'action' => 'CONTINUE', ...]);
}
```

---

## Respuesta JSON estándar

Todas las funciones responden con este esquema:

```json
{
  "type": "SUCCESS" | "ERROR",
  "action": "CONTINUE" | "CANCEL" | "TRY_AGAIN" | "DELETE" | "SHOW_LOGIN",
  "response": { ... } | "html string",
  "data": [ ... ],
  "message": "Mensaje legible para el usuario"
}
```

| `action` | Significado |
|----------|-------------|
| `CONTINUE` | Todo OK, continuar flujo |
| `CANCEL` | Detener flujo, mostrar mensaje |
| `TRY_AGAIN` | Algo falló en servidor, tomar captura y contactar soporte |
| `DELETE` | Borrar elemento del DOM |
| `SHOW_LOGIN` | Sesión expirada, redirigir a login |

---

## Patrón DataTables Server-Side

```
[Browser] → GET ServerSide/serversideUsuarios.php?tabla=caja
              ↓
[PHP]      → serverside.php (clase SSP)
              → $table_data->get('vista_caja', 'id_caja', [...columnas])
              → Construye WHERE, ORDER BY, LIMIT según parámetros DataTables
              → Retorna JSON { draw, recordsTotal, recordsFiltered, data }
```

Credenciales de BD para SSP están en `ServerSide/serversideConexion.php` (incluye `config/config.php`).

---

## Patrón de modales

Cada modal sigue esta convención de IDs:

- Modal container: `#modal{Action}{Entity}` — ej. `#modalAddCaja`
- Form: `#form-{action}-{entity}` — ej. `#form-add-caja`
- Campos: `#modal_caja_add_{campo}` — ej. `#modal_caja_add_fecha`
- Botón trigger: `#btn{Action}{Entity}` — ej. `#btnAddCaja`

---

## Cómo agregar un nuevo módulo

1. **BD**: Crear tabla `nueva_entidad` con columnas estándar (`id`, `nombre`, `band_eliminar`, timestamps).
2. **Vista**: Crear `vista_nueva_entidad` con JOINs necesarios.
3. **ServerSide**: Crear `ServerSide/serversideNuevaEntidad.php` apuntando a la vista.
4. **Functions**: Crear `functions/select_nueva.php`, `insert_nueva.php`, `update_nueva.php` siguiendo el patrón switch-case.
5. **Templates**: Crear `templates/modals_nueva/` con los modales HTML.
6. **JS**: Crear `js/init_nueva.js` (DataTables) y `js/nueva.js` (operaciones AJAX).
7. **Página**: Crear `lista-nueva-entidad.php` incluyendo header, barra, modales y footer.
8. **Navegación**: Agregar enlace en `templates/navegacion4.php`.
9. **Permisos**: Si es solo Admin, verificar `$_SESSION['nivel'] == 1` en las funciones correspondientes.
