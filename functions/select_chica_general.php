<?php
// Deshabilitar la visualización de errores en el navegador
// ini_set('display_errors', 0);

// Configurar qué tipo de errores se deben registrar (en este caso, se omiten notices y warnings)
// error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require_once 'conexion.php';
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
    $conexion = conectar();
    $option_value = $_POST['option_value'];

    // Inicializar la variable $option
    $option = '<option value="">Selecciona una opción</option>';
    $option2 = '<option value="">Escribe una opción</option>';

    // Preparar la consulta SQL con un marcador de posición para el nombre de la tabla
    $sql_store = "SELECT * FROM $option_value WHERE band_eliminar = 1 ORDER BY nombre ASC";

    // Intentar preparar la consulta
    if ($stmt = mysqli_prepare($conexion, $sql_store)) {
        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            // Obtener los resultados
            $resultado = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($resultado) == 0) {
                // Si no hay resultados, devolver la opción vacía
                $response = [
                    'type' => 'SUCCESS',
                    'action' => 'CONTINUE',
                    'response' => $option2,
                    'message' => 'Opciones cargadas correctamente.'
                ];
            } else {
                // Recorrer los resultados y generar las opciones para el select
                while ($fila = mysqli_fetch_array($resultado)) {
                    $selected = ($option_value === $fila['id']) ? 'selected' : '';
                    $option .= "<option value='$fila[id]' $selected>$fila[nombre]</option>";
                }

                // Retornar la respuesta exitosa con las opciones
                $response = [
                    'type' => 'SUCCESS',
                    'action' => 'CONTINUE',
                    'response' => $option,
                    'message' => 'Opciones cargadas correctamente.'
                ];
            }

            // Cerrar la sentencia
            mysqli_stmt_close($stmt);
        } else {
            // En caso de error al ejecutar la consulta
            $response = [
                'type' => 'ERROR',
                'message' => 'Error al ejecutar la consulta: ' . mysqli_error($conexion)
            ];
        }
    } else {
        // Si hubo un error al preparar la consulta
        $response = [
            'type' => 'ERROR',
            'message' => 'Error al preparar la consulta SQL.'
        ];
    }

    // Cerrar la conexión
    mysqli_close($conexion);

    // Enviar la respuesta en formato JSON
    echo json_encode($response);
}
function getPettyCashDetails()
{
    $vehicle_id = $_POST['option_value'];  // En este caso, esto sería el ID de la caja.
    $conexion = conectar();
    $conexion->set_charset('utf8');

    // Consulta para obtener los detalles de la caja_chica específica
    $query = "SELECT * FROM caja_chica WHERE id_caja = $vehicle_id";
    $result = mysqli_query($conexion, $query);
    $cash_data = mysqli_fetch_assoc($result);

    $response = array(
        'id_caja' => $cash_data['id_caja'],
        'fecha' => $cash_data['fecha'],
        'id_cargado' => $cash_data['id_cargado'],
        'id_area' => $cash_data['id_area'],
        'id_tipo_gasto' => $cash_data['id_tipo_gasto'],
        'concepto' => $cash_data['concepto'],
        'id_recibe' => $cash_data['id_recibe'],
        'id_unidad' => $cash_data['id_unidad'],
        'id_comprobante' => $cash_data['id_comprobante'],
        'id_razon_social' => $cash_data['id_razon_social'],
        'ingreso' => $cash_data['ingreso'],
        'egreso' => $cash_data['egreso'],
        'saldo' => $cash_data['saldo'],
        'id_empresa' => $cash_data['id_empresa'],
        'id_entrega' => $cash_data['id_entrega'],
        'id_tipo_ingreso' => $cash_data['id_tipo_ingreso'],
        'id_autoriza' => $cash_data['id_autoriza'],
        'id_proveedor' => $cash_data['id_proveedor'],
        'id_operador' => $cash_data['id_operador'],
        'id_factura' => $cash_data['id_factura'],
        'editado' => $cash_data['editado'],
        'creado_por' => $cash_data['creado_por'],
        'creado' => $cash_data['creado'],
        'band_eliminar' => $cash_data['band_eliminar'],

        // Puedes incluir más detalles como los accesos, dependiendo de los requerimientos
        // 'access' => array(
        //     'payroll' => $access_data['payroll'] ? 1 : 0,
        //     'crane_management' => $access_data['crane_management'] ? 1 : 0,
        //     'accidents_lch' => $access_data['accidents_lch'] ? 1 : 0,
        //     'accidents_acayucan' => $access_data['accidents_acayucan'] ? 1 : 0,
        //     'hotels_san_cristobal' => $access_data['hotels_san_cristobal'] ? 1 : 0,
        //     'hotels_molocan' => $access_data['hotels_molocan'] ? 1 : 0,
        //     'hotels_santa_fe' => $access_data['hotels_santa_fe'] ? 1 : 0,
        //     'hotels_pinolillo' => $access_data['hotels_pinolillo'] ? 1 : 0,
        //     'inventory' => $access_data['inventory'] ? 1 : 0
        // )
    );

    echo json_encode($response);
    mysqli_close($conexion);
}
function getVoucher()
{
    $conexion = conectar();
    $option_value = $_POST['option_value'];

    $sql_store = "SELECT id_comprobante FROM caja_chica WHERE id_caja = $option_value";
    $resultado = mysqli_query($conexion, $sql_store);

    if (mysqli_num_rows($resultado) == 0) {
        $response = [
            'type' => 'ERROR',
            'message' => 'Sin Resultados.'
        ];
        echo json_encode($response);
        mysqli_close($conexion);
        return;
    }

    $fila = mysqli_fetch_array($resultado);
    $id_comprobante = $fila['id_comprobante'];

    // Segunda consulta para obtener todos los nombres desde modelo_chica_comprobante
    $sql_modelo = "SELECT id, nombre FROM modelo_chica_comprobante";
    $resultado_modelo = mysqli_query($conexion, $sql_modelo);

    if (mysqli_num_rows($resultado_modelo) == 0) {
        $response = [
            'type' => 'ERROR',
            'message' => 'Sin Resultados en modelo_chica_comprobante.'
        ];
        echo json_encode($response);
        mysqli_close($conexion);
        return;
    }

    $option = '<option value="">Selecciona una opción</option>';
    while ($fila_modelo = mysqli_fetch_array($resultado_modelo)) {
        $selected = ($id_comprobante == $fila_modelo['id']) ? 'selected' : '';
        $option .= "<option value='{$fila_modelo['id']}' $selected>{$fila_modelo['nombre']}</option>";
    }

    $response = [
        'type' => 'SUCCESS',
        'action' => 'CONTINUE',
        'response' => $option,
        'message' => 'Opciones cargadas correctamente.'
    ];

    echo json_encode($response);
    mysqli_close($conexion);
}
function getFileType()
{
    $conexion = conectar();
    $option_value = $_POST['option_value'];

    $option = '<option value="">Selecciona una opción</option>';
    $option2 = '<option value="">Escribe una opción</option>';

    $sql_store = "SELECT * FROM modelo_chica_archivo WHERE band_eliminar = 1 ORDER BY nombre ASC";
    $resultado = mysqli_query($conexion, $sql_store);

    if (mysqli_num_rows($resultado) == 0) {
        $response = [
            'type' => 'SUCCESS',
            'action' => 'CONTINUE',
            'response' => $option2,
            'message' => 'Sin Resultados.'
        ];
        echo json_encode($response);
        mysqli_close($conexion);
        return;
    }

    while ($fila = mysqli_fetch_array($resultado)) {
        $selected = ($option_value == $fila['id']) ? 'selected' : '';
        $option .= "<option value='{$fila['id']}' $selected>{$fila['nombre']}</option>";
    }

    $response = [
        'type' => 'SUCCESS',
        'action' => 'CONTINUE',
        'response' => $option,
        'message' => 'Opciones cargadas correctamente.'
    ];

    echo json_encode($response);
    mysqli_close($conexion);
}
function getVoucherList()
{
    $option_value = $_POST['option_value'];
    $conexion = conectar();
    $conexion->set_charset('utf8');

    // Obtener el comprobante asociado al id_caja
    $stmt = $conexion->prepare("
        SELECT c.id_comprobante, mc.nombre AS comprobante_nombre
        FROM caja_chica c
        LEFT JOIN modelo_chica_comprobante mc ON c.id_comprobante = mc.id
        WHERE c.id_caja = ?
    ");
    $stmt->bind_param("i", $option_value);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode([
            'type' => 'ERROR',
            'message' => 'Sin Resultados.'
        ]);
        $stmt->close();
        $conexion->close();
        return;
    }

    while ($row = $result->fetch_assoc()) {

        // Obtener archivos asociados al comprobante
        $archivosStmt = $conexion->prepare("
            SELECT id, id_caja, file_name, file_path, comments, uploaded_at
            FROM caja_chica_archivos
            WHERE id_caja = ?
        ");
        $archivosStmt->bind_param("i", $option_value);
        $archivosStmt->execute();
        $archivosResult = $archivosStmt->get_result();

        $archivos = [];
        while ($archivo = $archivosResult->fetch_assoc()) {
            $archivos[] = [
                'id_caja' => $archivo['id'],
                'fecha' => $archivo['uploaded_at'],
                'comments' => $archivo['comments'],
                'id_comprobante' => $row['id_comprobante'],
                'comprobante_nombre' => $row['comprobante_nombre'],
                'file_name' => $archivo['file_name'],
                'file_path' => $archivo['file_path']
            ];
        }
        $archivosStmt->close();
    }

    $stmt->close();
    $conexion->close();

    echo json_encode([
        'type' => 'SUCCESS',
        'action' => 'CONTINUE',
        'response' => $archivos,
        'message' => 'Comprobante obtenido correctamente'
    ]);
}
function getCatalogData()
{
    $conexion = conectar();
    $modelo = $_POST['model'];

    $response = array();

    try {
        // Verificar si el modelo está vacío
        if (empty($modelo)) {
            throw new Exception('El modelo no puede estar vacío.');
        }

        // Preparar la consulta
        $sql = "SELECT id, nombre FROM $modelo WHERE band_eliminar = 1";
        $resultado = mysqli_query($conexion, $sql);

        if (!$resultado) {
            throw new Exception('Error al ejecutar la consulta: ' . mysqli_error($conexion));
        }

        $data = array();
        while ($row = mysqli_fetch_assoc($resultado)) {
            $data[] = $row;
        }

        $response['type'] = 'SUCCESS';
        $response['action'] = 'CONTINUE';
        $response['data'] = $data;
        $response['message'] = 'Datos obtenidos correctamente.';
    } catch (Exception $e) {
        $response['type'] = 'ERROR';
        $response['action'] = 'CANCEL';
        $response['message'] = $e->getMessage();
    }

    mysqli_close($conexion);

    echo json_encode($response);
}
function getDashCaja()
{
    $conexion = conectar();
    $conexion->set_charset('utf8');

    $sql_categories = "SELECT COUNT(*) as total FROM caja_chica WHERE band_eliminar = 1";
    $res_categories = mysqli_query($conexion, $sql_categories);
    if (mysqli_num_rows($res_categories) == 0) {
        die("Sin Resultados." . $sql_categories);
    }
    $fila = mysqli_fetch_assoc($res_categories);
    $response = $fila['total'];

    $data = array("$response");
    echo json_encode($data);
    mysqli_close($conexion);
}
function getDashMonthly()
{
    $conexion = conectar();
    $conexion->set_charset('utf8');

    $response = '';
    $option_value = $_POST['option_value'];

    // Obtener el primer y último día del mes actual
    $primer_dia_mes = date('Y-m-01');
    $ultimo_dia_mes = date('Y-m-t');

    // Consulta para obtener la sumatoria del saldo del mes actual
    $sql = "
        SELECT 
            SUM(ingreso) AS total_ingreso, 
            SUM(egreso) AS total_egreso 
        FROM 
            caja_chica 
        WHERE 
            band_eliminar = 1 
            AND fecha BETWEEN '$primer_dia_mes' AND '$ultimo_dia_mes'
    ";

    $resultado = mysqli_query($conexion, $sql);
    if (!$resultado) {
        die("Error en la consulta: " . mysqli_error($conexion));
    }

    $fila = mysqli_fetch_assoc($resultado);
    $total_ingreso = isset($fila['total_ingreso']) ? $fila['total_ingreso'] : 0;
    $total_egreso = isset($fila['total_egreso']) ? $fila['total_egreso'] : 0;
    $saldo = $total_ingreso - $total_egreso;

    // Formatear las cantidades
    $total_ingreso_formateado = '$' . number_format($total_ingreso, 2);
    $total_egreso_formateado = '$' . number_format($total_egreso, 2);
    $saldo_formateado = '$' . number_format($saldo, 2);

    if ($option_value === '1') {
        $response = $total_ingreso_formateado;
    }
    if ($option_value === '2') {
        $response = $total_egreso_formateado;
    }
    if ($option_value === '3') {
        $response = $saldo_formateado;
    }
    // $response = array(
    //     'total_ingreso' => $total_ingreso,
    //     'total_egreso' => $total_egreso,
    //     'saldo' => $saldo
    // );

    echo json_encode($response);
    mysqli_close($conexion);
}
function getChartData()
{
    $conexion = conectar();
    $conexion->set_charset('utf8');

    // Consulta SQL para obtener ingresos y egresos por mes del año en curso
    $sql = "
        SELECT 
            MONTH(fecha) as mes, 
            MONTHNAME(fecha) as nombre_mes,
            SUM(ingreso) AS total_ingreso, 
            SUM(egreso) AS total_egreso 
        FROM 
            caja_chica 
        WHERE 
            band_eliminar = 1 
            AND YEAR(fecha) = YEAR(CURDATE())
        GROUP BY 
            MONTH(fecha)
        ORDER BY 
            MONTH(fecha)
    ";

    $resultado = mysqli_query($conexion, $sql);
    if (!$resultado) {
        die("Error en la consulta: " . mysqli_error($conexion));
    }

    $ingresos = [];
    $egresos = [];
    $labels = [];

    while ($fila = mysqli_fetch_assoc($resultado)) {
        $labels[] = strtoupper(substr($fila['nombre_mes'], 0, 3)); // Primeras tres letras del mes en mayúsculas
        $ingresos[] = (int)$fila['total_ingreso'];
        $egresos[] = (int)$fila['total_egreso'];
    }

    // Estructura de datos a devolver
    $data = [
        'type' => 'SUCCESS',
        'action' => 'CONTINUE',
        'labels' => $labels,
        'data_ingresos' => $ingresos,
        'data_egresos' => $egresos
    ];

    echo json_encode($data);
    mysqli_close($conexion);
}
