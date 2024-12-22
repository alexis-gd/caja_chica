<?php
include_once 'functions/sesiones.php';
include_once 'functions/conexion.php';
$con = conectar();
include_once 'templates/header1.php';
include_once 'templates/header2.php';
?>
<!-- Aquí css por página -->
<link rel="stylesheet" type="text/css" href="css/selectize.bootstrap4.min.css" />
<link rel="stylesheet" type="text/css" href="css/flatpickr.min.css" />
</head>
<?php
include_once 'templates/barra3.php';
include_once 'templates/navegacion4.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <div class="container-fluid table-responsive py-5 px-2">
    <div class="card">
      <div class="card-header">
        <h3 class="mb-0 azul">
          <i class="fa-solid fa-cash-register azul pr-2"></i>Caja Chica
        </h3>
      </div>
      <div class="card-body">
        <table id="tablaCaja" class="table tab-hov table-condensed table-sm table-striped w-100" data-order='[[ 1, "desc" ]]'>
          <thead class="text-center thead-dark">
            <tr>
              <th>Id</th>
              <th>Fecha</th>
              <th>Cargado</th>
              <th>Área</th>
              <th>Gasto</th>
              <th>Concepto</th>
              <th>Recibe</th>
              <th>Unidad</th>
              <th>Comprobante</th>
              <th>Razón social</th>
              <th>Ingreso</th>
              <th>Egreso</th>
              <th>Saldo</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th>Id</th>
              <th>Fecha</th>
              <th>Cargado</th>
              <th>Área</th>
              <th>Gasto</th>
              <th>Concepto</th>
              <th>Recibe</th>
              <th>Unidad</th>
              <th>Comprobante</th>
              <th>Razón social</th>
              <th>Ingreso</th>
              <th>Egreso</th>
              <th>Saldo</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    
    <!-- Totales -->
    <div class="card mt-3">
      <div class="card-body">
        <div class="row align-items-center text-center pb-4">
          <div class="col col-12 col-md-4 pb-3 pb-md-0">
            <p class="f-size-sm mb-0">Ingreso total:</p>
            <span class="badge badge-azul ml-2 f-size-md" id="total_ingreso">0</span>
          </div>
          <div class="col col-12 col-md-4 pb-3 pb-md-0">
            <p class="f-size-sm mb-0">Egreso total: </p>
            <span class="badge badge-azul ml-2 f-size-md" id="total_egreso">0</span>
          </div>
          <div class="col col-12 col-md-4 pb-3 pb-md-0">
            <p class="f-size-sm mb-0">Saldo total: </p>
            <span class="badge badge-azul ml-2 f-size-md" id="total_saldo">0</span>
          </div>
        </div>
        <div class="text-center">
          <small class="small-text"><strong>Nota:</strong> Los montos mostrados corresponden a la suma total de todos los resultados, no solo de las 10 filas visibles. Los cálculos se actualizarán al aplicar filtros.</small>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
include_once 'templates/footer5.php';
include_once 'templates/modals/modal_add_caja.php';
include_once 'templates/modals/modal_edit_caja.php';
include_once 'templates/modals/modal_add_comprobante.php';
include_once 'templates/footer_table.php';
?>
<script type="text/javascript" src="js/selectize.min.js"></script>
<script type="text/javascript" src="js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="js/browser-image-compression.js"></script>
<script type="text/javascript" src="js/flatpickr.min.js"></script>
<script type="text/javascript" src="js/es.js"></script>
<script type="text/javascript" src="js/moment.min.js"></script>
<script type="text/javascript" src="js/moment-timezone-with-data.min.js"></script>
<script type="text/javascript" src="js/init_caja.js?v=<?php echo $v; ?>"></script>
<script type="text/javascript" src="js/caja.js?v=<?php echo $v; ?>"></script>
<script type="text/javascript" src="js/upload_file.js?v=<?php echo $v; ?>"></script>

</body>

</html>