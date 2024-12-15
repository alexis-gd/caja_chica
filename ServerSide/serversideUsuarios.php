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
        'cargado',         // Cambio: id_cargado -> cargado (nombre de modelo_cargado)
        'area',            // Cambio: id_area -> area (nombre de modelo_area)
        'tipo_gasto',      // Cambio: id_tipo_gasto -> tipo_gasto (nombre de modelo_tipo_gasto)
        'concepto',
        'recibe',          // Cambio: id_recibe -> recibe (nombre de modelo_recibe)
        'unidad',          // Cambio: id_unidad -> unidad (nombre de modelo_unidad)
        'comprobante',     // Cambio: id_comprobante -> comprobante (nombre de modelo_comprobante)
        'razon_social',
        'ingreso',
        'egreso',
        'saldo',
        // 'folio',
        // 'empresa',         // Cambio: id_empresa -> empresa (nombre de modelo_empresa)
        // 'entrega',         // Cambio: id_entrega -> entrega (nombre de modelo_entrega)
        // 'tipo_ingreso',    // Cambio: id_tipo_ingreso -> tipo_ingreso (nombre de modelo_tipo_ingreso)
        // 'autoriza',        // Cambio: id_autoriza -> autoriza (nombre de modelo_autoriza)
        // 'proveedor',       // Cambio: id_proveedor -> proveedor (nombre de modelo_proveedor)
        // 'operador',        // Cambio: id_operador -> operador (nombre de modelo_operador)
        // 'factura',         // Cambio: id_factura -> factura (nombre de modelo_factura)
    ));
}
