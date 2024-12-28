<?php
// Obtener la URL actual y la variable modelo
$current_page = basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']);
$modelo = isset($_GET['model']) ? $_GET['model'] : '';

// Función para verificar si una página está activa
function isActive($page, $model = '')
{
  global $current_page, $modelo;
  return ($current_page == $page && $modelo == $model) ? 'active' : '';
}

// Función para verificar si un menú debe estar abierto
function isMenuOpen($pages)
{
  global $current_page;
  return in_array($current_page, $pages) ? 'menu-open' : '';
}
?>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">

  <!-- Brand Logo -->

  <a href="#" class="brand-link">
    <img src="./img/<?php echo $marca['logotipo']; ?>" alt="AdminLTE Logo" class="brand-image" style="opacity: .8">
    <span class="brand-text "><?php echo $marca['nombre']; ?></span>
  </a>

  <!-- Sidebar -->

  <div class="sidebar">
    <!-- Sidebar user (optional) -->
    <div class="user-panel my-3 pb-3 d-flex">
      <div class="image d-flex align-items-center pl-3">
        <!-- <img src="img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image"> -->
        <a href="#" class="d-block"><i class="fas fa-user-circle"></i></a>
      </div>
      <div class="info">
        <a href="#" class="d-block"><?php echo ucwords($_SESSION['nombre']); ?></a>
      </div>
    </div>
    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
          <a href="dashboard.php" class="nav-link <?php echo isActive('dashboard.php'); ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>
        <!-- Caja chica -->
        <li class="nav-item <?php echo isMenuOpen(['lista-caja-chica.php']); ?>">
          <a href="#" class="nav-link">
            <i class="nav-icon fa-solid fa-vault"></i>
            <p>
              Caja General
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="lista-caja-chica.php" class="nav-link <?php echo isActive('lista-caja-chica.php'); ?>">
                <i class="fa-solid fa-solid fa-folder-open"></i>
                <p>Ver registros</p>
              </a>
            </li>
            <!-- Catálogos -->
            <?php if ($_SESSION['nivel'] == 1):
            ?>
              <li class="nav-item <?php echo isMenuOpen(['lista-generica-modelo.php']); ?>">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-list-alt"></i>
                  <p>
                    Listas de conceptos
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="lista-generica-modelo.php?model=modelo_cargado&title=Cargado a" class="nav-link <?php echo isActive('lista-generica-modelo.php', 'modelo_cargado'); ?>">
                      <i class="fa-solid fa-user-tag nav-icon"></i>
                      <p>Cargado a</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="lista-generica-modelo.php?model=modelo_area&title=Área" class="nav-link <?php echo isActive('lista-generica-modelo.php', 'modelo_area'); ?>">
                      <i class="fa-solid fa-map-marker-alt nav-icon"></i>
                      <p>Área</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="lista-generica-modelo.php?model=modelo_tipo_gasto&title=Tipo de gasto" class="nav-link <?php echo isActive('lista-generica-modelo.php', 'modelo_tipo_gasto'); ?>">
                      <i class="fa-solid fa-money-bill-transfer nav-icon"></i>
                      <p>Tipo de gasto</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="lista-generica-modelo.php?model=modelo_recibe&title=Recibe" class="nav-link <?php echo isActive('lista-generica-modelo.php', 'modelo_recibe'); ?>">
                      <i class="fa-solid fa-id-card nav-icon"></i>
                      <p>Recibe</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="lista-generica-modelo.php?model=modelo_unidad&title=Unidad" class="nav-link <?php echo isActive('lista-generica-modelo.php', 'modelo_unidad'); ?>">
                      <i class="fa-solid fa-truck nav-icon"></i>
                      <p>Unidad</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="lista-generica-modelo.php?model=modelo_comprobante&title=Comprobante" class="nav-link <?php echo isActive('lista-generica-modelo.php', 'modelo_comprobante'); ?>">
                      <i class="fa-solid fa-file-invoice nav-icon"></i>
                      <p>Comprobante</p>
                    </a>
                  </li>
                </ul>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="lista-generica-modelo.php?model=modelo_razon_social&title=Razón social" class="nav-link <?php echo isActive('lista-generica-modelo.php', 'modelo_razon_social'); ?>">
                      <i class="fa-solid fa-building-columns nav-icon"></i>
                      <p>Razón social</p>
                    </a>
                  </li>
                </ul>
              </li>
            <?php endif;
            ?>
          </ul>
        </li>

        <!-- Administradores -->

        <?php if ($_SESSION['nivel'] == 1):
        ?>

        <?php endif;
        ?>
        <!-- <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-user-shield"></i>
            <p>
              Administradores
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>

          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="lista-admin.php" class="nav-link">
                <i class="fas fa-clipboard-list nav-icon"></i>
                <p>Ver todos</p>
              </a>
            </li>
          </ul>

          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="crear-admin.php" class="nav-link">
                <i class="fas fa-plus-circle nav-icon"></i>
                <p>Agregar</p>
              </a>
            </li>
          </ul>
        </li> -->

        <?php //endif; 
        ?>

        <!-- Seccion banner -->
        <!-- <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-tasks"></i>
            <p>Secciones<i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="lista-banner.php" class="nav-link">
                <i class="far fa-image nav-icon"></i>
                <p>Banner</p>
              </a>
            </li>
          </ul>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="lista-footer.php" class="nav-link">
                <i class="fas fa-info-circle nav-icon"></i>
                <p>Pie de página</p>
              </a>
            </li>
          </ul>
        </li> -->
      </ul>

    </nav>

    <!-- /.sidebar-menu -->

  </div>

  <!-- /.sidebar -->

</aside>