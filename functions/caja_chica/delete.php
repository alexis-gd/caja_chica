<?php
require_once '../../config/conexion.php';
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
    $stmt = $con->prepare("DELETE FROM caja_chica_archivos WHERE id = ?");
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
  $tablas_permitidas = [
      'modelo_cargado', 'modelo_area', 'modelo_empresa', 'modelo_autoriza',
      'modelo_tipo_folio', 'modelo_tipo_ingreso', 'modelo_tipo_gasto',
      'modelo_entrega', 'modelo_recibe', 'modelo_comprobante', 'modelo_unidad',
      'modelo_razon_social', 'caja_archivos',
      'modelo_chica_cargado', 'modelo_chica_area', 'modelo_chica_tipo_gasto',
      'modelo_chica_recibe', 'modelo_chica_unidad', 'modelo_chica_comprobante',
      'modelo_chica_razon_social', 'caja_chica_archivos'
  ];
  if (!in_array($tabla, $tablas_permitidas, true)) {
      return json_encode(['type' => 'ERROR', 'message' => 'Tabla no permitida.']);
  }

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
