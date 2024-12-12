<?php
require_once 'conexion.php';
$opcion = $_POST['opcion'];
switch ($opcion) {
    case 'insertVehicle':
        echo insertVehicle();
        break;
    case 'insertUserNominas':
        echo insertUserNominas();
        break;
    case 'insertModelFile':
        echo insertModelFile();
        break;
    case 'insertFile':
        echo insertFile();
        break;
    case 'insertVehicleFileOutput':
        echo insertVehicleFileOutput();
        break;
    case 'insertVehicleFileObservation':
        echo insertVehicleFileObservation();
        break;
    case 'insertModelsGeneric':
        echo insertModelsGeneric();
        break;
    default:
        echo 'Not Insert';
        break;
}
function insertUserNominas()
{
    session_start();
    $option_value = $_POST['option_value'];
    $conexion = conectar($option_value, 'nominas');
    $conexion->set_charset('utf8');
    $usuario_general = $_SESSION['id'];
    $respuesta = array();
    $user = $_POST['modal_nomina_add_user'];
    $pass = $_POST['modal_nomina_add_pass'];
    $name = $_POST['modal_nomina_add_name'];
    $category = $_POST['modal_nomina_add_category'];
    $status = $_POST['status_add'];

    $insert_p = "INSERT INTO login (user, pass, nombre, categoria, created_by, band_eliminar) VALUES ('$user','$pass','$name','$category', '$usuario_general','$status')";
    $resultado_p = mysqli_query($conexion, $insert_p) or die("ERRORS" . mysqli_error($conexion));
    if ($resultado_p) {
        // Operación exitosa
        $respuesta['success'] = true;
        $respuesta['message'] = 'Operación realizada correctamente';
    } else {
        // Error al insertar en la tabla productos
        $respuesta['success'] = false;
        $respuesta['message'] = 'Error al crear el registro';
    }

    echo json_encode($respuesta);
    mysqli_close($conexion);
}
function insertVehicle()
{

    // TODO: validar que no exista el pto nombre
    session_start();
    $conexion = conectar();
    $conexion->set_charset('utf8');

    $response = array();

    // Procesar datos del formulario
    $brand = trim($_POST['modal_vehicle_add_brand']);
    $model = trim($_POST['modal_vehicle_add_model']);
    $year = trim($_POST['modal_vehicle_add_year']) ?: 0;
    $color = trim($_POST['modal_vehicle_add_color']);
    $serie = strtoupper(trim($_POST['modal_vehicle_add_serie'])); // Limpiar espacios y convertir a mayúsculas
    $engine = trim($_POST['modal_vehicle_add_engine']) ?: 'N/A'; // Limpiar espacios y usar 'N/A' si está vacío
    $pedimento = trim($_POST['modal_vehicle_add_pedimento']) ?: 'N/A'; // Limpiar espacios y usar 'N/A' si está vacío
    $owner = trim($_POST['modal_vehicle_add_owner']);
    $license_plate = trim($_POST['modal_vehicle_add_license_plate']) ?: 'N/A'; // Limpiar espacios y usar 'N/A' si está vacío
    $observations = trim($_POST['modal_vehicle_add_observations']);
    $delivery_date = trim($_POST['modal_vehicle_add_delivery_date']);

    // $status = isset($_POST['status_add']) ? $_POST['status_add'] : '0';

    // Iniciar transacción
    mysqli_begin_transaction($conexion);

    try {
        // Insertar en la tabla vehiculos
        $insert_vehiculos = "INSERT INTO vehiculo (
            id_marca, id_modelo, ano, id_color, serie, motor, pedimento, id_propietario, placa, observaciones, fecha_de_entrega) VALUES (
            '$brand', '$model', '$year', '$color', '$serie', '$engine', '$pedimento', '$owner', '$license_plate', '$observations', '$delivery_date')";
        $resultado_vehiculos = mysqli_query($conexion, $insert_vehiculos);

        if ($resultado_vehiculos) {
            // Confirmar la transacción
            mysqli_commit($conexion);

            $response['result'] = true;
            echo json_encode(array(
                'type' => 'SUCCESS',
                'action' => 'CONTINUE',
                'response' => $response,
                'message' => 'Registro creado correctamente'
            ));
        } else {
            throw new Exception('Error al insertar el usuario');
        }
    } catch (Exception $e) {
        // Revertir la transacción si hubo error
        mysqli_rollback($conexion);

        $response['result'] = false;
        echo json_encode(array(
            'type' => 'ERROR',
            'action' => 'CANCEL',
            'response' => $response,
            'message' => $e->getMessage()
        ));
    }

    mysqli_close($conexion);
}
function insertModelFile()
{
    session_start();
    $conexion = conectar();
    $conexion->set_charset('utf8');

    $response = array();

    // Procesar datos del formulario
    // Convertir la primer letra a mayúscula
    $nombre = ucfirst(strtolower(trim($_POST['modal_am_nombre'])));

    // Iniciar transacción
    mysqli_begin_transaction($conexion);

    try {
        // Verificar si el nombre ya existe
        $query_verificar = "SELECT COUNT(*) AS total FROM modelo_archivo WHERE nombre = ?";
        $stmt_verificar = mysqli_prepare($conexion, $query_verificar);
        mysqli_stmt_bind_param($stmt_verificar, 's', $nombre);
        mysqli_stmt_execute($stmt_verificar);
        mysqli_stmt_bind_result($stmt_verificar, $total);
        mysqli_stmt_fetch($stmt_verificar);
        mysqli_stmt_close($stmt_verificar);

        if ($total > 0) {
            throw new Exception('El nombre ya existe en la base de datos.');
        }

        // Insertar en la tabla vehículos
        $insert_archivo = "INSERT INTO modelo_archivo (nombre) VALUES ('$nombre')";
        $resultado_archivo = mysqli_query($conexion, $insert_archivo);

        if ($resultado_archivo) {
            // Confirmar la transacción
            mysqli_commit($conexion);

            $response['result'] = true;
            echo json_encode(array(
                'type' => 'SUCCESS',
                'action' => 'CONTINUE',
                'response' => $response,
                'message' => 'Registro creado correctamente'
            ));
        } else {
            throw new Exception('Error al insertar el registro');
        }
    } catch (Exception $e) {
        // Revertir la transacción si hubo error
        mysqli_rollback($conexion);

        $response['result'] = false;
        echo json_encode(array(
            'type' => 'ERROR',
            'action' => 'CANCEL',
            'response' => $response,
            'message' => $e->getMessage()
        ));
    }

    mysqli_close($conexion);
}
function insertFile()
{
    session_start();
    $conexion = conectar();
    $conexion->set_charset('utf8');

    // Información del archivo
    $file = $_FILES['product_file'];
    $vehicleId = $_POST['vehicleId'];
    $type_file_id = $_POST['type_file_id'];
    $type_file_name = $_POST['type_file_name'];
    $fileTmpName = $file['tmp_name'];
    $fileError = $file['error'];
    $observations = isset($_POST['observations']) ? trim($_POST['observations']) : null;

    // Ruta del vehículo
    $file_path = 'documents/vehiculos/vehiculo_' . intval($vehicleId) . '/';
    $path_directory = '../' . $file_path;

    // Crear directorio si no existe
    if (!is_dir($path_directory)) {
        mkdir($path_directory, 0755, true);
    }

    // Obtener nombre y extensión originales del archivo
    $originalName = $file['name'];
    $pathInfo = pathinfo($originalName);
    $extension = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : '';

    // Verificar el tipo MIME del archivo
    $mimeType = mime_content_type($fileTmpName);
    $validImageTypes = ['image/jpeg', 'image/png', 'image/jpg']; // Tipos de imagen válidos
    $validPDFType = 'application/pdf'; // Tipo MIME para PDF

    if (in_array($mimeType, $validImageTypes)) {
        // Si es una imagen, asignar la extensión correspondiente
        if ($mimeType === 'image/jpeg') {
            $extension = 'jpg';
        } elseif ($mimeType === 'image/png') {
            $extension = 'png';
        } else {
            $extension = 'jpg'; // Valor predeterminado
        }
    } elseif ($mimeType === $validPDFType) {
        // Si es un PDF
        $extension = 'pdf';
    } else {
        // Formato no reconocido
        echo json_encode(['type' => 'ERROR', 'message' => 'Formato de archivo no soportado.']);
        exit;
    }

    // Configuración de fecha y nombre único
    date_default_timezone_set('America/Mazatlan');
    $timestamp_actual = time();
    $date = date('Y-m-d');
    $uniqueName = $type_file_name . '_' . $date . '_' . $timestamp_actual . '.' . $extension;
    $normalizedFileName = mb_convert_encoding($uniqueName, 'UTF-8', 'auto');
    $destination = $path_directory . $normalizedFileName; // Ruta completa del archivo destino

    // Iniciar transacción
    mysqli_begin_transaction($conexion);

    try {
        if ($fileError === UPLOAD_ERR_OK) {
            if (move_uploaded_file($fileTmpName, $destination)) {
                // Insertar en `vehiculo_archivos`
                $stmt1 = $conexion->prepare("INSERT INTO vehiculo_archivos (vehicle_id, file_name, file_path, type_file_id) VALUES (?, ?, ?, ?)");
                $stmt1->bind_param("isss", $vehicleId, $uniqueName, $file_path, $type_file_id);
                if (!$stmt1->execute()) {
                    throw new Exception("Error al guardar el archivo en la base de datos.");
                }
                // Obtener el ID generado para `vehiculo_archivos`
                $id_archivo = $conexion->insert_id;
                $stmt1->close();

                // Insertar en `vehiculo_historial`
                // if ($observations) {
                $id_movimiento = 1; // ID de movimiento para "nuevo"
                $stmt2 = $conexion->prepare("INSERT INTO vehiculo_historial (id_vehiculo, observacion, id_movimiento) VALUES (?, ?, ?)");
                $stmt2->bind_param("isi", $vehicleId, $observations, $id_movimiento);
                if (!$stmt2->execute()) {
                    throw new Exception("Error al guardar la observación en el historial.");
                }
                // Obtener el ID generado para `vehiculo_historial`
                $id_vehiculo_historial = $conexion->insert_id;
                $stmt2->close();
                // } else {
                //     throw new Exception("No hay observaciones para guardar en el historial.");
                // }

                // Insertar en `vehiculo_historial_archivos` relacionando los IDs
                $stmt3 = $conexion->prepare("INSERT INTO vehiculo_historial_archivos (id_vehiculo_historial, id_archivo) VALUES (?, ?)");
                $stmt3->bind_param("ii", $id_vehiculo_historial, $id_archivo);
                if (!$stmt3->execute()) {
                    throw new Exception("Error al guardar la relación en vehiculo_historial_archivos.");
                }
                $stmt3->close();

                // Confirmar transacción
                mysqli_commit($conexion);
                echo json_encode(['type' => 'SUCCESS', 'message' => 'Archivo subido y observación guardada exitosamente.']);
            } else {
                throw new Exception("Error al mover el archivo al destino.");
            }
        } else {
            throw new Exception("Error al subir el archivo. Código: $fileError");
        }
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        mysqli_rollback($conexion);
        echo json_encode(['type' => 'ERROR', 'message' => $e->getMessage()]);
    }

    $conexion->close();
}
function insertVehicleFileOutput()
{
    session_start();
    $conexion = conectar();
    $conexion->set_charset('utf8');

    // Obtener datos enviados desde el frontend
    $vehicleId = $_POST['vehicle_id'];
    $fileIds = $_POST['file_ids']; // Lista de IDs de los archivos seleccionados
    $fileIds = json_decode($fileIds, true);
    $observations = isset($_POST['observations']) ? trim($_POST['observations']) : null;
    $fecha_manual = isset($_POST['fecha_manual']) ? trim($_POST['fecha_manual']) : null;
    $is_return = isset($_POST['is_return']) ? trim($_POST['is_return']) : null;

    // Validar los datos recibidos
    if (empty($vehicleId) || empty($fileIds)) {
        echo json_encode(['type' => 'ERROR', 'message' => 'Datos insuficientes para procesar.']);
        return;
    }

    // Iniciar transacción
    mysqli_begin_transaction($conexion);

    try {
        // Actualizar estado de los archivos en vehiculo_archivos
        $fileIdPlaceholders = implode(',', array_fill(0, count($fileIds), '?'));
        $stmt1 = $conexion->prepare("UPDATE vehiculo_archivos SET is_active = 0 WHERE id IN ($fileIdPlaceholders)");
        if ($is_return) {
            $stmt1 = $conexion->prepare("UPDATE vehiculo_archivos SET is_active = 1 WHERE id IN ($fileIdPlaceholders)");
        }
        $stmt1->bind_param(str_repeat('i', count($fileIds)), ...$fileIds);
        if (!$stmt1->execute()) {
            throw new Exception("Error al actualizar el estado de los archivos.");
        }
        $stmt1->close();

        // Insertar en vehiculo_historial
        $id_movimiento = $is_return ? 3 : 2; // Movimiento = Devolución o Salida
        $stmt2 = $conexion->prepare("INSERT INTO vehiculo_historial (id_vehiculo, observacion, id_movimiento, fecha_manual) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("isis", $vehicleId, $observations, $id_movimiento, $fecha_manual);
        if (!$stmt2->execute()) {
            throw new Exception("Error al guardar el registro en vehiculo_historial.");
        }
        $id_vehiculo_historial = $conexion->insert_id; // ID del registro insertado
        $stmt2->close();

        // Insertar en vehiculo_historial_archivos
        $stmt3 = $conexion->prepare("INSERT INTO vehiculo_historial_archivos (id_vehiculo_historial, id_archivo) VALUES (?, ?)");
        foreach ($fileIds as $fileId) {
            $stmt3->bind_param("ii", $id_vehiculo_historial, $fileId);
            if (!$stmt3->execute()) {
                throw new Exception("Error al guardar la relación en vehiculo_historial_archivos.");
            }
        }
        $stmt3->close();

        // Confirmar transacción
        mysqli_commit($conexion);
        echo json_encode([
            'type' => 'SUCCESS',
            'message' => 'Salida procesada exitosamente.',
            'id_vehiculo_historial' => $id_vehiculo_historial
        ]);
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        mysqli_rollback($conexion);
        echo json_encode(['type' => 'ERROR', 'message' => $e->getMessage()]);
    }

    $conexion->close();
}
function insertVehicleFileObservation()
{
    session_start();
    $conexion = conectar();
    $conexion->set_charset('utf8');

    // Obtener datos enviados desde el frontend
    $vehicleId = $_POST['vehicle_id'];
    $observations = isset($_POST['observations']) ? trim($_POST['observations']) : null;
    $fecha_manual = isset($_POST['fecha_manual']) ? trim($_POST['fecha_manual']) : null;

    // Validar los datos recibidos
    if (empty($vehicleId)) {
        echo json_encode(['type' => 'ERROR', 'message' => 'Datos insuficientes para procesar.']);
        return;
    }

    // Iniciar transacción
    mysqli_begin_transaction($conexion);

    try {
        // Insertar en vehiculo_historial
        $id_movimiento = 0; // Movimiento
        $stmt2 = $conexion->prepare("INSERT INTO vehiculo_historial (id_vehiculo, observacion, id_movimiento, fecha_manual) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("isis", $vehicleId, $observations, $id_movimiento, $fecha_manual);
        if (!$stmt2->execute()) {
            throw new Exception("Error al guardar el registro en vehiculo_historial.");
        }
        $id_vehiculo_historial = $conexion->insert_id; // ID del registro insertado
        $stmt2->close();

        // Confirmar transacción
        mysqli_commit($conexion);
        echo json_encode(['type' => 'SUCCESS', 'message' => 'Salida procesada exitosamente.']);
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        mysqli_rollback($conexion);
        echo json_encode(['type' => 'ERROR', 'message' => $e->getMessage()]);
    }

    $conexion->close();
}
function insertModelsGeneric()
{
    session_start();
    $conexion = conectar();
    $conexion->set_charset('utf8');

    $response = array();

    // Procesar datos del formulario
    $tabla = ucfirst(strtolower(trim($_POST['tabla'])));
    $nombre = ucfirst(strtolower(trim($_POST['newOption'])));

    // Iniciar transacción
    mysqli_begin_transaction($conexion);

    try {
        // Verificar si el nombre ya existe
        $query_verificar = "SELECT COUNT(*) AS total FROM $tabla WHERE nombre = ?";
        $stmt_verificar = mysqli_prepare($conexion, $query_verificar);
        mysqli_stmt_bind_param($stmt_verificar, 's', $nombre);
        mysqli_stmt_execute($stmt_verificar);
        mysqli_stmt_bind_result($stmt_verificar, $total);
        mysqli_stmt_fetch($stmt_verificar);
        mysqli_stmt_close($stmt_verificar);

        if ($total > 0) {
            throw new Exception('El nombre ya existe en la base de datos.');
        }

        // Insertar en la tabla
        $query_insert = "INSERT INTO $tabla (nombre) VALUES (?)";
        $stmt_insert = mysqli_prepare($conexion, $query_insert);
        mysqli_stmt_bind_param($stmt_insert, 's', $nombre);
        mysqli_stmt_execute($stmt_insert);

        // Obtener el ID insertado
        $newId = mysqli_insert_id($conexion);

        if ($newId > 0) {
            // Confirmar la transacción
            mysqli_commit($conexion);

            $response['result'] = true;
            $response['newId'] = $newId;
            echo json_encode(array(
                'type' => 'SUCCESS',
                'action' => 'CONTINUE',
                'response' => $response,
                'message' => 'Registro creado correctamente'
            ));
        } else {
            throw new Exception('Error al insertar el registro.');
        }

        mysqli_stmt_close($stmt_insert);
    } catch (Exception $e) {
        // Revertir la transacción si hubo error
        mysqli_rollback($conexion);

        $response['result'] = false;
        echo json_encode(array(
            'type' => 'ERROR',
            'action' => 'CANCEL',
            'response' => $response,
            'message' => $e->getMessage()
        ));
    }

    mysqli_close($conexion);
}
