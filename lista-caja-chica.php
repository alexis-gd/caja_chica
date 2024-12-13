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
              <th>Folio</th>
              <th>Empresa</th>
              <th>Entrega</th>
              <th>Ingreso</th>
              <th>Gasto</th>
              <th>Autoriza</th>
              <th>Concepto</th>
              <th>Proveedor</th>
              <th>Recibe</th>
              <th>Unidad</th>
              <th>Operador</th>
              <th>Comprobante</th>
              <th>Factura</th>
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
              <th>Folio</th>
              <th>Empresa</th>
              <th>Entrega</th>
              <th>Tipo Ingreso</th>
              <th>Tipo Gasto</th>
              <th>Autoriza</th>
              <th>Concepto</th>
              <th>Proveedor</th>
              <th>Recibe</th>
              <th>Unidad</th>
              <th>Operador</th>
              <th>Comprobante</th>
              <th>Factura</th>
              <th>Ingreso</th>
              <th>Egreso</th>
              <th>Saldo</th>
            </tr>
          </tfoot>
        </table>
      </div>
      <hr>
      <section>
        <div class="row align-items-center text-center pb-3">
          <div class="col col-12 col-md-4">
            <h3>Ingreso total:</h3>
            <span class="badge badge-azul ml-2" id="total_ingreso">0</span>
          </div>
          <div class="col col-12 col-md-4">
            <h3>Egreso total: </h3>
            <span class="badge badge-azul ml-2" id="total_egreso">0</span>
          </div>
          <div class="col col-12 col-md-4">
            <h3>Monto total: </h3>
            <span class="badge badge-azul ml-2" id="total_monto">0</span>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>

<?php
include_once 'templates/footer5.php';
include_once 'templates/modals/modal_add_caja.php';
include_once 'templates/footer_table.php';
?>
<script type="text/javascript" src="js/selectize.min.js"></script>
<script type="text/javascript" src="js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="js/init_caja.js?v=<?php echo $v; ?>"></script>
<script type="text/javascript" src="js/caja.js?v=<?php echo $v; ?>"></script>

</body>

</html>