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
            <a href="list-vehicles.php">
              <div class="small-box bg-gradient-success">
                <div class="inner">
                  <h3 id="total_vehicle">0</h3>
                  <p>Vehículos registrados</p>
                </div>
                <div class="icon">
                  <i class="fa-solid fa-box-archive"></i>
                </div>
              </div>
            </a>
          </div>
          <!-- small box -->
          <div class="col-6 col-lg-4">
              <div class="small-box bg-gradient-success">
                <div class="inner">
                  <h3 id="total_file">0</h3>
                  <p>Archivos registrados</p>
                </div>
                <div class="icon">
                  <i class="fa-solid fa-folder"></i>
                </div>
              </div>
          </div>
          <!-- small box -->
          <div class="col-6 col-lg-4">
              <div class="small-box bg-gradient-warning">
                <div class="inner">
                  <h3 id="total_files_given">0</h3>
                  <p>Archivos prestados</p>
                </div>
                <div class="icon">
                <i class="fa-solid fa-folder-open"></i>
                </div>
              </div>
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