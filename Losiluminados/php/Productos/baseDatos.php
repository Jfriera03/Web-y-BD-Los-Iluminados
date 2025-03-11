<?php 
  include '../database/db_connect.php'; // Conexión a la base de datos

  session_start(); // Inicia la sesión

  // Verifica si el usuario ha iniciado sesión
  $isLoggedIn = isset($_SESSION['nickname']); // Se define la variable para evitar el "undefined variable" warning

// Consulta para los privilegios
$sqlComprar = "SELECT CASE WHEN COUNT(*) > 0 THEN '1' ELSE '0' END AS tienePrivilegio
FROM UsuarioPrivilegio
WHERE usuario = ? AND privilegio = 'Comprar_Productos'";
$stmt = $conn->prepare($sqlComprar);
$stmt->bind_param("s", $_SESSION['nickname']);
$stmt->execute();
$result = $stmt->get_result();
$privilegioCompra = $result->fetch_assoc()['tienePrivilegio'] ?? '0'; // Maneja el caso en que no se devuelvan resultados
$stmt->close(); // Cierra el statement para liberar los resultados


  // Consulta SQL para obtener capacidades en GB
  $sql_capacidad_GB = "SELECT CONCAT(c.nombreCapacidad , ' - ' , u.unidadMedida) AS concat 
            FROM CAPACIDAD c
            JOIN capacidad_unidad cu ON c.nombreCapacidad = cu.nombreCapacidad
            JOIN unidad u ON cu.unidadMedida = u.unidadMedida AND u.unidadMedida='GB'
            ORDER BY c.nombreCapacidad ASC";
  $result_capacidad_GB = $conn->query($sql_capacidad_GB);

  $sql_sgbd = "SELECT idSGBD, nombreSGBD FROM SGBD";
  $result_sgbd = $conn->query($sql_sgbd);

  // Consulta SQL para obtener capacidades en TB
  $sql_capacidad_TB = "SELECT CONCAT(c.nombreCapacidad , ' - ' , u.unidadMedida) AS concat 
            FROM CAPACIDAD c
            JOIN capacidad_unidad cu ON c.nombreCapacidad = cu.nombreCapacidad
            JOIN unidad u ON cu.unidadMedida = u.unidadMedida AND u.unidadMedida='TB'
            ORDER BY c.nombreCapacidad ASC";

  $result_capacidad_TB = $conn->query($sql_capacidad_TB);

  // Comprobar si hay resultados
  $storages = [];
  if ($result_capacidad_GB->num_rows > 0) {
    while($row = $result_capacidad_GB->fetch_assoc()) {
    $storages[] = $row;
    } 
  }

  if ($result_capacidad_TB->num_rows > 0) {
    while($row = $result_capacidad_TB->fetch_assoc()) {
    $storages[] = $row;
    }
  }

  $conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <title>Light It Up! - Base de Datos</title>
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
    <link rel="stylesheet" type="text/css" href="/css/vendor.css">
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Open+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }
        .content-container {
            display: flex;
            align-items: flex-start;
            margin-top: 50px;
        }
        .content-container img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-right: 20px;
        }
        .content-container .info {
            max-width: 600px;
        }
        .image-resize {
            width: 500px; 
            height: auto;
        }
        .info {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
    </style>
</head>
<body>
<header>
      <nav class="navbar navbar-expand-lg navbar-light bg-light px-3">
        <div class="container-fluid">
          <!-- Logo a la izquierda -->
          <a class="navbar-brand" href="/index.php">
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
                  <a class="nav-link" href="/php/Dashboard_usuario/inicio.php">
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

    <main class="container mt-5">
        <div class="d-flex align-items-start">
            <img src="/images/baseDatos.png" alt="Base de Datos" class="image-resize me-3">
            <!-- Cuadro de Compra -->
            <div class="purchase-box mb-5" style="background-color: #f8f9fa; padding: 20px; border-radius: 8px;">
                <h2>Configure su Base de Datos</h2>
                <form method="GET" action="configProductoBD.php">
                  <div class="mb-3">
                    <label for="numConexiones" class="form-label">Número de Conexiones</label>
                    <input type="number" class="form-control" id="numConexiones" name="numConexiones" min="1" max="100" required>
                  </div>
                  <div class="mb-3">
                    <label for="recuperarDatos" class="form-label">Recuperar Datos</label>
                    <select class="form-select" id="recuperarDatos" name="recuperarDatos" required>
                      <option value="" disabled selected>Seleccione una opción</option>
                      <option value="1">Sí</option>
                      <option value="0">No</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label for="controlAcceso" class="form-label">Control de acceso</label>
                    <select class="form-select" id="controlAcceso" name="controlAcceso" required>
                      <option value="" disabled selected>Seleccione una opción</option>
                      <option value="1">Sí</option>
                      <option value="0">No</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label for="sgbd" class="form-label">Sistema de Gestión de Base de Datos</label>
                    <select class="form-select" id="sgbd" name="sgbd" required>
                      <option value="" disabled selected>Seleccione SGBD</option>
                      <?php while ($row = $result_sgbd->fetch_assoc()): ?>
                        <option value="<?= $row['idSGBD'] ?>"><?= $row['nombreSGBD'] ?></option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label for="sgbd" class="form-label">Capacidad de la Base de Datos</label>
                    <select class="form-select" id="capacidad" name="capacidad" required>
                      <option value="" disabled selected>Seleccione Capacidad</option>
                      <?php
                        foreach ($storages as $storage) {
                            echo '<option value="' . $storage['concat'] . '">' . $storage['concat'] . '</option>';
                        }
                        ?>
                    </select>
                  </div>
                  <?php if ($isLoggedIn && $privilegioCompra): ?>
                        <button type="submit" class="btn btn-primary">Comprar</button>
                  <?php else: ?>
                      <?php if (!$isLoggedIn): ?>
                        <button class="btn btn-secondary" disabled>Inicie sesión para comprar</button>
                      <?php else: ?>
                        <button class="btn btn-secondary" disabled>No tiene permisos para comprar</button>
                      <?php endif; ?> 
                  <?php endif; ?> 
              </form>
            </div>
        </div>
        <!-- Fin Cuadro de Compra -->
        <div class="content-container">
            <div >
                <h1>¿Qué es una Base de Datos?</h1>
                <p>Una base de datos es un conjunto de datos organizados de manera que se puedan acceder, gestionar y actualizar fácilmente. Las bases de datos son esenciales para almacenar y recuperar información de manera eficiente.</p>
                
                <h2>Ventajas</h2>
                <ul>
                    <li>Organización: Permiten organizar grandes cantidades de datos de manera estructurada.</li>
                    <li>Acceso Rápido: Facilitan el acceso rápido a la información almacenada.</li>
                    <li>Seguridad: Ofrecen mecanismos para proteger los datos contra accesos no autorizados.</li>
                    <li>Integridad: Aseguran la precisión y consistencia de los datos a lo largo del tiempo.</li>
                </ul>
            </div>
        </div>
    </main>

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