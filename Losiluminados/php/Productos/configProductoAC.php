<?php  
include '../database/db_connect.php'; // Conexión a la base de datos

session_start();

if (!isset($_SESSION['nickname'])) {
    die("Error: Usuario no autenticado.");
}
$nickname = $_SESSION['nickname'];

// Captura los datos del formulario
$capacidad = $_GET['capacidad'];


// Consultas para obtener las opciones de cada campo
$sql_ciudad = "SELECT codigoCiudad, nombreCiudad FROM CIUDAD";
$result_ciudad = $conn->query($sql_ciudad);

$sql_vlan = "SELECT VLAN.idVLAN, VLAN.nombreVLAN, VLAN.ipPublica
            FROM USUARIO
            JOIN EMPRESA ON USUARIO.CIF = EMPRESA.CIF
            JOIN RED ON EMPRESA.CIF = RED.CIF
            JOIN VLAN ON RED.ipPublica = VLAN.ipPublica
            WHERE USUARIO.nickname = '$nickname'";
$result_vlan = $conn->query($sql_vlan);



$ciudades = [];
if ($result_ciudad->num_rows > 0) {
    while($row = $result_ciudad->fetch_assoc()) {
        $ciudades[] = $row;
    }
    // Ordenar por nombre de ciudad
    usort($ciudades, function($a, $b) {
        return strcmp($a['nombreCiudad'], $b['nombreCiudad']);
    });
}

// Seleccionamos la capacidad
if (isset($_GET['capacidad'])) {
    // Extraer el nombre de la capacidad de la cadena "nombreCapacidad - unidadMedida"
        $capacidadSeleccionada = $_GET['capacidad'];
        $capacidadParts = explode(" - ", $capacidadSeleccionada);
        $capacidad = $capacidadParts[0]; // El nombre de la capacidad
}


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

///////////////// PRECIO FINAL DEL ALMACENAMIENTO ///////////////////////
// Calculamos el precio individual de cada componente y después se lo sumamos al precio base del producto
$sql_precioBase = " SELECT AC.precioBase FROM ALMACENAMIENTO_CLOUD AC WHERE AC.idAC = 1"; // El precio base es el mismo para todos los AC
$result_precioBase = $conn->query($sql_precioBase);
$sql_precioAlmacenamiento =  " SELECT cu.precio 
                    FROM capacidad_unidad cu
                    WHERE cu.nombreCapacidad = '$capacidad' AND cu.unidadMedida = 'GB'"; 
$result_precioAlmacenamiento = $conn->query($sql_precioAlmacenamiento);



if ($result_precioAlmacenamiento && $result_precioBase ) {
    // Obtener el precio base del AC
    $rowBase = $result_precioBase->fetch_assoc();
    $precioBaseAC = $rowBase['precioBase'];
    
    // Obtener el precio del Almacenaiento
    $rowAlmacenamiento = $result_precioAlmacenamiento->fetch_assoc();
    $precioAlmacenamiento = $rowAlmacenamiento['precio'];

    // Sumar los precios
    $precioTotal = $precioAlmacenamiento + $precioBaseAC ;
} else {
    echo "Error al obtener los precios.";
}


// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['submitProducto'])) {
    $capacidad = $_GET['capacidad'];
    //EL PRECIO Y LA VLAN SE ASIGNAN POR DEFECTO HASTA QUE ARREGLEMOS ESTO
    //$precioBase = 20.99;
    $idVLAN = 1;
    $nombreProducto = $_GET['nombreProducto'];
    $ipPrivada = $_GET['ipPrivada'];
    $ipPublica = $_GET['ipPublica'];
    $codigoCiudad = $_GET['codigoCiudad'];
    $nickname = $_SESSION['nickname']; // Usuario que realiza el pedido

    // Validación de entrada
    $errores = [];
    if (empty($capacidad)) $errores[] = "Capacidad";
    if (empty($nombreProducto)) $errores[] = "Nombre del Producto";
    if (empty($ipPrivada)) $errores[] = "IP Privada";
    if (empty($ipPublica)) $errores[] = "IP Pública";
    if (empty($codigoCiudad)) $errores[] = "Ciudad";

    // Seleccionamos la capacidad
    
    if (!empty($errores)) {
        die("Error: Todos los campos son obligatorios. Faltan: " . implode(", ", $errores));
    }



    $conn->begin_transaction();

    try {
        // Insertar en ALMACENAMIENTO_CLOUD
        $stmtAC = $conn->prepare("INSERT INTO ALMACENAMIENTO_CLOUD (precioBase) VALUES (?)");
        $stmtAC->bind_param("d", $precioTotal);
        if (!$stmtAC->execute()) {
            throw new Exception("Error al crear el Almacenamiento Cloud: " . $stmtAC->error);
        }
        $idAC = $stmtAC->insert_id; // Obtener el idAC generado
        $stmtAC->close();
        
        // Insertar en la tabla CapacidadBD
        $stmt = $conn->prepare("INSERT INTO CapacidadCloud (nombreCapacidad,idAC) VALUES (?, ?)");
        $stmt->bind_param("si", $capacidadSeleccionada,$idAC);
        if (!$stmt->execute()) {
            die("Error al insertar en CapacidadBD: " . $stmt->error);
        }
        $stmt->close();

        // Insertar en PRODUCTO
        $stmtProducto = $conn->prepare("INSERT INTO PRODUCTO (nombreProducto, ipPrivada, ipPublica, codigoCiudad, idAC, idVLAN, idEtapa) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmtProducto->bind_param("ssssii", $nombreProducto, $ipPrivada, $ipPublica, $codigoCiudad, $idAC, $idVLAN);
        if (!$stmtProducto->execute()) {
            throw new Exception("Error al crear el Producto: " . $stmtProducto->error);
        }
        $idProducto = $stmtProducto->insert_id; // Obtener el idProducto generado
        $stmtProducto->close();

        // Insertar en PEDIDO
        $stmtPedido = $conn->prepare("INSERT INTO PEDIDO (fechaPedido, nickname, idProducto) VALUES (NOW(), ?, ?)");
        $stmtPedido->bind_param("si", $nickname, $idProducto);
        if (!$stmtPedido->execute()) {
            throw new Exception("Error al crear el Pedido: " . $stmtPedido->error);
        }else{
            $stmt = $conn->prepare("INSERT INTO COPIA_AC (precioBase, idCapacidad, idAC, idProducto, fechaCopia) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("diii", $precioBaseAC, $capacidadSeleccionada, $idAC, $idProducto);
            if ($stmt->execute()) {
                echo "Copia de AC creada exitosamente <br>";
            } else {
                die("Error al insertar en COPIA_AC: " . $stmt->error);
            }
        }
        $stmtPedido->close();

        // Confirmar la transacción
        $conn->commit();
        echo "Pedido creado exitosamente.";
    } catch (Exception $e) {
        echo "SE HA GENERADO UN ERROR: " . $e->getMessage();
    }

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
            <input type="hidden" name="capacidad" value="<?= htmlspecialchars($capacidad) ?>">
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
                    <option value="" selected disabled>Seleccione VLAN</option>
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