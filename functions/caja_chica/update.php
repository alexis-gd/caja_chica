<?php
require_once '../../config/conexion.php';
require_once 'insert.php'; // Include the file where getDailyBalance is defined

$opcion = $_POST['opcion'];
switch ($opcion) {
    case 'updateCaja':
        echo updateCaja();
        break;
    case 'updateCatalogo':
        echo updateCatalogo();
        break;
    default:
        echo 'Not Found';
        break;
}
function updateCaja()
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $conexion = conectar();
    date_default_timezone_set('America/Mazatlan');

    $response = array();

    try {
        // Procesar datos del formulario
        $modal_caja_edit_fecha = trim($_POST['modal_caja_edit_fecha']);
        $fecha_actual = date('Y-m-d H:i:s');
        $timezone = new DateTimeZone('America/Mexico_City');
        $fecha_actual = new DateTime($modal_caja_edit_fecha, $timezone);
        $fecha_actual->setTime(date('H'), date('i'), date('s'));
        $modal_caja_edit_fecha = $fecha_actual->format('Y-m-d H:i:s');

        $modal_caja_edit_cargado      = isset($_POST['modal_caja_edit_cargado'])      ? trim($_POST['modal_caja_edit_cargado'])      : 0;
        $modal_caja_edit_area         = isset($_POST['modal_caja_edit_area'])         ? trim($_POST['modal_caja_edit_area'])         : 0;
        $modal_caja_edit_tipo_gasto   = isset($_POST['modal_caja_edit_tipo_gasto'])   ? trim($_POST['modal_caja_edit_tipo_gasto'])   : 0;
        $modal_caja_edit_concepto     = isset($_POST['modal_caja_edit_concepto'])     ? trim($_POST['modal_caja_edit_concepto'])     : 0;
        $modal_caja_edit_recibe       = isset($_POST['modal_caja_edit_recibe'])       ? trim($_POST['modal_caja_edit_recibe'])       : 0;
        $modal_caja_edit_unidad       = isset($_POST['modal_caja_edit_unidad'])       ? trim($_POST['modal_caja_edit_unidad'])       : 0;
        $modal_caja_edit_comprobante  = isset($_POST['modal_caja_edit_comprobante'])  ? trim($_POST['modal_caja_edit_comprobante'])  : 0;
        $modal_caja_edit_razon_social = isset($_POST['modal_caja_edit_razon_social']) ? trim($_POST['modal_caja_edit_razon_social']) : 0;
        $modal_caja_edit_ingreso = trim($_POST['modal_caja_edit_ingreso']);
        $modal_caja_edit_egreso  = trim($_POST['modal_caja_edit_egreso']);
        $modal_caja_edit_id      = (int)$_POST['modal_caja_edit_id'];

        // Obtener el saldo con manejo de errores
        try {
            $saldo = getDailyBalance($modal_caja_edit_ingreso, $modal_caja_edit_egreso, $conexion, $modal_caja_edit_fecha);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // Iniciar transacción
        $conexion->beginTransaction();

        // Sentencia preparada para actualizar en la tabla caja_chica
        $query = "
            UPDATE caja_chica
            SET
                fecha = ?,
                id_cargado = ?,
                id_area = ?,
                id_tipo_gasto = ?,
                concepto = ?,
                id_recibe = ?,
                id_unidad = ?,
                id_comprobante = ?,
                id_razon_social = ?,
                ingreso = ?,
                egreso = ?,
                saldo = ?
            WHERE id_caja = ?
        ";

        $stmt = $conexion->prepare($query);
        $stmt->execute([
            $modal_caja_edit_fecha,
            $modal_caja_edit_cargado,
            $modal_caja_edit_area,
            $modal_caja_edit_tipo_gasto,
            $modal_caja_edit_concepto,
            $modal_caja_edit_recibe,
            $modal_caja_edit_unidad,
            $modal_caja_edit_comprobante,
            $modal_caja_edit_razon_social,
            $modal_caja_edit_ingreso,
            $modal_caja_edit_egreso,
            $saldo,
            $modal_caja_edit_id,
        ]);

        // Confirmar la transacción
        $conexion->commit();

        $response['result'] = true;
        echo json_encode(array(
            'type' => 'SUCCESS',
            'action' => 'CONTINUE',
            'response' => $response,
            'message' => 'Registro actualizado correctamente'
        ));
    } catch (Exception $e) {
        // Revertir la transacción si hubo error
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }

        $response['result'] = false;
        echo json_encode(array(
            'type' => 'ERROR',
            'action' => 'CANCEL',
            'response' => $response,
            'message' => $e->getMessage()
        ));
    }
}
function updateCatalogo()
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $conexion = conectar();

    $response = array();

    try {
        $id     = (int)$_POST['id'];
        $nombre = ucfirst(strtolower(trim($_POST['modal_ec_nombre'])));
        $tabla  = trim($_POST['tabla']);
        $tablas_permitidas = [
            'modelo_chica_cargado', 'modelo_chica_area', 'modelo_chica_tipo_gasto',
            'modelo_chica_recibe', 'modelo_chica_unidad', 'modelo_chica_comprobante',
            'modelo_chica_razon_social', 'caja_chica_archivos'
        ];
        if (!in_array($tabla, $tablas_permitidas, true)) {
            throw new Exception('Tabla no permitida.');
        }

        // Iniciar transacción
        $conexion->beginTransaction();

        // Sentencia preparada para actualizar en la tabla correspondiente
        $query = "UPDATE $tabla SET nombre = ? WHERE id = ?";
        $stmt  = $conexion->prepare($query);
        $stmt->execute([$nombre, $id]);

        // Confirmar la transacción
        $conexion->commit();

        $response['result'] = true;
        echo json_encode(array(
            'type' => 'SUCCESS',
            'action' => 'CONTINUE',
            'response' => $response,
            'message' => 'Registro actualizado correctamente'
        ));
    } catch (Exception $e) {
        // Revertir la transacción si hubo error
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }

        $response['result'] = false;
        echo json_encode(array(
            'type' => 'ERROR',
            'action' => 'CANCEL',
            'response' => $response,
            'message' => $e->getMessage()
        ));
    }
}
