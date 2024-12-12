<?php
include_once 'functions/sesiones.php';
include_once 'functions/conexion.php';
$con = conectar();
include_once 'templates/header1.php';
include_once 'templates/header2.php';
?>
<!-- Aquí css por página -->
<link rel="stylesheet" type="text/css" href="css/selectize.bootstrap4.min.css" />
</head>
<?php
include_once 'templates/barra3.php';
include_once 'templates/navegacion4.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <div class=" container-fluid table-responsive py-5 px-2">
    <div class="card">
      <div class="card-header">
        <h3 class="mb-0 azul"><i class="fa-solid fa-clipboard-list azul pr-2"></i>Parque vehicular</h3>
      </div>
      <div class="card-body">
        <table id="tablaVehiculos" class="table tab-hov table-condensed table-sm table-striped w-100" data-order='[[ 0, "desc" ]]'>
          <thead class="text-center thead-dark">
            <tr>
              <th>Id</th>
              <th>Marca</th>
              <th>Modelo</th>
              <th>Año</th>
              <th>Color</th>
              <th>Serie</th>
              <th>Motor</th>
              <th>Pedimento</th>
              <th>Propietario</th>
              <th>Placa</th>
              <th>Observaciones</th>
              <th>Entrega</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th>Id</th>
              <th>Marca</th>
              <th>Modelo</th>
              <th>Año</th>
              <th>Color</th>
              <th>Serie</th>
              <th>Motor</th>
              <th>Pedimento</th>
              <th>Propietario</th>
              <th>Placa</th>
              <th>Observaciones</th>
              <th>Entrega</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

<?php
include_once 'templates/footer5.php';
include_once 'templates/modals/modal_add_vehicle.php';
include_once 'templates/footer_table.php';
?>
<script type="text/javascript" src="js/selectize.min.js"></script>
<script type="text/javascript" src="js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="js/init_vehicles.js?v=<?php echo $v; ?>"></script>
<script type="text/javascript" src="js/vehicles.js?v=<?php echo $v; ?>"></script>

</body>

</html>