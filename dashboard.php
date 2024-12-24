<?php
include_once 'functions/sesiones.php';
include_once 'functions/conexion.php';
$con = conectar();
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
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Dashboard</h1>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>

  <!-- Main content -->
  <section class="content">

    <!-- Default box -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Vista General</h3>
      </div>
      <div class="card-body">

        <div class="row">
          <!-- small box -->
          <div class="col-6 col-lg-4">
            <a href="lista-caja-chica.php">
              <div class="small-box bg-gradient-success">
                <div class="inner">
                  <h3 id="total_registros">0</h3>
                  <p>Registros ingresados</p>
                </div>
                <div class="icon">
                  <i class="fa-solid fa-file-pen"></i>
                </div>
              </div>
            </a>
          </div>
          <!-- small box -->
          <div class="col-6 col-lg-4">
            <a href="lista-caja-chica.php">
              <div class="small-box bg-gradient-success">
                <div class="inner">
                  <h3 id="total_ingreso">0</h3>
                  <p>Ingreso total del mes</p>
                </div>
                <div class="icon">
                <i class="fa-solid fa-circle-dollar-to-slot"></i>
                </div>
              </div>
            </a>
          </div>
          <!-- small box -->
          <div class="col-6 col-lg-4">
            <a href="lista-caja-chica.php">
              <div class="small-box bg-gradient-danger">
                <div class="inner">
                  <h3 id="total_egresos">0</h3>
                  <p>Egreso total del mes</p>
                </div>
                <div class="icon">
                  <i class="fa-solid fa-money-bill-trend-up"></i>
                </div>
              </div>
            </a>
          </div>
          <!-- small box -->
          <div class="col-6 col-lg-4">
            <a href="lista-caja-chica.php">
              <div class="small-box bg-gradient-primary">
                <div class="inner">
                  <h3 id="total_saldo">0</h3>
                  <p>Saldo total del mes</p>
                </div>
                <div class="icon">
                  <i class="fa-solid fa-sack-dollar"></i>
                </div>
              </div>
            </a>
          </div>
          <!-- /.row -->
        </div>

        <!-- /.card-body -->
        <div class="card-footer">
          ...
        </div>
        <!-- /.card-footer-->
      </div>
      <!-- /.card -->

  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

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

<script src="js/dashboard.js?v=<?php echo $v; ?>"></script>

</body>

</html>