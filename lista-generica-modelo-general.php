<?php
include_once 'functions/sesiones.php';
if ($_SESSION['nivel'] != 1) {
  header("Location: templates/not_found.php");
  exit();
}
include_once 'functions/conexion.php';
$con = conectar();
// $con->set_charset('utf8');
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
  <div class="container table-responsive py-5 px-2">
    <div class="card">
      <div class="card-header">
        <h3 class="mb-0 azul"><i class="fa-solid fa-list-check azul pr-2"></i>Listado <?php echo (isset($_GET['title']) && !empty($_GET['title'])) ? $_GET['title'] : 'Catálogos'; ?></h3>
      </div>
      <div class="card-body">
        <table id="tablaCatalogos" class="table tab-hov table-condensed table-sm table-striped w-100" data-order='[[ 1, "asc" ]]'>
          <thead class="text-center thead-dark">
            <tr>
              <th>Id</th>
              <th>Nombre</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php
include_once 'templates/footer5.php';
include_once 'templates/modals/modal_add_catalogo.php';
include_once 'templates/modals/modal_edit_catalogo.php';
include_once 'templates/footer_table.php';
?>
<script src="js/init_catalogos.js?v=<?php echo $v; ?>"></script>
<script src="js/catalogos.js?v=<?php echo $v; ?>"></script>

</body>

</html>