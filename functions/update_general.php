<?php
require_once 'conexion.php';
$opcion = $_POST['opcion'];
switch ($opcion) {
    case 'updateVehicle':
        // Actualizar  vehículo
        echo updateVehicle();
        break;
    default:
        echo 'Not Found';
        break;
}
function updateVehicle()
{
    session_start();
    $conexion = conectar();
    $conexion->set_charset('utf8');

    // Iniciar una transacción
    mysqli_begin_transaction($conexion);

    try {
        // Procesar datos del formulario
        $brand = trim($_POST['modal_vehicle_edit_brand']);
        $model = trim($_POST['modal_vehicle_edit_model']);
        $year = trim($_POST['modal_vehicle_edit_year']) ?: 0;
        $color = trim($_POST['modal_vehicle_edit_color']);
        $serie = strtoupper(trim($_POST['modal_vehicle_edit_serie'])); // Limpiar espacios y convertir a mayúsculas
        $engine = trim($_POST['modal_vehicle_edit_engine']) ?: 'N/A'; // Limpiar espacios y usar 'N/A' si está vacío
        $pedimento = trim($_POST['modal_vehicle_edit_pedimento']) ?: 'N/A'; // Limpiar espacios y usar 'N/A' si está vacío
        $owner = trim($_POST['modal_vehicle_edit_owner']);
        $license_plate = strtoupper(trim($_POST['modal_vehicle_edit_license_plate'])) ?: 'N/A'; // Limpiar espacios y usar 'N/A' si está vacío
        $observations = trim($_POST['modal_vehicle_edit_observations']);
        $delivery_date = trim($_POST['modal_vehicle_edit_delivery_date']) ?: null; // Permitir NULL si está vacío
        $vehicle_id = (int)$_POST['modal_vehicle_edit_vehicle_id']; // Asegurar que sea un número entero
        $user_id = $_SESSION['id']; // ID del usuario que edita el registro

        // Datos del response
        $response['id'] = $vehicle_id;

        // Query para actualizar los datos
        $actualizarVehiculo = "
            UPDATE vehiculo
            SET
                id_marca = '$brand',
                id_modelo = '$model',
                ano = $year,
                id_color = '$color',
                serie = '$serie',
                motor = '$engine',
                pedimento = '$pedimento',
                id_propietario = '$owner',
                placa = '$license_plate',
                observaciones = '$observations',
                fecha_de_entrega = " . ($delivery_date ? "'$delivery_date'" : "NULL") . ",
                editado = CURRENT_TIMESTAMP,
                creado_por = $user_id
            WHERE id_vehiculo = $vehicle_id
        ";

        // Ejecutar la consulta
        $resultado = mysqli_query($conexion, $actualizarVehiculo);

        // Verificar si la consulta fue exitosa
        if ($resultado) {
            // Confirmar la transacción
            mysqli_commit($conexion);
            echo json_encode(array(
                'type' => 'SUCCESS',
                'action' => 'CONTINUE',
                'response' => array(
                    'id' => $vehicle_id,
                    'updated' => true,
                ),
                'message' => 'Registro actualizado exitosamente'
            ));
        } else {
            // Si falla, deshacer la transacción
            mysqli_rollback($conexion);
            echo json_encode(array(
                'type' => 'ERROR',
                'action' => 'CANCEL',
                'response' => null,
                'message' => 'Hubo un error al actualizar el registro'
            ));
        }
    } catch (Exception $e) {
        // En caso de error, deshacer la transacción
        mysqli_rollback($conexion);
        echo json_encode(array(
            'type' => 'ERROR',
            'action' => 'CANCEL',
            'message' => 'Error en la transacción: ' . $e->getMessage()
        ));
    }

    // Cerrar conexión
    mysqli_close($conexion);
}
