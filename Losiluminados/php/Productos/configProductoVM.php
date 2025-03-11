<?php
include '../database/db_connect.php'; // Conexión a la base de datos

session_start();

$nickname = $_SESSION['nickname'];

// Verificar si se reciben los datos de CPU, RAM, SO, almacenamiento y distribución
if (!isset($_GET['cpu'], $_GET['ram'], $_GET['storage'], $_GET['os'], $_GET['dist'])) {
    die("Error: Faltan datos de CPU, RAM, almacenamiento, sistema operativo o distribución.");
}

$idCPU = $_GET['cpu'];
$idRAM = $_GET['ram'];
$idAlmacenamiento = $_GET['storage'];
$idSO = $_GET['os'];
$idDistribucion = $_GET['dist'];


// Consultas para obtener las opciones de cada campo

//////////////////CIUDAD

$sql_ciudad = "SELECT codigoCiudad, nombreCiudad FROM CIUDAD";
$result_ciudad = $conn->query($sql_ciudad);

$ciudades = [];
if ($result_ciudad->num_rows > 0) {
    while ($row = $result_ciudad->fetch_assoc()) {
        $ciudades[] = $row;
    }
    usort($ciudades, function ($a, $b) {
        return strcmp($a['nombreCiudad'], $b['nombreCiudad']);
    });
}

/////////////////// VLAN

// Seleccionamos la VLAN correspondiente a la empresa del usuario
$sql_vlan = "SELECT VLAN.idVLAN, VLAN.nombreVLAN, VLAN.ipPublica
            FROM USUARIO
            JOIN EMPRESA ON USUARIO.CIF = EMPRESA.CIF
            JOIN RED ON EMPRESA.CIF = RED.CIF
            JOIN VLAN ON RED.ipPublica = VLAN.ipPublica
            WHERE USUARIO.nickname = '$nickname'";
$result_vlan = $conn->query($sql_vlan);

////////////////// IPs

// Crear los arrays de IPs disponibles
$ipsPublicas = [
    '200.100.10.1', '200.100.10.2', '200.100.10.3', '200.100.10.4', '200.100.10.5', '200.100.10.6'
];
$ipsPrivadas = [
    '192.168.1.1', '192.168.1.2', '192.168.1.3', '192.168.1.4', '192.168.1.5', '192.168.1.6'
];

// Asignar IPs aleatorias
$ipPublica = $ipsPublicas[array_rand($ipsPublicas)];
$ipPrivada = $ipsPrivadas[array_rand($ipsPrivadas)];

///////////////// PRECIO FINAL

// Calculamos el precio individual de cada componente y después se lo sumamos al precio base del producto
$sql_precioBase = " SELECT VM.precioBase FROM VM WHERE VM.idVM = 1"; // El precio base es el mismo para todas las VM
$result_precioBase = $conn->query($sql_precioBase);
$sql_precioCPU =  " SELECT c.precio 
                    FROM CPU c
                    WHERE c.idCPU = $idCPU"; 
$result_precioCPU = $conn->query($sql_precioCPU);

$sql_precioRAM =  " SELECT r.precio 
                    FROM RAM r
                    WHERE r.idRAM = $idRAM"; 
$result_precioRAM = $conn->query($sql_precioRAM); 

$sql_precioAlmacenamiento =  "  SELECT alm.precio 
                                FROM ALMACENAMIENTO alm
                                WHERE alm.idAlmacenamiento = $idAlmacenamiento";
$result_precioAlmacenamiento = $conn->query($sql_precioAlmacenamiento);

$sql_precioDistribucion =     " SELECT d.precio 
                                FROM DISTRIBUCION d
                                WHERE d.idDistribucion = $idDistribucion"; 
$result_precioDistribucion = $conn->query($sql_precioDistribucion);


