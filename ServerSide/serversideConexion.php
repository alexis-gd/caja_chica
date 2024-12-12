<?php
require('../functions/config.php'); // Importa las configuraciones globales
$config = DB_CONFIG[ENVIRONMENT];

define("HOST_SS", $config['server']);
define("USER_SS", $config['user']);
define("PASSWORD_SS", $config['pass']);
define("DATABASE_SS", $config['db']);
?>