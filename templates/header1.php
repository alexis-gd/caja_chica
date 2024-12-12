<?php
  $select_marca = "SELECT nombre, logotipo, favicon FROM seccion_marca";
  $resultado = $con->query($select_marca);
  $marca = $resultado->fetch_assoc();

  try {
    $consulta_version = "SELECT web FROM versiones WHERE id_version = '1'";
    $resultado_version = mysqli_query($con, $consulta_version);
    $array_version = mysqli_fetch_array($resultado_version);
    $v = $array_version['web'];
  } catch (Exception $e) {
    $error = $e->getMessage();
  }
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $marca['nombre']; ?></title>