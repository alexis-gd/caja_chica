<?php
// conexion.php
function conectar() {
    require_once __DIR__ . '/config.php';
    $config = DB_CONFIG[ENVIRONMENT];

    $dsn = 'mysql:host=' . $config['server'] . ';dbname=' . $config['db'] . ';charset=utf8mb4';

    try {
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]));
    }
}
