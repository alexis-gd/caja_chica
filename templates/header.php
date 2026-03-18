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
<!-- Icon -->
<link rel="icon" type="image/png" href="./img/<?php echo $marca['favicon']; ?>" />
<!-- Google Font: Source Sans Pro -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<!-- Font Awesome -->
<script src="https://kit.fontawesome.com/3334950929.js" crossorigin="anonymous"></script>
<!-- Theme style -->
<link rel="stylesheet" href="css/adminlte.min.css">
<link rel="stylesheet" href="css/datatables.min.css" />
<link rel="stylesheet" href="css/dt_ss.css?v=<?php echo $v; ?>" />
