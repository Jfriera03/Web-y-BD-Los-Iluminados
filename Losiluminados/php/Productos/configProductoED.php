<?php
include '../database/db_connect.php'; // Conexión a la base de datos

session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION['nickname'])) {
    die("Error: Usuario no autenticado.");
}
$nickname = $_SESSION['nickname'];

// Obtener parámetros desde GET
$languages = isset($_GET['languages']) ? $_GET['languages'] : [];
$tipoGit = $_GET['gitType'] ?? null;
$librerias = isset($_GET['libraries']) ? $_GET['libraries'] : [];

// Validar parámetros
$missingParams = [];

if (empty($languages)) {
    $missingParams[] = 'languages';
}
if (empty($librerias)) {
    $missingParams[] = 'libraries';
}

if (!empty($missingParams)) {
    echo "Error: Faltan parámetros requeridos: " . implode(', ', $missingParams) . "<br>";
}


try {

    // Obtener ciudades
    $sql_ciudades = "SELECT codigoCiudad, nombreCiudad FROM CIUDAD";
    $ciudades = $conn->query($sql_ciudades)->fetch_all(MYSQLI_ASSOC);

    // Obtener VLANs
    $sql_vlan = "SELECT VLAN.idVLAN, VLAN.nombreVLAN 
                 FROM USUARIO
                 JOIN EMPRESA ON USUARIO.CIF = EMPRESA.CIF
                 JOIN RED ON EMPRESA.CIF = RED.CIF
                 JOIN VLAN ON RED.ipPublica = VLAN.ipPublica
                 WHERE USUARIO.nickname = '$nickname'";
    $vlans = $conn->query($sql_vlan)->fetch_all(MYSQLI_ASSOC);

    // Crear los arrays de IPs disponibles
    $ipsPublicas = ['200.100.10.1', '200.100.10.2', '200.100.10.3', '200.100.10.4', '200.100.10.5', '200.100.10.6'];
    $ipsPrivadas = ['192.168.1.1', '192.168.1.2', '192.168.1.3', '192.168.1.4', '192.168.1.5', '192.168.1.6'];

    // Asignar IPs aleatorias
    $ipPublica = $ipsPublicas[array_rand($ipsPublicas)];
    $ipPrivada = $ipsPrivadas[array_rand($ipsPrivadas)];

    // Obtener precios base
    $sql_precioBaseED = "SELECT precioBase FROM ENTORNO_DESAROLLO WHERE idED = 1";
    $precioBaseED = $conn->query($sql_precioBaseED)->fetch_assoc()['precioBase'] ?? 0;

    // Obtener el precio de Git seleccionado
    $sql_precioGIT = "SELECT precioGit FROM GIT WHERE idGit = ?";
    $stmt = $conn->prepare($sql_precioGIT);
    $stmt->bind_param("i", $tipoGit);
    $stmt->execute();
    $resultGit = $stmt->get_result();
    $precioBaseGIT = $resultGit->fetch_assoc()['precioGit'] ?? 0;
    $stmt->close();

    // Obtener precio librerias
    $sql_precioLibrerias = "SELECT precioLibreria FROM LIBRERIA WHERE idLibreria IN (" . implode(',', $librerias) . ")";
    $resultLibrerias = $conn->query($sql_precioLibrerias);

    $precioTotalLibrerias = 0;
    if ($resultLibrerias) {
        while ($row = $resultLibrerias->fetch_assoc()) {
            $precioTotalLibrerias += $row['precioLibreria'];
        }
    }

    // Calcular precio total
    $precioTotal = $precioBaseED + $precioBaseGIT + $precioTotalLibrerias;

} catch (Exception $e) {
    die("Error al cargar los datos: " . $e->getMessage());
}

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['submitProducto'])) {
   
    try {
        $nombreProducto = $_GET['nombreProducto'] ?? null;
        $ipPrivada = $_GET['ipPrivada'];
        $ipPublica = $_GET['ipPublica'];
        $codigoCiudad = $_GET['codigoCiudad'] ?? null;
        $vlan = $_GET['vlan'] ?? null;
        $languages = $_GET['languages'] ?? [];
        $tipoGit = $_GET['gitType'] ?? null;
        $librerias = $_GET['libraries'] ?? [];

        if (empty($nombreProducto) || empty($codigoCiudad) || empty($vlan) || empty($tipoGit)) {
            throw new Exception("Error: Todos los campos del formulario son obligatorios.");
        }

        // Obtener el precio de Git seleccionado
        $sql_precioGIT = "SELECT precioGit FROM GIT WHERE idGit = ?";
        $stmt = $conn->prepare($sql_precioGIT);
        $stmt->bind_param("i", $tipoGit);
        $stmt->execute();
        $resultGit = $stmt->get_result();
        $precioBaseGIT = $resultGit->fetch_assoc()['precioGit'] ?? 0;
        $stmt->close();
        

        // Insertar en ENTORNO_DESAROLLO
        $stmt = $conn->prepare("INSERT INTO ENTORNO_DESAROLLO (idGit, precioBase) VALUES (?, ?)");
        $stmt->bind_param("id", $tipoGit, $precioTotal);
        $stmt->execute();
        $idED = $stmt->insert_id;
        $stmt->close();

        // Insertar en libreriadeentorno
        foreach ($librerias as $libreria) {
            $stmt = $conn->prepare("INSERT INTO libreriadeentorno (idEntornoDesarrollo, idLibreria) VALUES (?, ?)");
            $stmt->bind_param("ii", $idED, $libreria);
            $stmt->execute();
            $stmt->close();
        }

        // Insertar en lenguajedeentorno
        foreach ($languages as $language) {
            $stmt = $conn->prepare("INSERT INTO lenguajedeentorno (idEntornoDesarrollo, nombreLenguaje) VALUES (?, ?)");
            $stmt->bind_param("is", $idED, $language);
            $stmt->execute();
            $stmt->close();
        }

        // Insertar en PRODUCTO
        $stmt = $conn->prepare("INSERT INTO PRODUCTO (nombreProducto, ipPrivada, ipPublica, codigoCiudad, idVLAN, idED, idEtapa) 
                                VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("ssssii", $nombreProducto, $ipPrivada, $ipPublica, $codigoCiudad, $vlan, $idED);
        $stmt->execute();
        $idProducto = $stmt->insert_id;
        //Hacer copia de seguridad del producto
        $stmt = $conn->prepare("INSERT INTO COPIA_ED (precioBase, idGit, idED, idProducto, fechaCopia) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("diii", $precioTotal, $tipoGit, $idED, $idProducto);
        if ($stmt->execute()) {
            echo "Copia de ED creada exitosamente <br>";
        } else {
            die("Error al insertar en COPIA_ED: " . $stmt->error);
        }
        $stmt->close();

        // Insertar en PEDIDO
        $fechaPedido = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO PEDIDO (fechaPedido, nickname, idProducto) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $fechaPedido, $nickname, $idProducto);
        $stmt->execute();
        $stmt->close();

    } catch (Exception $e) {
        echo "Error al procesar el formulario: " . $e->getMessage();
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <!-- <title>Light It Up! - Configuración</title>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"> -->

    <!-- ESTO ES MIO 1 -->
    <title>Light It Up! - Máquina Virtual</title>
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }
        
        .footer-container {
            background-color: #f7f7f7;
            padding: 20px 0;
        }
        .container mt-5{

        }
        .precio-container {
            background-color: #ffffff; /* Gris claro */
            display: inline-block; /* Ocupa solo el espacio necesario */
            padding: 10px;
            border-radius: 5px;
        }
        .precio-input {
            width: auto;
            text-align: center;
            background-color: #f0f0f0; /* Fondo blanco para el input */
        }
        .precio-label {
        color: #000000; /* Negro */
        }
    </style>
    <!-- ESTO ES MIO 2 -->
</head>
<body>
<header>
  <nav class="navbar navbar-expand-lg navbar-light bg-light px-3">
    <div class="container-fluid">
      <!-- Logo a la izquierda -->
      <a class="navbar-brand" href="/index.php" target="_self">
        <img src="/images/logo.svg" alt="Logo" width="100" height="50">
      </a>
      <!-- Botón de colapso para móviles -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>
  </nav>
</header>
<main class="container mt-5">
    <h2>Configurar Producto</h2>
    <form method="GET" action="">
        <!-- Campos ocultos para lenguajes -->
        <?php foreach ($languages as $language): ?>
            <input type="hidden" name="languages[]" value="<?= htmlspecialchars($language) ?>">
        <?php endforeach; ?>

        <!-- Campos ocultos para librerías -->
        <?php foreach ($librerias as $libreria): ?>
            <input type="hidden" name="libraries[]" value="<?= htmlspecialchars($libreria) ?>">
        <?php endforeach; ?>
        
        <input type="hidden" name="gitType" value="<?= htmlspecialchars($tipoGit) ?>">

        <input type="hidden" name="precioTotal" value="<?= htmlspecialchars($precioTotal) ?>">


        <div class="mb-3">
            <label for="nombreProducto" class="form-label">Nombre del Producto</label>
            <input type="text" class="form-control" id="nombreProducto" name="nombreProducto" required>
        </div>
        <div class="mb-3">
                <label for="ipPrivada" class="form-label">IP Privada</label>
                <input type="text" class="form-control" name="ipPrivada" id="ipPrivada" value="<?= htmlspecialchars($ipPrivada) ?>" readonly>
            </div>
        <div class="mb-3">
            <label for="ipPublica" class="form-label">IP Pública</label>
            <input type="text" class="form-control" name="ipPublica" id="ipPublica" value="<?= htmlspecialchars($ipPublica) ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="codigoCiudad" class="form-label">Ciudad</label>
            <select class="form-select" id="codigoCiudad" name="codigoCiudad" required>
                <option value="" disabled selected>Seleccione una ciudad</option>
            <?php foreach ($ciudades as $ciudad): ?>
                    <option value="<?= $ciudad['codigoCiudad'] ?>"><?= $ciudad['nombreCiudad'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="vlan" class="form-label">VLAN</label>
            <select class="form-select" id="vlan" name="vlan" required>
                <option value="" selected disabled>Seleccione VLAN</option>
                <?php foreach ($vlans as $vlan): ?>
                    <option value="<?= $vlan['idVLAN'] ?>"><?= $vlan['nombreVLAN'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3 precio-container">
            <label for="precio" class="form-label precio-label"><strong>Precio total:</strong></label>
            <input type="text" class="form-control precio-input" name="precio" id="precio" value="<?= htmlspecialchars($precioTotal) ?>" readonly>
        </div>
        <div class="btn-container">
            <button type="submit" name="submitProducto" class="btn btn-primary">Guardar Producto</button>
        </div>
    </form>
</main>
</body>
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
</body>
<script src="/js/jquery-1.11.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
<script src="/js/plugins.js"></script>
<script src="/js/script.js"></script>
</html>

