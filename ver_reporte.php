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
<div class="content-wrapper p-2 p-md-4 pt-3">
  <div class="container p-3">
    <div class="d-flex flex-column justify-content-center">
      <div class="row mx-0 justify-content-between align-items-center title-form mb-3">
        <div class="col-6">
          <p class="title mb-0">Nota de salida</p>
        </div>
        <div class="col-6">
          <p class="title mb-0">Grupo Uribe</p>
        </div>
      </div>
      <div class="row align-items-end mx-0 mb-3">
        <div class="col-3 print-mw-100">
          <p class="mb-0 pr-2">Folio:</p>
          <p id="folio" class="box-data mb-0"></p>
        </div>
        <div class="col-3 print-mw-100">
          <p class="mb-0 pr-2">Fecha del sistema:</p>
          <p id="creado" class="box-data mb-0"></p>
        </div>
        <div class="col-3 print-mw-100">
          <p class="mb-0 pr-2">Hora del sistema:</p>
          <p id="hora" class="box-data mb-0"></p>
        </div>
      </div>
      <div class="row align-items-end mx-0 mb-3">
        <div class="col-12 print-mw-100">
          <p class="mb-0 pr-2">Concepto de salida:</p>
          <p id="concepto" class="box-data text-left mb-0"></p>
        </div>
      </div>

      <div class="table-responsive pt-2">
        <table class="table tab-hov table-condensed table-sm table-striped w-100 table-bordered">
          <thead class="text-center thead-dark">
            <tr>
              <th>Archivo</th>
              <th>Fecha de salida</th>
            </tr>
          </thead>
          <tbody id="tableStock" class="text-center">
          </tbody>
        </table>
      </div>

      <div class="row justify-content-around text-center mt-5">
        <div class="col-4">
          <p class="mb-0" id="firma_entrega"></p>
          <p class="firma mb-0">(Entrega)</p>
          <p>Firma</p>
        </div>
        <div class="col-4">
          <p class="mb-0" id="firma_recibe"></p>
          <p class="firma mb-0">(Recibe)</p>
          <p>Firma</p>
        </div>
      </div>

      <div class="text-center mb-5">
        <button type="button" class="print btn btn-secondary px-5">Imprimir</button>
      </div>
    </div>
  </div>
</div>


<?php
include_once 'templates/footer5.php';
include_once 'templates/footer_table.php';
?>
<!-- <script type="text/javascript" src="js/selectize.min.js"></script> -->
<script type="text/javascript" src="js/ver_reporte.js?v=<?php echo $v; ?>"></script>
<!-- <script type="text/javascript" src="js/vehicle_detail.js?v=<?php echo $v; ?>"></script> -->

</body>

</html>