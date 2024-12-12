<?php
// Iniciar sesion
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

    if ($_SESSION['access']['petty_cash'] == 1) {
      $respuesta = array(
        'response' => $apiResponse['type'],
        'user' => $apiResponse['data']['customer']['full_name'],
        'access' => $apiResponse['data']['customer']['access'],
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
