<?php
// Capturar el valor de 'tabla' pasado por la URL
$tabla = isset($_GET['tabla']) ? $_GET['tabla'] : 'caja';

// Vista tabla_servicios
require 'serverside.php';

// Tomar decisiones basadas en el valor de 'tabla'
if ($tabla == 'caja') {
    $table_data->get('vista_caja', 'id_caja', array(
        'id_caja',
        'fecha',
        'cargado',
        'area',
        'empresa',
        'autoriza',
        'folio',
        'tipo_folio',
        'tipo_ingreso',
        'tipo_gasto',
        'concepto',
        'entrega',
        'recibe',
        'comprobante',
        'unidad',
        'razon_social',
        'ingreso',
        'egreso',
        'saldo'
    ));
}
