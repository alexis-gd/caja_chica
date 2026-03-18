# Autenticación — Caja Chica

## Mecanismo general

El sistema **no tiene base de datos de usuarios propia**. La autenticación se delega a un servicio externo compartido por todos los proyectos de Grupo Uribe (`{BASE_URL}/login.php`).

Flujo:
```
[Browser] → POST login.php → [PHP] → POST {BASE_URL}/login.php (API)
                                           ↓
                                    JSON { type, data.customer }
                                           ↓
                             Valida access.petty_cash == 1
                                           ↓
                             session_start() → $_SESSION poblado
                                           ↓
                             [Browser] redirige a dashboard.php
```

---

## Archivo: `functions/login-admin.php`

Procesado cuando `$_POST['login-admin']` está seteado (submit del form en `login.php`).

### Qué hace:

1. Recibe `usuario` y `password` del formulario
2. Hace POST a `BASE_URL . API_EP_LOGIN` con JSON `{"usuario": "...", "password": "..."}`
3. Header de autorización: `Authorization: Bearer {TOKEN}` (TOKEN definido en `config/config.php`)
4. Si la API responde `type === 'SUCCESS'`:
   - Verifica `access.petty_cash == 1` — si no tiene acceso, retorna CANCEL
   - Puebla la sesión con datos del usuario
   - Asigna `nivel` desde `roles.petty_cash`
5. Retorna JSON al browser (manejado por `js/login-ajax.js`)

---

## Estructura de la sesión

Tras login exitoso:

```php
$_SESSION['id']       // user_id retornado por la API
$_SESSION['usuario']  // user_name (login name)
$_SESSION['nombre']   // full_name (nombre completo)
$_SESSION['nivel']    // role_id específico para 'petty_cash'
$_SESSION['access']   // array de accesos por plataforma
$_SESSION['roles']    // array de roles por plataforma
```

### Ejemplo de `$_SESSION['access']`:

```php
[
  'petty_cash' => 1,   // 1 = tiene acceso, 0 = no tiene acceso
  'inventory'  => 1,
  'payroll'    => 0,
  // ...otras plataformas
]
```

### Ejemplo de `$_SESSION['roles']`:

```php
[
  'petty_cash' => 1,   // 1 = Admin, otro = Capturista
  'inventory'  => 2,
  // ...
]
```

---

## Roles dentro del sistema

El campo clave es `$_SESSION['nivel']`, que toma el valor de `roles.petty_cash`:

| `$_SESSION['nivel']` | Rol | Permisos |
|----------------------|-----|----------|
| `1` | Admin | CRUD en catálogos, eliminar cualquier registro, editar cualquier registro |
| `!= 1` (ej. 2, 3…) | Capturista | Solo crear registros, editar propios, imprimir, descargar |

### Verificación en PHP (funciones):

```php
// Bloquear operación de catálogo si no es admin
if ($_SESSION['nivel'] != 1) {
    throw new Exception('No tienes autorización para agregar conceptos a las listas.');
}
```

### Verificación en templates (mostrar/ocultar botones):

```php
<?php if ($_SESSION['nivel'] == 1): ?>
    <button id="btnDeleteModelo">Eliminar</button>
<?php endif; ?>
```

---

## Protección de páginas

### Archivo: `config/sesiones.php`

```php
function usuario_autenticado() {
    if (!revisar_usuario()) {
        header('Location: login.php');
        exit();
    }
}
function revisar_usuario() {
    return isset($_SESSION['usuario']);
}
session_start();
usuario_autenticado();
```

### Cómo proteger una página

Incluir al inicio de cualquier página PHP protegida:

```php
<?php
require_once 'config/sesiones.php';  // Desde raíz del proyecto
// Si no hay sesión activa, redirige automáticamente a login.php
?>
```

Desde subcarpetas (`functions/`), la sesión ya debe existir — las funciones llaman `session_start()` individualmente cuando necesitan acceder a `$_SESSION`.

### Páginas protegidas actualmente:

- `dashboard.php`
- `lista-caja-general.php`
- `lista-caja-chica.php`
- `lista-generica-modelo-general.php`
- `lista-generica-modelo-chica.php`
- `editar-admin.php`

### Páginas públicas:

- `index.php` — Redirige a `login.php`
- `login.php` — Página de login (no requiere sesión)

---

## Logout

No existe un archivo de logout explícito documentado. El mecanismo estándar sería:

```php
session_start();
session_destroy();
header('Location: login.php');
exit();
```

---

## API externa — detalles técnicos

- **Endpoint**: `{BASE_URL}/login.php` (configurado por entorno en `config/config.php`)
- **Método**: POST con body JSON
- **Auth header**: `Authorization: Bearer {TOKEN}` — TOKEN es `'12345'` en todos los entornos (token de servicio compartido)
- **Timeout**: Sin timeout configurado explícitamente (default cURL)
- **Errores cURL**: Se detectan con `curl_errno()` — ante error, `die()` con mensaje

> El TOKEN `'12345'` es un token de servicio interno, no una contraseña de usuario.
