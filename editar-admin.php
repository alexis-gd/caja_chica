<?php
include_once 'functions/sesiones.php';
include_once 'functions/conexion.php';
$con = conectar();
$id = $_GET['id'];
if (!filter_var($id, FILTER_VALIDATE_INT)) {
  die("Error");
}
include_once 'templates/header1.php';
include_once 'templates/header2.php';
?>
<!-- Aquí css por página -->
</head>
<?php
include_once 'templates/barra3.php';
include_once 'templates/navegacion4.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row justify-content-between">
        <div class="col-6">
          <h1 class="mb-0">Editar cuenta</h1>
        </div>
        <div class="col-6">
          <p class="text-right mb-0">Aquí puedes editar los datos de la cuenta</p>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="container table-responsive pb-5 px-2">
      <!-- Default box -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Editar usuario</h3>
        </div>
        <div class="card-body d-flex">
          <!-- form start -->
          <form method="POST" action="functions/update_user.php" name="guardar-registro" id="guardar-registro">
            <div class="card-body">
              <div class="form-group">
                <label for="usuario">Usuario <small class="text-muted">(opcional)</small></label>
                <input type="text" class="form-control" name="usuario" id="usuario" placeholder="Usuario" maxlength="50" value="<?php echo $_SESSION['usuario'] ?>">
                <span id="resultado_usuario" class=""></span>
              </div>
              <div class="form-group">
                <label for="nombre">Nombre <small class="text-muted">(opcional)</small></label>
                <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Nombre" maxlength="100" value="<?php echo $_SESSION['nombre'] ?>">
                <span id="resultado_nombre" class=""></span>
              </div>
              <div class="form-group">
                <label for="password">Password <small class="text-muted">(opcional)</small></label>
                <input type="password" class="form-control" name="password" id="password" placeholder="Password" autocomplete="" maxlength="60">
              </div>
              <!-- /.card-body -->
              <div class="">
                <input type="hidden" name="registro" value="actualizar">
                <input type="hidden" name="id_registro" value="<?php echo $_SESSION['id']; ?>">
                <button type="submit" class="btn btn-primary">Guardar</button>
              </div>
            </div>
          </form>
        </div>
        <!-- /.card -->
      </div>
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php
include_once 'templates/footer5.php';
?>

<?php
include_once 'templates/footer6.php';
?>

</body>

</html>