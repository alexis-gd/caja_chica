<?php
/**
 * whisper.php — Transcripción de audio via Groq Whisper
 * Recibe un blob de audio (webm/wav) y retorna el texto transcrito.
 */

require_once __DIR__ . '/../../config/sesiones.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/conexion.php';

usuario_autenticado();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['audio'])) {
    echo json_encode(array('type' => 'ERROR', 'message' => 'Audio requerido.'));
    exit;
}

$file = $_FILES['audio'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(array('type' => 'ERROR', 'message' => 'Error al recibir audio.'));
    exit;
}

// Validar tamaño máximo 25MB (límite Groq)
if ($file['size'] > 25 * 1024 * 1024) {
    echo json_encode(array('type' => 'ERROR', 'message' => 'Audio demasiado largo.'));
    exit;
}

// Cargar nombres del catálogo para el initial_prompt
$conexion       = conectar();
$nombres_recibe = $conexion->query(
    "SELECT nombre FROM modelo_chica_recibe  WHERE band_eliminar = 1 ORDER BY nombre"
)->fetchAll(PDO::FETCH_COLUMN);
$nombres_cargado = $conexion->query(
    "SELECT nombre FROM modelo_chica_cargado WHERE band_eliminar = 1 ORDER BY nombre"
)->fetchAll(PDO::FETCH_COLUMN);

$todos_nombres = array_unique(array_merge($nombres_recibe, $nombres_cargado));
$prefix  = 'Caja chica. Personas: ';
$suffix  = '. Términos: gruas, inversión, diesel, Maravilla, Santa Fe.';
$max_nombres_len = 896 - strlen($prefix) - strlen($suffix);
$nombres_str = implode(', ', $todos_nombres);
if (strlen($nombres_str) > $max_nombres_len) {
    $nombres_str = substr($nombres_str, 0, $max_nombres_len);
    // cortar en la última coma completa para no dejar nombre partido
    $nombres_str = substr($nombres_str, 0, strrpos($nombres_str, ','));
}
$initial_prompt = $prefix . $nombres_str . $suffix;

// Llamar Groq Whisper
$tmpPath  = $file['tmp_name'];
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $tmpPath);
finfo_close($finfo);

$valid_mimes = array(
    'audio/webm' => 'webm', 'audio/wav'  => 'wav',  'audio/x-wav' => 'wav',
    'audio/ogg'  => 'ogg',  'audio/mp4'  => 'mp4',  'audio/mpeg'  => 'mp3',
    'audio/mpga' => 'mp3',  'audio/m4a'  => 'm4a',  'video/webm'  => 'webm',
);
if (!isset($valid_mimes[$mimeType])) {
    echo json_encode(array('type' => 'ERROR', 'message' => 'Formato de audio no soportado.'));
    exit;
}
$ext = $valid_mimes[$mimeType];

$curlFile = new CURLFile($tmpPath, $mimeType, 'audio.' . $ext);

$ch = curl_init('https://api.groq.com/openai/v1/audio/transcriptions');
curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => array(
        'file'            => $curlFile,
        'model'           => 'whisper-large-v3-turbo',
        'language'        => 'es',
        'response_format' => 'json',
        'prompt'          => $initial_prompt,
    ),
    CURLOPT_HTTPHEADER     => array(
        'Authorization: Bearer ' . GROQ_API_KEY,
    ),
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => true,
));

$result  = curl_exec($ch);
$errCode = curl_errno($ch);
curl_close($ch);

if ($errCode || $result === false) {
    echo json_encode(array('type' => 'ERROR', 'message' => 'No se pudo conectar con Groq Whisper.'));
    exit;
}

$data = json_decode($result, true);

if (!isset($data['text'])) {
    $msg = isset($data['error']['message']) ? $data['error']['message'] : 'Respuesta inesperada de Groq Whisper.';
    echo json_encode(array('type' => 'ERROR', 'message' => $msg));
    exit;
}

echo json_encode(array(
    'type' => 'SUCCESS',
    'text' => trim($data['text']),
));
