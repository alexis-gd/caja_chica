<?php
// Iniciar sesión
if (isset($_POST['login-admin'])) {
  $usuario = $_POST['usuario'];
  $password = $_POST['password'];

  // Datos para enviar a la API
  $data = array(
    "usuario" => $usuario,
    "password" => $password
  );

  // Incluir el archivo de configuración
  require_once 'config.php';

  // Obtener el hostname del servidor actual
  $url = BASE_URL . API_EP_LOGIN;

  // Inicializar cURL
  $ch = curl_init($url);

  // Configurar opciones de cURL
  curl_setopt($ch, CURLOPT_POST, 1); // Usar método POST
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Devolver la respuesta como cadena
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . TOKEN)); // Añadir cabecera de autorización
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Pasar los datos como JSON

  // Ejecutar la solicitud
  $response = curl_exec($ch);

  // Verificar errores en la ejecución de cURL
  if (curl_errno($ch)) {
    echo 'Error en cURL: ' . curl_error($ch);
    die();
  }

  // Cerrar cURL
  curl_close($ch);

  // Convertir respuesta JSON a array PHP
  $apiResponse = json_decode($response, true);

  // Validar la respuesta de la API
  if (isset($apiResponse['type']) && $apiResponse['type'] === 'SUCCESS') {
    session_start();
    $_SESSION['id'] = $apiResponse['data']['customer']['user_id'];
    $_SESSION['usuario'] = $apiResponse['data']['customer']['user_name'];
    $_SESSION['nombre'] = $apiResponse['data']['customer']['full_name'];
    $_SESSION['nivel'] = $apiResponse['data']['customer']['role_id'];

    // Accesos del usuario
    $_SESSION['access'] = $apiResponse['data']['customer']['access'];

    // Roles del usuario por plataforma
    $_SESSION['roles'] = $apiResponse['data']['customer']['roles'];  // Aquí guardamos los roles de la plataforma

    // Verificamos si tiene acceso a 'petty_cash' y asignamos el nivel basado en los roles
    if ($_SESSION['access']['petty_cash'] == 1) {
      // Buscar el rol correspondiente a 'petty_cash'
      if (isset($_SESSION['roles']['petty_cash'])) {
        $_SESSION['nivel'] = $_SESSION['roles']['petty_cash'];  // Asignamos el rol de 'petty_cash'
      }
      
      // Aquí puedes agregar otras verificaciones para otros accesos similares, si es necesario

      $respuesta = array(
        'response' => $apiResponse['type'],
        'user' => $apiResponse['data']['customer']['full_name'],
        'access' => $_SESSION['access'],  // Aquí se mantiene el acceso con los roles incluidos
        'roles' => $_SESSION['roles'],  // Incluir los roles en la respuesta
        'nivel' => $_SESSION['nivel'],  // Añadir el nivel a la respuesta
        'message' => $apiResponse['data']['message'] ? $apiResponse['data']['message'] : 'Success en autenticación'
      );
    } else {
      $respuesta = array(
        'response' => 'CANCEL',
        'message' => 'Parece que no tienes acceso a esta plataforma. Comunícate con soporte técnico para más detalles.'
      );
    }
  } else {
    $respuesta = array(
      'response' => $apiResponse['type'],
      'message' => $apiResponse['message'] ? $apiResponse['message'] : 'Error en autenticación'
    );
  }

  die(json_encode($respuesta));
}
