<?php
require_once '../config/conexion.php';
$opcion = $_POST['opcion'];
switch ($opcion) {
  case 'deleteFile':
    echo deleteFile();
    break;
  case 'deleteModel':
    echo deleteModel();
    break;
  default:
    echo 'Not Found';
    break;
}
function deleteFile()
{
  $con = conectar();

  $id_borrar = (int)$_POST['id_borrar'];
  $filePath  = '../' . $_POST['filePath'];

  $response = array();

  $con->beginTransaction();

  try {
    $stmt = $con->prepare("DELETE FROM vehiculo_archivos WHERE id = ?");
    $stmt->execute([$id_borrar]);

    if (file_exists($filePath)) {
      if (unlink($filePath)) {
        $con->commit();
        $response = array('type' => 'SUCCESS', 'message' => 'Datos eliminados correctamente');
      } else {
        throw new Exception('No se pudo eliminar el archivo físico');
      }
    } else {
      throw new Exception('El archivo no existe en el servidor');
    }

    echo json_encode($response);
  } catch (Exception $e) {
    if ($con->inTransaction()) {
      $con->rollBack();
    }
    echo json_encode(array('type' => 'ERROR', 'message' => 'Error en la transacción: ' . $e->getMessage()));
  }
}
function deleteModel()
{
  $con = conectar();

  $id_borrar = (int)$_POST['id'];
  $tabla     = $_POST['tabla'];

  $response = array();

  $con->beginTransaction();

  try {
    $stmt = $con->prepare("DELETE FROM $tabla WHERE id = ?");
    $stmt->execute([$id_borrar]);

    $con->commit();
    $response = array('type' => 'SUCCESS', 'message' => 'Registro eliminado correctamente.');
  } catch (Exception $e) {
    if ($con->inTransaction()) {
      $con->rollBack();
    }
    $response = array('type' => 'ERROR', 'message' => 'Error en la transacción: ' . $e->getMessage());
  }

  return json_encode($response);
}
