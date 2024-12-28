<?php
// require_once 'config.php';
// Configurar la visualización de errores basada en el entorno
// if (ENVIRONMENT === 'dev') {
// ini_set('display_errors', 0);
// error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
// }
require_once 'conexion.php';
$opcion = $_POST['opcion'];
switch ($opcion) {
    case 'insertCaja':
        echo insertCaja();
        break;
    case 'insertModelsGeneric':
        echo insertModelsGeneric();
        break;
    case 'insertFile':
        echo insertFile();
        break;
    default:
        // echo 'Not Insert';
        break;
}
function getDailyBalance($modal_caja_add_ingreso, $modal_caja_add_egreso, $conexion, $fecha)
{
    // $conexion = conectar();
    // $conexion->set_charset('utf8');

    // Validar parámetros
    if ($modal_caja_add_ingreso > 0 && $modal_caja_add_egreso > 0) {
        throw new Exception("Solo uno de los campos de ingreso o egreso debe tener un valor mayor a 0.");
    }
    if ($modal_caja_add_ingreso == 0 && $modal_caja_add_egreso == 0) {
        throw new Exception("Algún campo de ingreso o egreso deben tener un valor mayor a 0.");
    }

    $monto_total = 0;

    // Obtener la fecha actual en la zona horaria específica
    // $timezone = new DateTimeZone('America/Mexico_City');
    // $fecha_actual = new DateTime('now', $timezone);
    // $fecha_actual = $fecha_actual->format('Y-m-d'); // Formatear la fecha sin la hora

    // cambiamos la fecha del dia por la fecha del registro
    $fecha_actual = substr($fecha, 0, 10);
    $fecha_actual .= ' 00:00:00';

    try {
        // Iniciar transacción
        mysqli_begin_transaction($conexion);

        // Verificar si existe un registro para el día actual en `caja_chica_totales`
        $query = "
            SELECT monto_total 
            FROM caja_chica_totales 
            WHERE DATE(fecha) = ?
            LIMIT 1
        ";
        $stmt = $conexion->prepare($query);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conexion->error);
        }

        $stmt->bind_param('s', $fecha_actual);
        $stmt->execute();
        $stmt->bind_result($monto_total);
        $registro_existe = $stmt->fetch();
        $stmt->close();

        if ($registro_existe) {
            $modal_caja_add_ingreso = floatval($modal_caja_add_ingreso);
            $modal_caja_add_egreso = floatval($modal_caja_add_egreso);

            // Calcular el nuevo monto total
            if ($modal_caja_add_ingreso > 0) {
                $monto_total += $modal_caja_add_ingreso;
            } elseif ($modal_caja_add_egreso > 0) {
                $monto_total -= $modal_caja_add_egreso;
            }

            // Actualizar el registro existente
            $update_query = "
                UPDATE caja_chica_totales
                SET monto_total = ?
                WHERE DATE(fecha) = ?
            ";
            $update_stmt = $conexion->prepare($update_query);
            if (!$update_stmt) {
                throw new Exception("Error al preparar la consulta de actualización: " . $conexion->error);
            }

            $update_stmt->bind_param('ds', $monto_total, $fecha_actual);
            if (!$update_stmt->execute()) {
                throw new Exception("Error al actualizar el registro: " . $conexion->error);
            }
            $update_stmt->close();
        } else {
            // Validar que no se pueda registrar un egreso inicial
            if ($modal_caja_add_egreso > 0) {
                throw new Exception("No se puede registrar un egreso inicial sin un ingreso previo.");
            }

            // Insertar un nuevo registro
            $insert_query = "
                INSERT INTO caja_chica_totales (monto_total, fecha)
                VALUES (?, ?)
            ";
            $insert_stmt = $conexion->prepare($insert_query);
            if (!$insert_stmt) {
                throw new Exception("Error al preparar la consulta de inserción: " . $conexion->error);
            }

            $insert_stmt->bind_param('ds', $modal_caja_add_ingreso, $fecha_actual);
            if (!$insert_stmt->execute()) {
                throw new Exception("Error al insertar el nuevo registro: " . $conexion->error);
            }
            $monto_total = $modal_caja_add_ingreso; // El monto total es igual al ingreso inicial
            $insert_stmt->close();
        }

        // Confirmar la transacción
        mysqli_commit($conexion);

        return $monto_total;
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        mysqli_rollback($conexion);
        throw new Exception($e->getMessage());
    }
}
function insertCaja()
{
    session_start();
    $conexion = conectar();
    $conexion->set_charset('utf8');
    date_default_timezone_set('America/Mazatlan');

    $response = array();

    try {

        // Procesar datos del formulario
        $modal_caja_add_fecha = trim($_POST['modal_caja_add_fecha']); // Se espera en formato 'Y-m-d' (por ejemplo: 2024-12-22)
        $fecha_actual = date('Y-m-d H:i:s');
        // Establecer la zona horaria
        $timezone = new DateTimeZone('America/Mexico_City');
        $fecha_actual = new DateTime($modal_caja_add_fecha, $timezone);
        // Obtener la hora actual y agregarla a la fecha recibida
        $fecha_actual->setTime(date('H'), date('i'), date('s')); // Establece la hora, minutos y segundos actuales
        // Formatear la fecha con la hora y segundos
        $modal_caja_add_fecha = $fecha_actual->format('Y-m-d H:i:s');

        $modal_caja_add_cargado = trim($_POST['modal_caja_add_cargado']);
        $modal_caja_add_area = trim($_POST['modal_caja_add_area']);
        $modal_caja_add_tipo_gasto = trim($_POST['modal_caja_add_tipo_gasto']);
        $modal_caja_add_concepto = trim($_POST['modal_caja_add_concepto']);
        $modal_caja_add_recibe = trim($_POST['modal_caja_add_recibe']);
        $modal_caja_add_unidad = trim($_POST['modal_caja_add_unidad']);
        $modal_caja_add_comprobante = trim($_POST['modal_caja_add_comprobante']);
        $modal_caja_add_razon_social = trim($_POST['modal_caja_add_razon_social']);
        $modal_caja_add_ingreso = trim($_POST['modal_caja_add_ingreso']);
        $modal_caja_add_egreso = trim($_POST['modal_caja_add_egreso']);
        // $modal_caja_add_folio = trim($_POST['modal_caja_add_folio']);
        // $modal_caja_add_empresa = trim($_POST['modal_caja_add_empresa']);
        // $modal_caja_add_entrega = trim($_POST['modal_caja_add_entrega']);
        // $modal_caja_add_tipo_ingreso = trim($_POST['modal_caja_add_tipo_ingreso']);
        // $modal_caja_add_autoriza = trim($_POST['modal_caja_add_autoriza']);
        // $modal_caja_add_proveedor = trim($_POST['modal_caja_add_proveedor']);
        // $modal_caja_add_operador = trim($_POST['modal_caja_add_operador']);
        // $modal_caja_add_factura = trim($_POST['modal_caja_add_factura']);
        // Obtener el saldo con getDailyBalance

        // Obtener el saldo con manejo de errores
        try {
            $saldo = getDailyBalance($modal_caja_add_ingreso, $modal_caja_add_egreso, $conexion, $modal_caja_add_fecha);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // Iniciar transacción
        mysqli_begin_transaction($conexion);

        // Sentencia preparada para insertar en la tabla caja
        $query = "
            INSERT INTO caja_chica (
                fecha, id_cargado, id_area, id_tipo_gasto, concepto, id_recibe,
                id_unidad, id_comprobante, id_razon_social, ingreso, egreso, saldo
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ";

        // Preparar la sentencia
        $stmt = $conexion->prepare($query);
        if ($stmt === false) {
            throw new Exception("Error al preparar la consulta: " . $conexion->error);
        }

        // Enlazar los parámetros
        $stmt->bind_param(
            'siissisdiddd', // Tipos de los parámetros
            $modal_caja_add_fecha,
            $modal_caja_add_cargado,
            $modal_caja_add_area,
            $modal_caja_add_tipo_gasto,
            $modal_caja_add_concepto,
            $modal_caja_add_recibe,
            $modal_caja_add_unidad,
            $modal_caja_add_comprobante,
            $modal_caja_add_razon_social,
            $modal_caja_add_ingreso,
            $modal_caja_add_egreso,
            $saldo
        );

        // Ejecutar la sentencia
        if ($stmt->execute()) {
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
            throw new Exception('Error al insertar los datos en la tabla caja: ' . $conexion->error);
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

    // Cerrar la sentencia y la conexión
    if (isset($stmt)) {
        $stmt->close();
    }
    mysqli_close($conexion);
}
function replaceSpecialChars($string)
{
    $search = ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ'];
    $replace = ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N'];
    return str_replace($search, $replace, $string);
}
function insertModelsGeneric()
{
    session_start();
    $conexion = conectar();
    $conexion->set_charset('utf8');

    $response = array();

    // Procesar datos del formulario
    $tabla = trim($_POST['tabla']);
    $nombre_sin_caracteres = replaceSpecialChars($_POST['newOption']);
    $nombre = ucfirst(strtolower(trim($nombre_sin_caracteres)));

    // Iniciar transacción
    mysqli_begin_transaction($conexion);

    try {

        if ($_SESSION['nivel'] != 1) {
            throw new Exception('No tienes autorización para agregar conceptos a las listas.');
        }

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
function insertFile()
{
    session_start();
    $conexion = conectar();
    $conexion->set_charset('utf8');

    // Verificar si el archivo fue enviado
    $file = isset($_FILES['product_file']) ? $_FILES['product_file'] : null;
    $modal_comprobante_id = $_POST['modal_comprobante_id'];
    $type_file_id = $_POST['type_file_id'];
    
    $type_file_name = $_POST['type_file_name'];
    $nombre_sin_caracteres = replaceSpecialChars($type_file_name);
    $type_file_name = ucfirst(strtolower(trim($nombre_sin_caracteres)));

    $fileTmpName = $file ? $file['tmp_name'] : null;
    $fileError = $file ? $file['error'] : null;
    $comments = isset($_POST['comments']) ? trim($_POST['comments']) : null;
    $modal_comprobante_add_comprobante = $_POST['modal_comprobante_add_comprobante'];
    $check_visible = isset($_POST['check_visible']) ? 1 : 0;
    $id_archivo = 0;

    // Ruta del vehículo
    $file_path = 'documents_chica/comprobante/comprobante_' . intval($modal_comprobante_id) . '/';
    $path_directory = '../' . $file_path;

    // Crear directorio si no existe
    if (!is_dir($path_directory)) {
        mkdir($path_directory, 0755, true);
    }

    // Iniciar transacción
    mysqli_begin_transaction($conexion);

    try {
        if ($check_visible && $file) {
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

            if ($fileError === UPLOAD_ERR_OK) {
                if (move_uploaded_file($fileTmpName, $destination)) {
                    // Insertar en `caja_chica_archivos`
                    $stmt1 = $conexion->prepare("INSERT INTO caja_chica_archivos (id_caja, file_name, file_path, type_file_id, comments) VALUES (?, ?, ?, ?, ?)");
                    $stmt1->bind_param("issss", $modal_comprobante_id, $uniqueName, $file_path, $type_file_id, $comments);
                    if (!$stmt1->execute()) {
                        throw new Exception("Error al guardar el archivo en la base de datos.");
                    }
                    // Obtener el ID generado para `caja_chica_archivos`
                    $id_archivo = $conexion->insert_id;
                    $stmt1->close();
                } else {
                    throw new Exception("Error al mover el archivo al destino.");
                }
            } else {
                throw new Exception("Error al subir el archivo. Código: $fileError");
            }
        } else {
            // Si no se sube archivo, insertar solo el comentario si no está vacío
            if (!empty($comments)) {
                $stmt1 = $conexion->prepare("INSERT INTO caja_chica_archivos (id_caja, file_name, file_path, type_file_id, comments) VALUES (?, '', '', '', ?)");
                $stmt1->bind_param("is", $modal_comprobante_id, $comments);
                if (!$stmt1->execute()) {
                    throw new Exception("Error al guardar el comentario en la base de datos.");
                }
                // Obtener el ID generado para `caja_chica_archivos`
                $id_archivo = $conexion->insert_id;
                $stmt1->close();
            }
        }

        // Actualizar la tabla `caja` si `modal_comprobante_add_comprobante` no está vacío
        if (!empty($modal_comprobante_add_comprobante)) {
            $stmt2 = $conexion->prepare("UPDATE caja_chica SET id_comprobante = ? WHERE id_caja = ?");
            $stmt2->bind_param("ii", $modal_comprobante_add_comprobante, $modal_comprobante_id);
            if (!$stmt2->execute()) {
                throw new Exception("Error al actualizar el comprobante en la base de datos.");
            }
            $stmt2->close();
        }

        // Confirmar transacción
        mysqli_commit($conexion);
        echo json_encode(['type' => 'SUCCESS', 'id_insertado' => $id_archivo, 'message' => 'Registro actualizado exitosamente.']);
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        mysqli_rollback($conexion);
        echo json_encode(['type' => 'ERROR', 'message' => $e->getMessage()]);
    }

    $conexion->close();
}
