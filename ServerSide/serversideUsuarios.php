<?php
// Capturar el valor de 'tabla' pasado por la URL
$tabla = isset($_GET['tabla']) ? $_GET['tabla'] : 'vehiculos';

// Vista tabla_servicios
require 'serverside.php';

// Tomar decisiones basadas en el valor de 'tabla'
if ($tabla == 'vehiculos') {
    $table_data->get('vista_vehiculos', 'id_vehiculo', array('id_vehiculo', 'marca', 'modelo', 'ano', 'color', 'serie', 'motor', 'pedimento', 'propietario', 'placa', 'observaciones', 'fecha_de_entrega'));
}