<?php
// Deshabilitar la visualización de errores en el navegador
ini_set('display_errors', 0);

// Configurar qué tipo de errores se deben registrar (en este caso, se omiten notices y warnings)
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require_once 'conexion.php';
$opcion = $_POST['opcion'];
switch ($opcion) {
    case 'getModelGeneric':
        echo getModelGeneric();
        break;
    case 'getBrand':
        echo getBrand();
        break;
    case 'getModel':
        echo getModel();
        break;
    case 'getColor':
        echo getColor();
        break;
    case 'getOwner':
        echo getOwner();
        break;
    case 'getFileType':
        echo getFileType();
        break;
    case 'getVehicleDetails':
        echo getVehicleDetails();
        break;
    case 'getVehicleFiles':
        echo getVehicleFiles();
        break;
    case 'getVehicleHistory':
        echo getVehicleHistory();
        break;
    case 'getListFile':
        echo getListFile();
        break;
    case 'getHistoryReport':
        echo getHistoryReport();
        break;
    case 'getDashVehicle':
        echo getDashVehicle();
        break;
    case 'getDashVehicleFiles':
        echo getDashVehicleFiles();
        break;
    case 'getDashVehicleGiven':
        echo getDashVehicleGiven();
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
                    'type' => 'ERROR',
                    'message' => 'No se encontraron resultados.'
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
function getBrand()
{
    $conexion = conectar();
    $option_value = $_POST['option_value'];

    $sql_store = "SELECT * FROM modelo_marca WHERE band_eliminar = 1 ORDER BY nombre ASC";
    $resultado = mysqli_query($conexion, $sql_store);

    if (mysqli_num_rows($resultado) == 0) {
        die("Sin Resultados." . $resultado);
    }

    $option = '<option value="">Selecciona una opción</option>';
    while ($fila = mysqli_fetch_array($resultado)) {
        if ($option_value === $fila['id']) {
            $option .= "<option value='$fila[id]' selected>$fila[nombre]</option>";
        } else {
            $option .= "<option value='$fila[id]'>$fila[nombre]</option>";
        }
    }

    $data = array("$option");
    echo json_encode($data);
    mysqli_close($conexion);
}
function getModel()
{
    $conexion = conectar();
    $option_value = $_POST['option_value'];

    $sql_store = "SELECT * FROM modelo_modelo WHERE band_eliminar = 1 ORDER BY nombre ASC";
    $resultado = mysqli_query($conexion, $sql_store);

    if (mysqli_num_rows($resultado) == 0) {
        die("Sin Resultados." . $resultado);
    }

    $option = '<option value="">Selecciona una opción</option>';
    while ($fila = mysqli_fetch_array($resultado)) {
        if ($option_value === $fila['id']) {
            $option .= "<option value='$fila[id]' selected>$fila[nombre]</option>";
        } else {
            $option .= "<option value='$fila[id]'>$fila[nombre]</option>";
        }
    }

    $data = array("$option");
    echo json_encode($data);
    mysqli_close($conexion);
}
function getColor()
{
    $conexion = conectar();
    $option_value = $_POST['option_value'];

    $sql_store = "SELECT * FROM modelo_color WHERE band_eliminar = 1 ORDER BY nombre ASC";
    $resultado = mysqli_query($conexion, $sql_store);

    if (mysqli_num_rows($resultado) == 0) {
        die("Sin Resultados." . $resultado);
    }

    $option = '<option value="">Selecciona una opción</option>';
    while ($fila = mysqli_fetch_array($resultado)) {
        if ($option_value === $fila['id']) {
            $option .= "<option value='$fila[id]' selected>$fila[nombre]</option>";
        } else {
            $option .= "<option value='$fila[id]'>$fila[nombre]</option>";
        }
    }

    $data = array("$option");
    echo json_encode($data);
    mysqli_close($conexion);
}
function getOwner()
{
    $conexion = conectar();
    $option_value = $_POST['option_value'];

    $sql_store = "SELECT * FROM modelo_propietario WHERE band_eliminar = 1 ORDER BY nombre ASC";
    $resultado = mysqli_query($conexion, $sql_store);

    if (mysqli_num_rows($resultado) == 0) {
        die("Sin Resultados." . $resultado);
    }

    $option = '<option value="">Selecciona una opción</option>';
    while ($fila = mysqli_fetch_array($resultado)) {
        if ($option_value === $fila['id']) {
            $option .= "<option value='$fila[id]' selected>$fila[nombre]</option>";
        } else {
            $option .= "<option value='$fila[id]'>$fila[nombre]</option>";
        }
    }

    $data = array("$option");
    echo json_encode($data);
    mysqli_close($conexion);
}
function getFileType()
{
    $conexion = conectar();
    $option_value = $_POST['option_value'];

    $sql_store = "SELECT * FROM modelo_archivo WHERE band_eliminar = 1 ORDER BY nombre ASC";
    $resultado = mysqli_query($conexion, $sql_store);

    if (mysqli_num_rows($resultado) == 0) {
        die("Sin Resultados." . $resultado);
    }

    $option = '<option value="">Selecciona una opción</option>';
    while ($fila = mysqli_fetch_array($resultado)) {
        if ($option_value === $fila['id']) {
            $option .= "<option value='$fila[id]' selected>$fila[nombre]</option>";
        } else {
            $option .= "<option value='$fila[id]'>$fila[nombre]</option>";
        }
    }

    $data = array("$option");
    echo json_encode($data);
    mysqli_close($conexion);
}
function getVehicleDetails()
{
    $vehicle_id = $_POST['option_value'];
    $conexion = conectar();
    $conexion->set_charset('utf8');

    $query = "SELECT * FROM vehiculo WHERE id_vehiculo = $vehicle_id";
    $result = mysqli_query($conexion, $query);
    $user_data = mysqli_fetch_assoc($result);

    // Ahora se asume que hay una tabla user_access que contiene los accesos
    // $query_access = "SELECT * FROM user_access WHERE user_id = $user_id";
    // $result_access = mysqli_query($conexion, $query_access);
    // $access_data = mysqli_fetch_assoc($result_access);

    $response = array(
        'id_vehiculo' => $user_data['id_vehiculo'],
        'id_marca' => $user_data['id_marca'],
        'id_modelo' => $user_data['id_modelo'],
        'ano' => $user_data['ano'],
        'color' => $user_data['id_color'],
        'serie' => $user_data['serie'],
        'motor' => $user_data['motor'],
        'pedimento' => $user_data['pedimento'],
        'id_propietario' => $user_data['id_propietario'],
        'placa' => $user_data['placa'],
        'observaciones' => $user_data['observaciones'],
        'fecha_de_entrega' => $user_data['fecha_de_entrega'],

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
function getVehicleFiles()
{
    $vehicleId = $_POST['vehicleId'];
    $conexion = conectar();
    $conexion->set_charset('utf8');

    $stmt = $conexion->prepare("SELECT id, file_name, file_path, is_active, uploaded_at FROM vehiculo_archivos WHERE vehicle_id = ? ORDER BY uploaded_at DESC");
    $stmt->bind_param("i", $vehicleId);
    $stmt->execute();
    $result = $stmt->get_result();

    $files = [];
    while ($row = $result->fetch_assoc()) {
        $pathInfo = pathinfo($row['file_name']);
        $files[] = [
            'id' => $row['id'],
            'file_name' => $row['file_name'],
            'file_path' => $row['file_path'],
            'uploaded_at' => $row['uploaded_at'],
            'extension' => $pathInfo['extension'],
            'is_active' => $row['is_active']
        ];
    }

    $stmt->close();
    $conexion->close();

    echo json_encode($files);
}
function getVehicleHistory()
{
    $vehicleId = $_POST['vehicleId'];
    $conexion = conectar();
    $conexion->set_charset('utf8');

    // Obtener el historial del vehículo con el nombre del movimiento
    $stmt = $conexion->prepare("
        SELECT vh.id, vh.id_vehiculo, vh.observacion, vh.id_movimiento, vh.fecha_manual, mm.nombre AS movimiento_nombre, vh.creado
        FROM vehiculo_historial vh
        LEFT JOIN modelo_movimiento mm ON vh.id_movimiento = mm.id
        WHERE vh.id_vehiculo = ?
        ORDER BY vh.creado DESC
    ");
    $stmt->bind_param("i", $vehicleId);
    $stmt->execute();
    $result = $stmt->get_result();

    $history = [];
    while ($row = $result->fetch_assoc()) {
        $historialId = $row['id'];

        // Obtener archivos asociados al historial
        $archivosStmt = $conexion->prepare("
            SELECT vha.id, vha.id_archivo, va.vehicle_id, va.file_name, va.file_path
            FROM vehiculo_historial_archivos vha
            LEFT JOIN vehiculo_archivos va ON vha.id_archivo = va.id
            WHERE vha.id_vehiculo_historial = ?
        ");
        $archivosStmt->bind_param("i", $historialId);
        $archivosStmt->execute();
        $archivosResult = $archivosStmt->get_result();

        $archivos = [];
        while ($archivo = $archivosResult->fetch_assoc()) {
            $archivos[] = [
                'id_historial_archivo' => $archivo['id'],
                'vehicle_id' => $archivo['vehicle_id'],
                'file_name' => $archivo['file_name'],
                'file_path' => $archivo['file_path']
            ];
        }
        $archivosStmt->close();

        // Construir el historial con archivos y nombre de movimiento
        $history[] = [
            'id_vehiculo' => $row['id_vehiculo'],
            'id_vehiculo_historial' => $row['id'],
            'observacion' => $row['observacion'],
            'id_movimiento' => $row['id_movimiento'],
            'movimiento_nombre' => $row['movimiento_nombre'],
            'fecha_manual' => $row['fecha_manual'],
            'creado' => $row['creado'],
            'archivos' => $archivos
        ];
    }

    $stmt->close();
    $conexion->close();

    echo json_encode([
        'type' => 'SUCCESS',
        'action' => 'CONTINUE',
        'response' => $history,
        'message' => 'Historial obtenido correctamente'
    ]);
}
function getListFile()
{
    $vehicle_id = $_POST['option_value'];
    $conexion = conectar();
    $conexion->set_charset('utf8');

    $query = "SELECT * FROM vehiculo_archivos WHERE vehicle_id = $vehicle_id ORDER BY id DESC";
    $result = mysqli_query($conexion, $query);
    // $user_data = mysqli_fetch_assoc($result);

    $files = [];
    while ($row = $result->fetch_assoc()) {
        $files[] = [
            'id' => $row['id'],
            'vehicle_id' => $row['vehicle_id'],
            'file_name' => $row['file_name'],
            'file_path' => $row['file_path'],
            'type_file_id' => $row['type_file_id'],
            'is_active' => $row['is_active']
        ];
    }

    echo json_encode($files);
    mysqli_close($conexion);
}
function getHistoryReport()
{
    if (!isset($_POST['id_vehiculo_historial'])) {
        echo json_encode([
            'type' => 'ERROR',
            'message' => 'No se proporcionó el ID del historial del vehículo.'
        ]);
        return;
    }

    $id_vehiculo_historial = (int)$_POST['id_vehiculo_historial'];
    $conexion = conectar();
    $conexion->set_charset('utf8');

    try {
        // Obtener el historial del vehículo
        $stmt = $conexion->prepare("
            SELECT vh.id, vh.id_vehiculo, vh.observacion, vh.id_movimiento, vh.fecha_manual, vh.creado, mm.nombre AS movimiento_nombre
            FROM vehiculo_historial vh
            LEFT JOIN modelo_movimiento mm ON vh.id_movimiento = mm.id
            WHERE vh.id = ?
        ");
        $stmt->bind_param("i", $id_vehiculo_historial);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode([
                'type' => 'ERROR',
                'message' => 'No se encontró información para el ID proporcionado.'
            ]);
            $stmt->close();
            $conexion->close();
            return;
        }

        $history = [];
        $row = $result->fetch_assoc();

        // Obtener archivos asociados al historial
        $archivosStmt = $conexion->prepare("
            SELECT vha.id, vha.id_archivo, va.vehicle_id, va.file_name, va.file_path
            FROM vehiculo_historial_archivos vha
            LEFT JOIN vehiculo_archivos va ON vha.id_archivo = va.id
            WHERE vha.id_vehiculo_historial = ?
        ");
        $archivosStmt->bind_param("i", $id_vehiculo_historial);
        $archivosStmt->execute();
        $archivosResult = $archivosStmt->get_result();

        $archivos = [];
        while ($archivo = $archivosResult->fetch_assoc()) {
            $archivos[] = [
                'id_historial_archivo' => $archivo['id'],
                'vehicle_id' => $archivo['vehicle_id'],
                'file_name' => $archivo['file_name'],
                'file_path' => $archivo['file_path']
            ];
        }
        $archivosStmt->close();

        // Construir el historial con los archivos
        $history[] = [
            'id_vehiculo_historial' => $row['id'],
            'id_vehiculo' => $row['id_vehiculo'],
            'observacion' => $row['observacion'],
            'id_movimiento' => $row['id_movimiento'],
            'movimiento_nombre' => $row['movimiento_nombre'],
            'fecha_manual' => $row['fecha_manual'],
            'creado' => $row['creado'],
            'archivos' => $archivos
        ];

        $stmt->close();
        $conexion->close();

        echo json_encode([
            'type' => 'SUCCESS',
            'action' => 'CONTINUE',
            'response' => $history,
            'message' => 'Historial obtenido correctamente.'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'type' => 'ERROR',
            'message' => 'Error al obtener el historial: ' . $e->getMessage()
        ]);
        $conexion->close();
    }
}
function getDashVehicle()
{
    $conexion = conectar();
    $conexion->set_charset('utf8');

    $sql_categories = "SELECT COUNT(*) as total FROM vehiculo WHERE band_eliminar = 1";
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
function getDashVehicleFiles()
{
    $conexion = conectar();
    $conexion->set_charset('utf8');

    $sql_categories = "SELECT COUNT(*) as total FROM vehiculo_archivos";
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
function getDashVehicleGiven()
{
    $conexion = conectar();
    $conexion->set_charset('utf8');

    $sql_categories = "SELECT COUNT(*) as total FROM vehiculo_archivos WHERE is_active = 0";
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
