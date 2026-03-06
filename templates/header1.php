<?php
  $select_marca = "SELECT nombre, logotipo, favicon FROM seccion_marca";
  $resultado = $con->query($select_marca);
  $marca = $resultado->fetch(PDO::FETCH_ASSOC);

  try {
    $consulta_version = "SELECT web FROM versiones WHERE id_version = '1'";
    $resultado_version = $con->query($consulta_version);
    $array_version = $resultado_version->fetch();
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