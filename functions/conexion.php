<?php
// conexion.php
function conectar() {
    require('config.php'); // Importa las configuraciones globales
    $config = DB_CONFIG[ENVIRONMENT];

    $server = $config['server'];
    $user = $config['user'];
    $pass = $config['pass'];
    $db = $config['db'];
    
    // Crear la conexión
    $con = new mysqli($server, $user, $pass, $db); 
    $con->set_charset('utf8');
    // Verificar si la conexión falla
    if ($con->connect_error) {
        printf("Error de conexión: %s\n", $con->connect_error);
        exit();
    } else {
        return $con;
    }
}
