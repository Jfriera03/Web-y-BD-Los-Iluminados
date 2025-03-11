<?php 
include '../database/db_connect.php'; // Conexión a la base de datos

session_start(); // Inicia la sesión

// Verifica si el usuario ha iniciado sesión
$isLoggedIn = isset($_SESSION['nickname']);

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
$sql_gb = "SELECT CONCAT(c.nombreCapacidad , ' - ' , u.unidadMedida) AS concat 
            FROM capacidad c
            JOIN capacidad_unidad cu ON c.nombreCapacidad = cu.nombreCapacidad
            JOIN unidad u ON cu.unidadMedida = u.unidadMedida AND u.unidadMedida='GB'
            ORDER BY c.nombreCapacidad ASC";

$result_gb = $conn->query($sql_gb);

// Consulta SQL para obtener capacidades en TB
$sql_tb = "SELECT CONCAT(c.nombreCapacidad , ' - ' , u.unidadMedida) AS concat 
            FROM capacidad c
            JOIN capacidad_unidad cu ON c.nombreCapacidad = cu.nombreCapacidad
            JOIN unidad u ON cu.unidadMedida = u.unidadMedida AND u.unidadMedida='TB'
            ORDER BY c.nombreCapacidad ASC";

$result_tb = $conn->query($sql_tb);

// Comprobar si hay resultados
$storages = [];
if ($result_gb->num_rows > 0) {
    while($row = $result_gb->fetch_assoc()) {
        $storages[] = $row;
    }   
}

if ($result_tb->num_rows > 0) {
    while($row = $result_tb->fetch_assoc()) {
        $storages[] = $row;
    }
}



$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <title>Light It Up! - Almacenamiento Virtual</title>
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
            width: 600px; 
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
            <img src="/images/almacenamientoVirtual.png" alt="Almacenamiento Virtual" class="image-resize me-3">
            <!-- Cuadro de Compra -->
            <div class="purchase-box mb-5" style="background-color: #f8f9fa; padding: 20px; border-radius: 8px;">
                
                <form  method="GET" action="configProductoAC.php" >
                <h2>Configure su Almacenamiento Virtual</h2>
                
                    
                <div class="mb-3">
                    <label for="capacidad" class="form-label">Seleccione Almacenamiento</label>
                    <select class="form-select" id="capacidad" name="capacidad" required>
                        <option value="" selected>Seleccione Almacenamiento</option>
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
                <h1>¿Qué es el Almacenamiento Virtual?</h1>
                <p>El almacenamiento virtual es una tecnología que permite a los usuarios almacenar y gestionar datos en un entorno virtualizado. Esto significa que los datos no están vinculados a un hardware físico específico, sino que se distribuyen en múltiples dispositivos y ubicaciones.</p>
                
                <h2>Ventajas</h2>
                <ul>
                    <li>Escalabilidad: Permite aumentar o disminuir la capacidad de almacenamiento según las necesidades.</li>
                    <li>Accesibilidad: Los datos pueden ser accedidos desde cualquier lugar y en cualquier momento.</li>
                    <li>Seguridad: Ofrece mecanismos avanzados para proteger los datos contra accesos no autorizados y pérdidas.</li>
                    <li>Costos Reducidos: Reduce los costos asociados con la compra y mantenimiento de hardware físico.</li>
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