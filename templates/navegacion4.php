<?php
// Obtener la URL actual
$current_page = basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']);

// Verificar si la página es 'nomina-usuarios.php'
$dashboard_active = ($current_page == 'dashboard.php') ? 'active' : '';
$caja_active = ($current_page == 'lista-caja-chica.php') ? 'active' : '';
$nomina_active = ($current_page == 'nomina-usuarios.php') ? 'active' : '';
$inventario_active = ($current_page == 'inventario-usuarios.php') ? 'active' : '';

$caja_open = ($caja_active) ? 'menu-open' : '';
$nomina_open = ($nomina_active) ? 'menu-open' : '';
$inventario_open = ($inventario_active) ? 'menu-open' : '';
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
          <a href="dashboard.php" class="nav-link <?php echo $dashboard_active; ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>
        <!-- Vehículos -->
        <li class="nav-item <?php echo $caja_open; ?>">
          <a href="#" class="nav-link">
            <i class="nav-icon fa-solid fa-vault"></i>
            <p>
              Caja chica
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="lista-caja-chica.php" class="nav-link <?php echo $caja_active; ?>">
                <i class="fa-solid fa-solid fa-folder-open"></i>
                <p>Caja</p>
              </a>
            </li>
          </ul>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="lista-cuentas.php" class="nav-link <?php echo $vehicles_active; ?>">
                <i class="fa-solid fa-solid fa-folder-open"></i>
                <p>Cuentas</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Administradores -->

        <?php //if($_SESSION['nivel'] == 1): 
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