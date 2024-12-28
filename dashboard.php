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
        <h3 class="card-title">Caja General</h3>
      </div>
      <div class="card-body">

        <div class="row">
          <!-- small box -->
          <div class="col-12 col-lg-4">
            <a href="lista-caja-general.php">
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
          <div class="col-12 col-lg-4">
            <a href="lista-caja-general.php">
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
          <div class="col-12 col-lg-4">
            <a href="lista-caja-general.php">
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
          <div class="col-12 col-lg-4">
            <a href="lista-caja-general.php">
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
          <div class="col-12">
            <div class="card">
              <div class="card-header border-0">
                <div class="d-flex justify-content-center">
                  <h3 class="card-title">Movimientos del año <?php echo date('Y'); ?></h3>
                </div>
              </div>
              <div class="card-body">
                <div class="d-flex">
                  <p class="d-flex flex-column">
                    <span class="text-bold text-lg" id="total_saldo_grafica">0</span>
                    <span>Saldo total del último mes</span>
                  </p>
                  <!-- <p class="ml-auto d-flex flex-column text-right">
                    <span class="text-success">
                      <i class="fas fa-arrow-up"></i> 33.1%
                    </span>
                    <span class="text-muted">Since last month</span>
                  </p> -->
                </div>
                <!-- /.d-flex -->

                <div class="position-relative mb-4">
                  <canvas id="sales-chart" height="200"></canvas>
                </div>

                <div class="d-flex flex-row justify-content-end">
                  <span class="mr-2">
                    <i class="fas fa-square text-primary"></i> Ingreso
                  </span>

                  <span>
                    <i class="fas fa-square text-gray"></i> Egreso
                  </span>
                </div>
              </div>
            </div>
            <!-- /.card -->
          </div>
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
<script src="js/chart.min.js"></script>
<script src="js/dashboard.js?v=<?php echo $v; ?>"></script>

</body>

</html>