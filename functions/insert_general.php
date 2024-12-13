<?php
require_once 'conexion.php';
$opcion = $_POST['opcion'];
switch ($opcion) {
    case 'insertCaja':
        echo insertCaja();
        break;
    default:
        echo 'Not Insert';
        break;
}
function getDailyBalance($modal_caja_add_ingreso, $modal_caja_add_egreso, $conexion)
{
    // $conexion = conectar();
    // $conexion->set_charset('utf8');

    // Validar parámetros
    if ($modal_caja_add_ingreso > 0 && $modal_caja_add_egreso > 0) {
        throw new Exception("Solo uno de los campos de ingreso o egreso debe tener un valor mayor a 0.");
    }

    $monto_total = 0;
    $fecha_actual = date('Y-m-d'); // Obtener la fecha actual sin la hora

    try {
        // Iniciar transacción
        mysqli_begin_transaction($conexion);

        // Verificar si existe un registro para el día actual en `caja_totales`
        $query = "
            SELECT monto_total 
            FROM caja_totales 
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
            // Calcular el nuevo monto total
            if ($modal_caja_add_ingreso > 0) {
                $monto_total += $modal_caja_add_ingreso;
            } elseif ($modal_caja_add_egreso > 0) {
                $monto_total -= $modal_caja_add_egreso;
            }

            // Actualizar el registro existente
            $update_query = "
                UPDATE caja_totales
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
                INSERT INTO caja_totales (monto_total, fecha)
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
        throw new Exception("Error en la operación: " . $e->getMessage());
    }
}
function insertCaja()
{
    session_start();
    $conexion = conectar();
    $conexion->set_charset('utf8');

    $response = array();

    // Procesar datos del formulario
    $modal_caja_add_fecha = trim($_POST['modal_caja_add_fecha']);
    $modal_caja_add_cargado = trim($_POST['modal_caja_add_cargado']);
    $modal_caja_add_area = trim($_POST['modal_caja_add_area']);
    $modal_caja_add_folio = trim($_POST['modal_caja_add_folio']);
    $modal_caja_add_empresa = trim($_POST['modal_caja_add_empresa']);
    $modal_caja_add_entrega = trim($_POST['modal_caja_add_entrega']);
    $modal_caja_add_tipo_ingreso = trim($_POST['modal_caja_add_tipo_ingreso']);
    $modal_caja_add_tipo_gasto = trim($_POST['modal_caja_add_tipo_gasto']);
    $modal_caja_add_autoriza = trim($_POST['modal_caja_add_autoriza']);
    $modal_caja_add_concepto = trim($_POST['modal_caja_add_concepto']);
    $modal_caja_add_proveedor = trim($_POST['modal_caja_add_proveedor']);
    $modal_caja_add_recibe = trim($_POST['modal_caja_add_recibe']);
    $modal_caja_add_unidad = trim($_POST['modal_caja_add_unidad']);
    $modal_caja_add_operador = trim($_POST['modal_caja_add_operador']);
    $modal_caja_add_comprobante = trim($_POST['modal_caja_add_comprobante']);
    $modal_caja_add_factura = trim($_POST['modal_caja_add_factura']);
    $modal_caja_add_ingreso = trim($_POST['modal_caja_add_ingreso']);
    $modal_caja_add_egreso = trim($_POST['modal_caja_add_egreso']);
    // Obtener el saldo con getDailyBalance
    $saldo = getDailyBalance($modal_caja_add_ingreso, $modal_caja_add_egreso, $conexion);

    // Iniciar transacción
    mysqli_begin_transaction($conexion);

    try {

        // Sentencia preparada para insertar en la tabla caja
        $query = "
            INSERT INTO caja (
                fecha, id_cargado, id_area, folio, id_empresa, id_entrega, id_tipo_ingreso, 
                id_tipo_gasto, id_autoriza, concepto, id_proveedor, id_recibe, 
                id_unidad, id_operador, id_comprobante, id_factura, ingreso, egreso, saldo
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ";

        // Preparar la sentencia
        $stmt = $conexion->prepare($query);
        if ($stmt === false) {
            throw new Exception("Error al preparar la consulta: " . $conexion->error);
        }

        // Enlazar los parámetros
        $stmt->bind_param(
            'siisiiiiisiiiiidddi', // Tipos de los parámetros
            $modal_caja_add_fecha,
            $modal_caja_add_cargado,
            $modal_caja_add_area,
            $modal_caja_add_folio,
            $modal_caja_add_empresa,
            $modal_caja_add_entrega,
            $modal_caja_add_tipo_ingreso,
            $modal_caja_add_tipo_gasto,
            $modal_caja_add_autoriza,
            $modal_caja_add_concepto,
            $modal_caja_add_proveedor,
            $modal_caja_add_recibe,
            $modal_caja_add_unidad,
            $modal_caja_add_operador,
            $modal_caja_add_comprobante,
            $modal_caja_add_factura,
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
    $stmt->close();
    mysqli_close($conexion);
}