if ($result_precioCPU && $result_precioRAM && $result_precioAlmacenamiento  &&$result_precioDistribucion && $result_precioBase ) {
    // Obtener el precio base de la VM
    $rowBase = $result_precioBase->fetch_assoc();
    $precioBaseVM = $rowBase['precioBase'];
    
    // Obtener el precio del CPU
    $rowCPU = $result_precioCPU->fetch_assoc();
    $precioCPU = $rowCPU['precio'];

    // Obtener el precio de la RAM
    $rowRAM = $result_precioRAM->fetch_assoc();
    $precioRAM = $rowRAM['precio'];

    // Obtener el precio del ALMACENAMIENTO
    $rowAlmacenamiento = $result_precioAlmacenamiento->fetch_assoc();
    $precioAlmacenamiento = $rowAlmacenamiento['precio'];

    // Obtener el precio de la Distribución
    $rowDistribucion = $result_precioDistribucion->fetch_assoc();
    $precioDistribucion = $rowDistribucion['precio'];

    // Sumar los precios
    $precioTotal = $precioCPU + $precioRAM  + $precioAlmacenamiento + $precioDistribucion + $precioBaseVM ;
} else {
    echo "Error al obtener los precios.";
}

                                

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['submitProducto'])) {
    $nombreProducto = $_GET['nombreProducto'];
    $ipPrivada = $_GET['ipPrivada'];
    $ipPublica = $_GET['ipPublica'];
    $codigoCiudad = $_GET['codigoCiudad'];
    $vlan = $_GET['vlan'];
    //$precioBase = 50;

    // Validación
    if (empty($nombreProducto) || empty($ipPrivada) || empty($ipPublica) || empty($codigoCiudad)) {
        die("Error: Todos los campos del formulario son obligatorios.");
    }

    // Insertar en VM
    $stmt = $conn->prepare("INSERT INTO VM (idCPU, idRAM, idAlmacenamiento, idSO, precioBase) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiid", $idCPU, $idRAM, $idAlmacenamiento, $idSO, $precioBaseVM);
    if (!$stmt->execute()) {
        die("Error al insertar en VM: " . $stmt->error);
    }
    $idVM = $stmt->insert_id; // Obtener el ID de la VM
    $stmt->close();

    // Insertar en PRODUCTO
    $stmt = $conn->prepare("INSERT INTO PRODUCTO (nombreProducto, ipPrivada, ipPublica, codigoCiudad, idVLAN, idVM, idEtapa) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("ssssii", $nombreProducto, $ipPrivada, $ipPublica, $codigoCiudad, $vlan, $idVM);

    if ($stmt->execute()) {
        $idProducto = $conn->insert_id; // Obtener el ID del producto recién creado
        echo "Producto creado exitosamente <br>";
        $stmt = $conn->prepare("INSERT INTO COPIA_VM (precioBase, idCPU, idRAM, idAlmacenamiento, idSO, idVM, idProducto, fechaCopia) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("diiiiii", $precioBaseVM, $idCPU, $idRAM, $idAlmacenamiento, $idSO, $idVM, $idProducto);
        if ($stmt->execute()) {
            echo "Copia de VM creada exitosamente <br>";
        } else {
            die("Error al insertar en COPIA_VM: " . $stmt->error);
        }
    } else {
        die("Error al insertar en PRODUCTO: " . $stmt->error);
    }
    $stmt->close();

    // Insertar en PEDIDO
    $fechaPedido = date('Y-m-d H:i:s'); // Obtener la fecha y hora actual
    $stmt = $conn->prepare("INSERT INTO PEDIDO (fechaPedido, nickname, idProducto) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $fechaPedido, $nickname, $idProducto);
    if ($stmt->execute()) {
        echo "Pedido creado exitosamente.";
    } else {
        die("Error al insertar en PEDIDO: " . $stmt->error);
    }
    $stmt->close();


    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
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
    <meta charset="UTF-8">

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
        </div>
      </nav>
    </header>

    <main class="container mt-5">
        <h2>Configurar Producto</h2>
        <form action="" method="GET">
            <input type="hidden" name="cpu" value="<?= htmlspecialchars($idCPU) ?>">
            <input type="hidden" name="ram" value="<?= htmlspecialchars($idRAM) ?>">
            <input type="hidden" name="storage" value="<?= htmlspecialchars($idAlmacenamiento) ?>">
            <input type="hidden" name="os" value="<?= htmlspecialchars($idSO) ?>">
            <input type="hidden" name="dist" value="<?= htmlspecialchars($idDistribucion) ?>">
            <div class="mb-3">
                <label for="nombreProducto" class="form-label">Nombre del Producto</label>
                <input type="text" class="form-control" name="nombreProducto" id="nombreProducto" required>
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
                <label for="codigoCiudad" class="form-label">Ciudad donde se alojará</label>
                <select class="form-select" id="codigoCiudad" name="codigoCiudad" required>
                    <option value="" selected disabled>Seleccione una ciudad</option>
                    <?php foreach ($ciudades as $ciudad): ?>
                        <option value="<?= $ciudad['codigoCiudad'] ?>">
                            <?= $ciudad['nombreCiudad'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="vlan" class="form-label">VLAN</label>
                <select class="form-select" id="vlan" name="vlan" required>
                <option value="" disabled selected>Selecciona una VLAN</option>
                <?php while ($row = $result_vlan->fetch_assoc()) { ?>
                            <option value="<?php echo $row['idVLAN']; ?>"><?php echo $row['nombreVLAN']; ?></option>
                <?php } ?>
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

<?php
// Cerrar conexión después del uso de datos
$conn->close();
?>