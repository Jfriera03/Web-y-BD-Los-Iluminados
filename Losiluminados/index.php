<?php
  session_start();

  $isLoggedIn = isset($_SESSION['nickname']);
  if ($isLoggedIn) {
    include '../htdocs/php/database/db_connect.php'; // Conexión a la base de datos
    $nickname = $_SESSION['nickname'];

    // Consulta para ver si el usuario tiene privilegios de ver el dashboardAdmin de administrador
    $sql = "SELECT 
              CASE WHEN COUNT(*) > 0 THEN '1' ELSE '0' END AS tienePrivilegio
            FROM 
                UsuarioPrivilegio
            WHERE 
                usuario = ?
            AND privilegio = 'Dashboard_admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nickname);
    $stmt->execute();
    $result = $stmt->get_result();
    $dashboardAdmin = 0;

    if ($result->num_rows > 0) {
      $dashboardAdmin = $result->fetch_assoc()['tienePrivilegio'];
    }

    if ($dashboardAdmin == 1) {
      $_SESSION['dashboardAdmin'] = 1;
    }

    // Consulta para ver si el usuario tiene privilegios de ver el dashboardUsuario de usuario
    $sql = "SELECT 
              CASE WHEN COUNT(*) > 0 THEN '1' ELSE '0' END AS tienePrivilegio
            FROM 
                UsuarioPrivilegio
            WHERE 
                usuario = ?
            AND privilegio = 'Dashboard_User'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nickname);
    $stmt->execute();
    $result = $stmt->get_result();
    $dashboardUser = 0;

    if ($result->num_rows > 0) {
      $dashboardUser = $result->fetch_assoc()['tienePrivilegio'];
    }

    if ($dashboardUser == 1) {
      $_SESSION['dashboardUser'] = 1;
    }

    $stmt->close();
    $conn->close();
  }

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Light It Up! - Base de Datos 2</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="author" content="">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="./css/vendor.css">
    <link rel="stylesheet" type="text/css" href="./css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Open+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
  </head>
  <body>
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
    </svg>
    <header>
      <nav class="navbar navbar-expand-lg navbar-light bg-light px-3">
        <div class="container-fluid">
          <!-- Logo a la izquierda -->
          <a class="navbar-brand" href="#">
            <img src="/images/logo.svg" alt="Logo" width="100" height="50">
          </a>
          <!-- Botón de colapso para móviles -->
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <!-- Opciones del menú -->
          <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Opciones a la derecha -->
            <ul class="navbar-nav d-flex align-items-center ms-auto">
              <!-- Iniciar sesión -->
              <li class="nav-item">
                <?php if ($isLoggedIn): ?>
                  <a class="nav-link" href="<?php echo $dashboardAdmin == 1 ? '/php/Dashboard_admin/inicio.php' : '/php/Dashboard_usuario/inicio.php'; ?>">
                    <svg width="24" height="24"><use xlink:href="#user"></use></svg>
                    Ver Perfil
                  </a>
                <?php else: ?>
                  <a class="nav-link" href="/php/Cuenta/login.php">
                    <svg width="24" height="24"><use xlink:href="#user"></use></svg>
                    Login
                  </a>
                <?php endif; ?>
              </li>

            </ul>
          </div>
        </div>
      </nav>
    </header>
    <section style="background-image: url('/images/banner_pagina.jpg');background-repeat: no-repeat;background-size: cover;">
      <div class="container-lg">
        <div class="row">
          <div class="col-lg-6 pt-1 mt-3">
            <h2 class="display-1 ls-1">
              <span class="fw-bold text-primary">Light It Up!</span> 
              <span class="text-productos">Productos a</span> 
              <span class="fw-bold text-titulo">Medida</span>
            </h2>            
            <p></p>
          </div>
        </div>
      </div>
    </section>
    <section class="py-5 overflow-hidden">
      <div class="container-lg">
        <div class="row">
          <div class="col-md-12">
            <div class="section-header d-flex flex-wrap justify-content-between mb-5">
              <h2 class="section-title">Nuestros Productos</h2>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="category-carousel swiper">
              <div class="swiper-wrapper">
                <a href="/php/Productos/maquinaVirtual.php" class="nav-link swiper-slide text-center">
                  <img src="/images/maquina_virtual.jpg" class="rounded-circle" alt="Category Thumbnail" width="160" height="160">
                  <h4 class="fs-6 mt-3 fw-normal category-title">Máquina Virtual</h4>
                </a>
                <a href="/php/Productos/baseDatos.php" class="nav-link swiper-slide text-center">
                  <img src="/images/base_de_datos.jpg" class="rounded-circle" alt="Category Thumbnail"width="160" height="160">
                  <h4 class="fs-6 mt-3 fw-normal category-title">Base de Datos</h4>
                </a>
                <a href="/php/Productos/entornoDesarrollo.php" class="nav-link swiper-slide text-center">
                  <img src="/images/entorno_desarollo.jpg" class="rounded-circle" alt="Category Thumbnail"width="160" height="160">
                  <h4 class="fs-6 mt-3 fw-normal category-title">Entorno de Desarrollo</h4>
                </a>
                <a href="/php/Productos/almacenamientoVirtual.php" class="nav-link swiper-slide text-center">
                  <img src="/images/almacenamiento_cloud.jpg" class="rounded-circle" alt="Category Thumbnail"width="160" height="160">
                  <h4 class="fs-6 mt-3 fw-normal category-title">Almacenamiento Virtual</h4>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>


    <style>
      .footer-container {
        background-color: #f7f7f7; 
        padding: 20px 0;
      }
    </style>

    <footer class="py-5 footer footer-container">
      <div class="container-lg">
        <div class="row">
          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="footer-menu">
              <img src="/images/logo.svg" width="240" height="70" alt="logo">
            </div>
          </div>
          <div class="col-md-2 col-sm-6">
            <div class="footer-menu">
              <h5 class="widget-title">Light It Up!</h5>
              <ul class="menu-list list-unstyled">
                <li class="menu-item">
                  <a href="/php/Otros/integrantes.php" class="nav-link">Sobre Nosotros</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </footer>

    <div id="footer-bottom" class="footer-container">
      <div class="container-lg">
        <div class="row">
          <div class="col-md-6 copyright">
            <p>© 2024 LosIluminados. All rights reserved.</p>
          </div>
        </div>
      </div>
    </div>



    <script src="/js/jquery-1.11.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
    <script src="/js/plugins.js"></script>
    <script src="/js/script.js"></script>
  </body>
</html>