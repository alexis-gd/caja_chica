<?php
session_start();
$user_id = $_SESSION['id']; // Obtener el ID del usuario desde la sesión
$full_name = $_POST['nombre'];
$user_name = $_POST['usuario'];
$user_password = $_POST['password'];

$data = [
    "user_id" => $user_id,
    "full_name" => $full_name,
    "user_name" => $user_name,
    "user_password" => $user_password
];

// Incluir el archivo de configuración
require_once 'config.php';

// Configurar la solicitud PUT a la API
$url = BASE_URL . API_EP_LOGIN;
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . TOKEN));
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Error en cURL: ' . curl_error($ch);
    die();
}

curl_close($ch);

$apiResponse = json_decode($response, true);

if (isset($apiResponse['type']) && $apiResponse['type'] === 'SUCCESS') {
    // Actualizar los datos de la sesión
    $_SESSION['nombre'] = $full_name;
    $_SESSION['usuario'] = $user_name;

    echo json_encode([
        "response" => "SUCCESS",
        "message" => "Usuario actualizado y sesión sincronizada"
    ]);
} else {
    echo json_encode([
        "response" => "ERROR",
        "message" => $apiResponse['message']
    ]);
}
