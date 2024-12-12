<?php
session_start();

// Siempre destruir la sesión al entrar al login
if (session_status() == PHP_SESSION_ACTIVE) {
    session_destroy();
}

// Reconectar después de destruir la sesión
session_start();

// Incluir el archivo de conexión
include_once 'functions/conexion.php';
$con = conectar();

// Consultar la marca
$select_marca = "SELECT nombre, logotipo, favicon FROM seccion_marca";
$resultado = $con->query($select_marca);
$marca = $resultado->fetch_assoc();

// Incluir el header
include_once 'templates/header1.php';
?>
<!-- Aquí css por página -->
<!-- Icon -->
<link rel="icon" type="image/png" href="./img/<?php echo $marca['favicon']; ?>" />
<!-- Google Font: Source Sans Pro -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<!-- Font Awesome -->
<script src="https://kit.fontawesome.com/3334950929.js" crossorigin="anonymous"></script>
<!-- Theme style -->
<link rel="stylesheet" href="css/adminlte.min.css">
<link rel="stylesheet" href="css/datatables.min.css" />
<link rel="stylesheet" href="css/config.css">
</head>
<?php

?>

<body class="hold-transition login-page">
  <div class="login-box">
    <!-- /.login-logo -->
    <div class="card rounded-lg">
      <div class="card-body login-card-body rounded-lg p-4">
        <div class="login-logo">
          <!-- <a href="#"><img class="sombra-logo my-3" src="./img/<?php // echo $marca['logotipo']; ?>" width="180" alt="" /></a> -->
          <p><?php echo $marca['nombre']; ?></p>
        </div>
        <p class="login-box-msg">Acceso al panel de administración</p>

        <form method="POST" action="functions/login-admin.php" name="login-admin" id="login-admin">
          <div class="input-group mb-3">
            <input type="text" name="usuario" id="usuario" class="form-control" placeholder="Usuario" autofocus="">
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-envelope"></span>
              </div>
            </div>
          </div>
          <div class="input-group mb-3">
            <input type="password" name="password" id="password" class="form-control" placeholder="Contraseña" autocomplete="">
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-lock"></span>
              </div>
            </div>
          </div>
          <div class="row justify-content-center">
            <div class="col-6">
              <input type="hidden" name="login-admin" value="1">
              <button type="submit" class="btn btn-login btn-block" id="btn_login">Iniciar sesión</button>
            </div>
            <!-- /.col -->
          </div>
        </form>
      </div>
      <!-- /.login-card-body -->
    </div>
  </div>
  <!-- /.login-box -->

  <?php
  include_once 'templates/footer5.php';
  ?>

  <!-- Control Sidebar -->
  <!-- <aside class="control-sidebar control-sidebar-dark"> -->
  <!-- Control sidebar content goes here -->
  <!-- </aside> -->
  <!-- /.control-sidebar -->

  <?php
  include_once 'templates/footer6.php';
  ?>

</body>

</html>