<?php

// Obtener el hostname del servidor actual
$hostname = $_SERVER['HTTP_HOST'];

// Definir el entorno basado en el hostname
if ($hostname === 'grupouribe.local') {
    // Ambiente de desarrollo
    define('ENVIRONMENT', 'dev');

    // Configuración para desarrollo
    define('BASE_URL', 'http://grupouribe.local/services');
    define('API_EP_LOGIN', '/login.php');
    define('TOKEN', '12345');
} else {
    // Ambiente de producción
    define('ENVIRONMENT', 'prod');

    // Configuración para producción
    define('BASE_URL', 'https://grupouribe.org/services');
    define('API_EP_LOGIN', '/login.php');
    define('TOKEN', '12345');
}

const DB_CONFIG = [
    'dev' => [
        'titulo' => 'Caja chica',
        'titulo2' => 'CC',
        'server' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'db' => 'grupour1_caja_chica',
        'url' => 'https://grupouribe.org/caja_chica/login.php',
    ],
    'prod' => [
        'titulo' => 'Caja chica',
        'titulo2' => 'CC',
        'server' => 'localhost',
        'user' => 'grupour1_alexis92',
        'pass' => 'gruas.uribe.22',
        'db' => 'grupour1_caja_chica',
        'url' => 'https://grupouribe.org/caja_chica/login.php',
    ]
];
