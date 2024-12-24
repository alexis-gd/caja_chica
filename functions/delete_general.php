<?php
require_once 'conexion.php';
$opcion = $_POST['opcion'];
switch ($opcion) {
  case 'deleteFile':
    // Eliminar usuarios por Accesos
    echo deleteFile();
    break;
  case 'deleteModel':
    // Eliminar opciones de los modelos
    echo deleteModel();
    break;
  default:
    echo 'Not Found';
    break;
}
function deleteFile()
{
  // Conectar con la base de datos
  $con = conectar();
  $con->set_charset('utf8');

  $id_borrar = $_POST['id_borrar'];
  $filePath = '../' . $_POST['filePath'];

  $response = array();

  // Iniciar una transacción
  mysqli_begin_transaction($con);

  try {
    // Eliminar de la tabla 'vehiculo_archivos'
    $eliminar_archivo = "DELETE FROM vehiculo_archivos WHERE id = '$id_borrar'";
    $resultado_archivo = mysqli_query($con, $eliminar_archivo);

    // Verificar si la eliminación fue exitosa
    if ($resultado_archivo) {
      // Eliminar archivo físico
      if (file_exists($filePath)) {
        if (unlink($filePath)) {
          // Confirmar la transacción si el archivo fue eliminado con éxito
          mysqli_commit($con);
          $response = array('type' => 'SUCCESS', 'message' => 'Datos eliminados correctamente');
        } else {
          throw new Exception('No se pudo eliminar el archivo físico');
        }
      } else {
        throw new Exception('El archivo no existe en el servidor');
      }
    } else {
      // Si alguna eliminación falla, deshacer los cambios
      mysqli_rollback($con);
      $response = array('type' => 'ERROR', 'message' => 'Error al eliminar los datos de la base de datos');
    }

    echo json_encode($response);
  } catch (Exception $e) {
    // En caso de excepción, deshacer la transacción
    mysqli_rollback($con);
    echo json_encode(array('type' => 'ERROR', 'message' => 'Error en la transacción: ' . $e->getMessage()));
  }

  mysqli_close($con);
}
function deleteModel()
{
  $con = conectar();
  $con->set_charset('utf8');

  $id_borrar = $_POST['id']; // ID del registro enviado desde el cliente
  $tabla = $_POST['tabla']; // Nombre de la tabla enviado desde el cliente

  $response = array();

  // Iniciar una transacción
  mysqli_begin_transaction($con);

  try {
    // Construir consulta para eliminar registro
    $deleteQuery = "DELETE FROM $tabla WHERE id = ?";
    $stmt = $con->prepare($deleteQuery);
    $stmt->bind_param('i', $id_borrar);

    if ($stmt->execute()) {
      // Confirmar la transacción
      mysqli_commit($con);
      $response = array('type' => 'SUCCESS', 'message' => 'Registro eliminado correctamente.');
    } else {
      throw new Exception('Error al eliminar el registro de la base de datos.');
    }

    $stmt->close();
  } catch (Exception $e) {
    // Revertir los cambios en caso de error
    mysqli_rollback($con);
    $response = array('type' => 'ERROR', 'message' => 'Error en la transacción: ' . $e->getMessage());
  }

  mysqli_close($con);
  return json_encode($response);
}