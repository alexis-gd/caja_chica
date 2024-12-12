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

  <!-- Title -->
  <div class="container box2 mb-3 p-1 text-center position-relative">
    <!-- Botón para retroceder -->
    <button class="btn btn-back btn-secondary position-absolute my-0 ml-3" style="left: 0; top: 50%; transform: translateY(-50%);">
      <i class="fa-solid fa-chevron-left"></i><span class="pl-2 d-none d-md-inline-block">Regresar</span>
    </button>
    <h2 class="azul mb-0 p-1 ml-4 ml-md-0">Detalles del vehículo</h2>
  </div>

  <!-- Vehicle details -->
  <div class="container box2 my-3 p-3 min-h-250 position-relative">
    <div class="d-flex justify-content-between">
      <h3 class="d-flex align-items-center justify-content-center azul mb-0">Información general</h3>
      <button class="btn btn-success" data-toggle="modal" data-target="#modal_edit_vehicle"><i class="fa-solid fa-pen pr-2"></i>Editar</button>
    </div>

    <!-- Loader -->
    <div id="form-loader" class="justify-content-center align-items-center py-5 form-loader" style="display: none;">
      <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Cargando...</span>
      </div>
    </div>

    <!-- Formulario -->
    <div class="form-row" id="form-vehicle-details" style="display: none;">
      <!-- Id -->
      <div class="form-group mb-0 col-md-3">
        <label for="vehicle_detail_vehicle_id" class="col-form-label pb-0">Vehículo id:</label>
        <input type="text" class="form-control input-readonly" id="vehicle_detail_vehicle_id" name="vehicle_detail_vehicle_id" readonly>
      </div>
      <!-- Marca -->
      <div class="form-group mb-0 col-md-3">
        <label for="vehicle_detail_brand" class="col-form-label pb-0">Marca:</label>
        <select name="vehicle_detail_brand" id="vehicle_detail_brand" class="form-control input-readonly selectize" disabled></select>
      </div>
      <!-- Modelo -->
      <div class="form-group mb-0 col-md-3">
        <label for="vehicle_detail_model" class="col-form-label pb-0">Modelo:</label>
        <select name="vehicle_detail_model" id="vehicle_detail_model" class="form-control input-readonly selectize" disabled></select>
      </div>
      <!-- Año -->
      <div class="form-group mb-0 col-md-3">
        <label for="vehicle_detail_year" class="col-form-label pb-0">Año:</label>
        <input type="text" class="form-control input-readonly" id="vehicle_detail_year" name="vehicle_detail_year" readonly>
      </div>
      <!-- Color -->
      <div class="form-group mb-0 col-md-6">
        <label for="vehicle_detail_color" class="col-form-label pb-0">Color:</label>
        <select name="vehicle_detail_color" id="vehicle_detail_color" class="form-control input-readonly selectize" disabled></select>
      </div>
      <!-- Serie -->
      <div class="form-group mb-0 col-md-3">
        <label for="vehicle_detail_serie" class="col-form-label pb-0">Serie:</label>
        <input type="text" class="form-control input-readonly" id="vehicle_detail_serie" name="vehicle_detail_serie" readonly>
      </div>
      <!-- Motor -->
      <div class="form-group mb-0 col-md-3">
        <label for="vehicle_detail_engine" class="col-form-label pb-0">Motor:</label>
        <input type="text" class="form-control input-readonly" id="vehicle_detail_engine" name="vehicle_detail_engine" readonly>
      </div>
      <!-- Pedimento -->
      <div class="form-group mb-0 col-md-3">
        <label for="vehicle_detail_pedimento" class="col-form-label pb-0">Pedimento:</label>
        <input type="text" class="form-control input-readonly" id="vehicle_detail_pedimento" name="vehicle_detail_pedimento" readonly>
      </div>
      <!-- Propietario -->
      <div class="form-group mb-0 col-md-3">
        <label for="vehicle_detail_owner" class="col-form-label pb-0">Propietario:</label>
        <select name="vehicle_detail_owner" id="vehicle_detail_owner" class="form-control input-readonly selectize" disabled></select>
      </div>
      <!-- Placa -->
      <div class="form-group mb-0 col-md-3">
        <label for="vehicle_detail_license_plate" class="col-form-label pb-0">Placa:</label>
        <input type="text" class="form-control input-readonly" id="vehicle_detail_license_plate" name="vehicle_detail_license_plate" readonly>
      </div>
      <!-- Observaciones -->
      <div class="form-group mb-0 col-md-6">
        <label for="vehicle_detail_observations" class="col-form-label pb-0">Observaciones:</label>
        <textarea class="form-control input-readonly" id="vehicle_detail_observations" name="vehicle_detail_observations" readonly></textarea>
      </div>
      <!-- Entrega -->
      <div class="form-group mb-0 col-md-3">
        <label for="vehicle_detail_delivery_date" class="col-form-label pb-0">Entrega:</label>
        <input type="date" class="form-control input-readonly" id="vehicle_detail_delivery_date" name="vehicle_detail_delivery_date" readonly>
      </div>
    </div>
  </div>

  <!-- Subir archivos -->
  <div class="d-flex">
    <form id="form" method="POST" enctype="multipart/form-data">
      <div class="d-flex justify-content-center mb-3">
        <div class="container box3 w-100ms p-3">
          <h3 class="azul mb-0">Subir archivos</h3>

          <!-- Tipo de archivo -->
          <div class="form-group col-md-4 px-0">
            <label for="vehicle_detail_upload_file" class="col-form-label">Tipo de archivo:</label>
            <div class="d-flex align-items-center">
              <select name="vehicle_detail_upload_file" id="vehicle_detail_upload_file" class="form-control selectize me-2" autocomplete="new-password"></select>
              <button type="button" class="btn btn-success p-2 m-0 ml-1 text-nowrap" data-toggle="modal" data-target="#modal_add_model">
                <i class="fa-solid fa-plus pr-1"></i>Nuevo
              </button>
            </div>
          </div>
          <!-- Observaciones -->
          <div class="card-body px-0 py-1">
            <label for="check_visible" class="pr-2 mb-0">Agregar observaciones</label>
            <input type="checkbox" id="check_visible" name="check_visible" data-bootstrap-switch data-off-color="danger" data-on-color="success" data-on-text="Si" data-off-text="No">
          </div>
          <div class="form-group mb-0 px-0 col-md-4" id="observation-group" style="display: none; transition: all 0.5s;">
            <label for="vehicle_detail_upload_observations" class="col-form-label pb-0">Observaciones:</label>
            <textarea class="form-control" id="vehicle_detail_upload_observations" name="vehicle_detail_upload_observations"></textarea>
          </div>
          <!-- Archivo -->
          <div class="d-flex justify-content-center justify-content-lg-start flex-wrap py-3 gap-2">
            <div class="custom-file">
              <input type="file" name="product_file" id="file" class="custom-file-input" />
              <label for="file" class="custom-file-label" id="file-label">Selecciona o arrastra un archivo<img src="img/puntero.svg" alt="puntero"></label>
            </div>
            <div class="box-button m-0">
              <button type="submit" name="upload" id="upload" class="btn btn-primary m-0 text-nowrap"><i class="fa-solid fa-upload pr-2"></i>Subir archivo</button>
            </div>
          </div>
          <div class="d-flex gap-1">
            <p class="custom-file-smalls mb-0"><b>Aviso:</b> Sube 1 archivo a la vez</p>
            <p class="custom-file-smalls mb-0"><b>Formato:</b> .pdf .jpg .png</p>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Lista de archivos -->
  <div class="d-flex">
    <div class="d-flex justify-content-center w-100 mb-3">
      <div class="container box3 w-100ms position-relative p-3">
        <h3 class="azul mb-3">Lista de archivos</h3>
        <div id="lista_archivos_cargados"></div>
        <div id="loader" class="justify-content-center align-items-center py-5 form-loader" style="display: none;">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Cargando...</span>
          </div>
        </div>
        <div id="no-files" class="text-center text-muted no-files justify-content-center align-content-center" style="display: none;">
          <p>No hay archivos disponibles por el momento.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Historial de archivos -->
  <div class="d-flex" id="printableContent">
    <div class="d-flex justify-content-center w-100">
      <div class="container box3 w-100ms position-relative p-3">
        <div class="d-flex align-items-center mb-3">
          <h3 class="azul mb-0 pr-2">Historial de movimientos</h3>
          <div class="d-flex flex-column flex-md-row text-nowrap">
            <button class="btn btn-danger btn-sm mr-0 mr-md-2 no-print" data-toggle="modal" data-target="#modal_output_vehicle"><i class="fa-solid fa-boxes-packing pr-2"></i>Dar salida</button>
            <button class="btn btn-secondary btn-sm mr-0 mr-md-2 no-print" data-toggle="modal" data-target="#modal_return_vehicle"><i class="fa-solid fa-people-carry-box pr-2"></i>Devoluciones</button>
            <button class="btn btn-success btn-sm mr-0 mr-md-2 no-print" data-toggle="modal" data-target="#modal_observation_vehicle"><i class="fa-solid fa-file-pen pr-2"></i>Nueva observación</button>
            <button class="btn btn-primary btn-sm mr-0 mr-md-2 no-print d-none" onclick="printElement('printableContent')"><i class="fa-solid fa-print pr-2"></i>Imprimir</button>
          </div>
        </div>
        <div id="tabla_historial_observaciones" class="table-responsive"></div>
        <div id="loader-history" class="justify-content-center align-items-center py-5 form-loader" style="display: none;">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Cargando...</span>
          </div>
        </div>
        <div id="no-files-history" class="text-center text-muted no-files justify-content-center align-content-center" style="display: none;">
          <p>No hay historial disponible por el momento.</p>
        </div>
      </div>
    </div>
  </div>

</div>


<?php
include_once 'templates/footer5.php';
include_once 'templates/modals/modal_edit_vehicle.php';
include_once 'templates/modals/modal_add_model.php';
include_once 'templates/modals/modal_output_vehicle.php';
include_once 'templates/modals/modal_return_vehicle.php';
include_once 'templates/modals/modal_observation_vehicle.php';
include_once 'templates/footer_table.php';
?>
<script type="text/javascript" src="js/selectize.min.js"></script>
<script type="text/javascript" src="js/bootstrap-switch.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/browser-image-compression@latest/dist/browser-image-compression.js"></script>
<script type="text/javascript" src="js/init_vehicle_detail.js?v=<?php echo $v; ?>"></script>
<script type="text/javascript" src="js/vehicle_detail.js?v=<?php echo $v; ?>"></script>

</body>

</html>