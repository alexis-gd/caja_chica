<?php
require_once '../../config/conexion.php';
$opcion = $_POST['opcion'];
switch ($opcion) {
    case 'getModelGeneric':
        echo getModelGeneric();
        break;
    case 'getPettyCashDetails':
        echo getPettyCashDetails();
        break;
    case 'getVoucher':
        echo getVoucher();
        break;
    case 'getFileType':
        echo getFileType();
        break;
    case 'getVoucherList':
        echo getVoucherList();
        break;
    case 'getCatalogData':
        echo getCatalogData();
        break;
    case 'getDashCaja':
        echo getDashCaja();
        break;
    case 'getDashMonthly':
        echo getDashMonthly();
        break;
    case 'getChartData':
        echo getChartData();
        break;
    default:
        echo 'Not Found';
        break;
}
function getModelGeneric()
{
    $conexion     = conectar();
    $option_value = $_POST['option_value'];
    $tablas_permitidas = [
        'modelo_cargado', 'modelo_area', 'modelo_empresa', 'modelo_autoriza',
        'modelo_tipo_folio', 'modelo_tipo_ingreso', 'modelo_tipo_gasto',
        'modelo_entrega', 'modelo_recibe', 'modelo_comprobante', 'modelo_unidad',
        'modelo_razon_social', 'caja_archivos'
    ];
    if (!in_array($option_value, $tablas_permitidas, true)) {
        echo json_encode(['type' => 'ERROR', 'message' => 'Tabla no permitida.']);
        return;
    }

    $option  = '<option value="">Selecciona una opción</option>';
    $option2 = '<option value="">Escribe una opción</option>';

    $sql_store = "SELECT * FROM $option_value WHERE band_eliminar = 1 ORDER BY nombre ASC";

    $stmt = $conexion->prepare($sql_store);
    if ($stmt->execute()) {
        $rows = $stmt->fetchAll();

        if (count($rows) == 0) {
            $response = [
                'type'     => 'SUCCESS',
                'action'   => 'CONTINUE',
                'response' => $option2,
                'message'  => 'Opciones cargadas correctamente.'
            ];
        } else {
            foreach ($rows as $fila) {
                $selected = ($option_value === $fila['id']) ? 'selected' : '';
                $option .= "<option value='{$fila['id']}' $selected>{$fila['nombre']}</option>";
            }
            $response = [
                'type'     => 'SUCCESS',
                'action'   => 'CONTINUE',
                'response' => $option,
                'message'  => 'Opciones cargadas correctamente.'
            ];
        }
    } else {
        $response = [
            'type'    => 'ERROR',
            'message' => 'Error al ejecutar la consulta.'
        ];
    }

    echo json_encode($response);
}
function getPettyCashDetails()
{
    $vehicle_id = (int)$_POST['option_value'];
    $conexion   = conectar();

    $stmt = $conexion->prepare("SELECT * FROM caja WHERE id_caja = ?");
    $stmt->execute([$vehicle_id]);
    $cash_data = $stmt->fetch();

    $response = array(
        'id_caja'        => $cash_data['id_caja'],
        'fecha'          => $cash_data['fecha'],
        'id_cargado'     => $cash_data['id_cargado'],
        'id_area'        => $cash_data['id_area'],
        'id_empresa'     => $cash_data['id_empresa'],
        'id_autoriza'    => $cash_data['id_autoriza'],
        'folio'          => $cash_data['folio'],
        'id_folio'       => $cash_data['id_folio'],
        'id_tipo_ingreso'=> $cash_data['id_tipo_ingreso'],
        'id_tipo_gasto'  => $cash_data['id_tipo_gasto'],
        'concepto'       => $cash_data['concepto'],
        'id_entrega'     => $cash_data['id_entrega'],
        'id_recibe'      => $cash_data['id_recibe'],
        'id_comprobante' => $cash_data['id_comprobante'],
        'id_unidad'      => $cash_data['id_unidad'],
        'id_razon_social'=> $cash_data['id_razon_social'],
        'ingreso'        => $cash_data['ingreso'],
        'egreso'         => $cash_data['egreso'],
        'saldo'          => $cash_data['saldo'],
        'editado'        => $cash_data['editado'],
        'creado_por'     => $cash_data['creado_por'],
        'creado'         => $cash_data['creado'],
        'band_eliminar'  => $cash_data['band_eliminar']
    );

    echo json_encode($response);
}
function getVoucher()
{
    $conexion     = conectar();
    $option_value = (int)$_POST['option_value'];

    $stmt = $conexion->prepare("SELECT id_comprobante FROM caja WHERE id_caja = ?");
    $stmt->execute([$option_value]);
    $fila = $stmt->fetch();

    if (!$fila) {
        echo json_encode(['type' => 'ERROR', 'message' => 'Sin Resultados.']);
        return;
    }

    $id_comprobante = $fila['id_comprobante'];

    $stmt_modelo = $conexion->prepare("SELECT id, nombre FROM modelo_comprobante");
    $stmt_modelo->execute();
    $rows_modelo = $stmt_modelo->fetchAll();

    if (count($rows_modelo) == 0) {
        echo json_encode(['type' => 'ERROR', 'message' => 'Sin Resultados en modelo_comprobante.']);
        return;
    }

    $option = '<option value="">Selecciona una opción</option>';
    foreach ($rows_modelo as $fila_modelo) {
        $selected = ($id_comprobante == $fila_modelo['id']) ? 'selected' : '';
        $option .= "<option value='{$fila_modelo['id']}' $selected>{$fila_modelo['nombre']}</option>";
    }

    echo json_encode([
        'type'     => 'SUCCESS',
        'action'   => 'CONTINUE',
        'response' => $option,
        'message'  => 'Opciones cargadas correctamente.'
    ]);
}
function getFileType()
{
    $conexion     = conectar();
    $option_value = $_POST['option_value'];

    $option  = '<option value="">Selecciona una opción</option>';
    $option2 = '<option value="">Escribe una opción</option>';

    $stmt = $conexion->prepare("SELECT * FROM modelo_archivo WHERE band_eliminar = 1 ORDER BY nombre ASC");
    $stmt->execute();
    $rows = $stmt->fetchAll();

    if (count($rows) == 0) {
        echo json_encode([
            'type'     => 'SUCCESS',
            'action'   => 'CONTINUE',
            'response' => $option2,
            'message'  => 'Sin Resultados.'
        ]);
        return;
    }

    foreach ($rows as $fila) {
        $selected = ($option_value == $fila['id']) ? 'selected' : '';
        $option .= "<option value='{$fila['id']}' $selected>{$fila['nombre']}</option>";
    }

    echo json_encode([
        'type'     => 'SUCCESS',
        'action'   => 'CONTINUE',
        'response' => $option,
        'message'  => 'Opciones cargadas correctamente.'
    ]);
}
function getVoucherList()
{
    $option_value = (int)$_POST['option_value'];
    $conexion     = conectar();

    $stmt = $conexion->prepare("
        SELECT c.id_comprobante, mc.nombre AS comprobante_nombre
        FROM caja c
        LEFT JOIN modelo_comprobante mc ON c.id_comprobante = mc.id
        WHERE c.id_caja = ?
    ");
    $stmt->execute([$option_value]);
    $row = $stmt->fetch();

    if (!$row) {
        echo json_encode(['type' => 'ERROR', 'message' => 'Sin Resultados.']);
        return;
    }

    $archivosStmt = $conexion->prepare("
        SELECT id, id_caja, file_name, file_path, comments, uploaded_at
        FROM caja_archivos
        WHERE id_caja = ?
    ");
    $archivosStmt->execute([$option_value]);
    $archivosRows = $archivosStmt->fetchAll();

    $archivos = [];
    foreach ($archivosRows as $archivo) {
        $archivos[] = [
            'id_caja'           => $archivo['id'],
            'fecha'             => $archivo['uploaded_at'],
            'comments'          => $archivo['comments'],
            'id_comprobante'    => $row['id_comprobante'],
            'comprobante_nombre'=> $row['comprobante_nombre'],
            'file_name'         => $archivo['file_name'],
            'file_path'         => $archivo['file_path']
        ];
    }

    echo json_encode([
        'type'     => 'SUCCESS',
        'action'   => 'CONTINUE',
        'response' => $archivos,
        'message'  => 'Comprobante obtenido correctamente'
    ]);
}
function getCatalogData()
{
    $conexion = conectar();
    $modelo   = $_POST['model'];

    $response = array();

    try {
        $tablas_permitidas = [
            'modelo_cargado', 'modelo_area', 'modelo_empresa', 'modelo_autoriza',
            'modelo_tipo_folio', 'modelo_tipo_ingreso', 'modelo_tipo_gasto',
            'modelo_entrega', 'modelo_recibe', 'modelo_comprobante', 'modelo_unidad',
            'modelo_razon_social', 'caja_archivos'
        ];
        if (!in_array($modelo, $tablas_permitidas, true)) {
            throw new Exception('Tabla no permitida.');
        }

        $sql  = "SELECT id, nombre FROM $modelo WHERE band_eliminar = 1";
        $stmt = $conexion->query($sql);
        $data = $stmt->fetchAll();

        $response['type']    = 'SUCCESS';
        $response['action']  = 'CONTINUE';
        $response['data']    = $data;
        $response['message'] = 'Datos obtenidos correctamente.';
    } catch (Exception $e) {
        $response['type']    = 'ERROR';
        $response['action']  = 'CANCEL';
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
}
function getDashCaja()
{
    $conexion = conectar();

    $stmt = $conexion->query("SELECT COUNT(*) as total FROM caja WHERE band_eliminar = 1");
    $fila = $stmt->fetch();

    echo json_encode([$fila['total']]);
}
function getDashMonthly()
{
    $conexion     = conectar();
    $option_value = $_POST['option_value'];

    $primer_dia_mes = date('Y-m-01');
    $ultimo_dia_mes = date('Y-m-t');

    $stmt = $conexion->prepare("
        SELECT
            SUM(ingreso) AS total_ingreso,
            SUM(egreso)  AS total_egreso
        FROM caja
        WHERE band_eliminar = 1
          AND fecha BETWEEN ? AND ?
    ");
    $stmt->execute([$primer_dia_mes, $ultimo_dia_mes]);
    $fila = $stmt->fetch();

    $total_ingreso = isset($fila['total_ingreso']) ? $fila['total_ingreso'] : 0;
    $total_egreso  = isset($fila['total_egreso'])  ? $fila['total_egreso']  : 0;
    $saldo         = $total_ingreso - $total_egreso;

    $total_ingreso_formateado = '$' . number_format($total_ingreso, 2);
    $total_egreso_formateado  = '$' . number_format($total_egreso,  2);
    $saldo_formateado         = '$' . number_format($saldo,         2);

    $response = '';
    if ($option_value === '1') $response = $total_ingreso_formateado;
    if ($option_value === '2') $response = $total_egreso_formateado;
    if ($option_value === '3') $response = $saldo_formateado;

    echo json_encode($response);
}
function getChartData()
{
    $conexion = conectar();

    $stmt = $conexion->query("
        SELECT
            MONTH(fecha)     as mes,
            MONTHNAME(fecha) as nombre_mes,
            SUM(ingreso)     AS total_ingreso,
            SUM(egreso)      AS total_egreso
        FROM caja
        WHERE band_eliminar = 1
          AND YEAR(fecha) = YEAR(CURDATE())
        GROUP BY MONTH(fecha)
        ORDER BY MONTH(fecha)
    ");

    $ingresos = [];
    $egresos  = [];
    $labels   = [];

    while ($fila = $stmt->fetch()) {
        $labels[]   = strtoupper(substr($fila['nombre_mes'], 0, 3));
        $ingresos[] = (int)$fila['total_ingreso'];
        $egresos[]  = (int)$fila['total_egreso'];
    }

    echo json_encode([
        'type'          => 'SUCCESS',
        'action'        => 'CONTINUE',
        'labels'        => $labels,
        'data_ingresos' => $ingresos,
        'data_egresos'  => $egresos
    ]);
}
