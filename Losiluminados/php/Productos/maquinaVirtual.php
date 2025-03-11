<?php 
  include '../database/db_connect.php'; //Conexión a la base de datos

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

// Consultas para obtener las opciones de cada campo
$sql_cpu = "SELECT c.idCPU, c.modelo FROM CPU c LEFT JOIN VM vm ON c.idCPU = vm.idCPU WHERE vm.idCPU IS NULL";
$sql_ram = "SELECT r.idRAM, r.modelo FROM RAM r LEFT JOIN VM vm ON r.idRAM = vm.idRAM WHERE vm.idRAM IS NULL";
$sql_storage = "SELECT a.idAlmacenamiento, CONCAT(a.nombreAlmacenamiento, ' - ', a.tipo, ' - ', a.capacidad, ' - ', a.unidadCapacidad) AS descCompleta FROM ALMACENAMIENTO a
                            LEFT JOIN VM vm ON a.idAlmacenamiento = vm.idAlmacenamiento WHERE vm.idAlmacenamiento IS NULL ";
$sql_os = "SELECT idSO, nombreSO 
            FROM SISTEMA_OPERATIVO";
$sql_distribucion = "SELECT idDistribucion, nombreDistribucion, idSO FROM distribucion";        
$sql_version_distribucion = "SELECT numeroVersion, idDistribucion FROM versiondedistribucion";

// Ejecutar las consultas
$result_cpu = $conn->query($sql_cpu);
$result_ram = $conn->query($sql_ram);
$result_storage = $conn->query($sql_storage);
$result_os = $conn->query($sql_os);
$result_distribucion = $conn->query($sql_distribucion);
$result_version_distribucion = $conn->query($sql_version_distribucion);

// Verifica si la consulta devuelve resultados
$cpus = [];
if ($result_cpu->num_rows > 0) {
    while($row = $result_cpu->fetch_assoc()) {
        $cpus[] = $row;
    }
    usort($cpus, function($a, $b) {
        return strcmp($a['modelo'], $b['modelo']);
    });
}

$rams = [];
if ($result_ram->num_rows > 0) {
    while($row = $result_ram->fetch_assoc()) {
        $rams[] = $row;
    }
    usort($rams, function($a, $b) {
        return strcmp($a['modelo'], $b['modelo']);
    });
}

$storages = [];
if ($result_storage->num_rows > 0) {
    while($row = $result_storage->fetch_assoc()) {
        $storages[] = $row;
    }
    usort($storages, function($a, $b) {
        return strcmp($a['descCompleta'], $b['descCompleta']);
    });
}

$oss = [];
if ($result_os->num_rows > 0) {
    while($row = $result_os->fetch_assoc()) {
        $oss[] = $row;
    }
    usort($oss, function($a, $b) {
        return strcmp($a['nombreSO'], $b['nombreSO']);
    });
}

$distt = [];
if ($result_distribucion->num_rows > 0) {
    while($row = $result_distribucion->fetch_assoc()) {
        $distt[] = $row;
    }
    usort($distt, function($a, $b) {
        return strcmp($a['idSO'], $b['nombreDistribucion']);
    });
}

$version_languages = [];
if ($result_version_distribucion->num_rows > 0) {
    while($row = $result_version_distribucion->fetch_assoc()) {
        $version_languages[] = $row;
    }
}

// Cerrar la conexión
$conn->close();
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
        
        .footer-container {
            background-color: #f7f7f7;
            padding: 20px 0;
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
        <div class="d-flex align-items-start">
            <img src="/images/maquina-virtual.png" alt="Máquina Virtual" class="image-resize me-3">
            <!-- Cuadro de Compra -->
            <div class="purchase-box mb-5" style="background-color: #f8f9fa; padding: 20px; border-radius: 8px;">
                <h2>Configure su Máquina Virtual</h2>
                <form method="GET" action="configProductoVM.php">
                    <div class="mb-3">
                        <label for="cpu" class="form-label">CPU</label>
                        <select class="form-select" id="cpu" name="cpu" required>
                            <option value=""selected>Seleccione CPU</option>
                            <?php
                            foreach ($cpus as $cpu) {
                                echo '<option value="' . $cpu['idCPU'] . '">' . $cpu['modelo'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="ram" class="form-label">RAM</label>
                        <select class="form-select" id="ram" name="ram" required>
                            <option value="" selected>Seleccione RAM</option>
                            <?php
                            foreach ($rams as $ram) {
                                echo '<option value="' . $ram['idRAM'] . '">' . $ram['modelo'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="storage" class="form-label">Almacenamiento</label>
                        <select class="form-select" id="storage" name="storage" required>
                            <option value="" selected>Seleccione Almacenamiento</option>
                            <?php
                            foreach ($storages as $storage) {
                                echo '<option value="' . $storage['idAlmacenamiento'] . '">' . $storage['descCompleta'] . '</option>';                            
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="os" class="form-label">Sistema Operativo</label>
                        <select class="form-select" id="os" name="os" onchange="updateDistribuciones()" required>
                            <option value="" selected>Seleccione Sistema Operativo</option>
                            <?php
                            foreach ($oss as $os) {
                                echo '<option value="' . $os['idSO'] . '">' . $os['nombreSO'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="dist" class="form-label">Distribucion</label>
                        <select class="form-select" id="dist" name="dist" onchange="updateVersions()" required>
                            <option value="" selected>Seleccione Distribucion</option>
                            <?php
                            foreach ($distt as $dist) {
                                echo '<option value="' . $dist['idDistribucion'] . '">' . $dist['nombreDistribucion'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="version_distribucion" class="form-label">Versión de Distribucion</label>
                    <select class="form-select" id="version_distribucion" name="version_distribucion" required>
                        <option value="" selected>Seleccione Versión de Distribucion</option>
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
                <h1>¿Qué es una Máquina Virtual?</h1>
                <p>Una máquina virtual (VM) es un entorno de software que emula un sistema informático completo, permitiendo ejecutar un sistema operativo y aplicaciones como si fueran en un hardware físico.</p>
                
                <h2>Ventajas</h2>
                <ul>
                    <li>Flexibilidad: Permiten ejecutar múltiples sistemas operativos en un solo hardware físico.</li>
                    <li>Aislamiento: Cada VM está aislada de las demás, mejorando la seguridad y estabilidad.</li>
                    <li>Facilidad de Gestión: Las VM pueden ser fácilmente creadas, clonadas, y eliminadas.</li>
                    <li>Costos Reducidos: Ayudan a reducir los costos de hardware al maximizar el uso de los recursos disponibles.</li>
                </ul>
            </div>
        </div>
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
    <script>


        const distribuciones = <?php echo json_encode($distt); ?>;

        function updateDistribuciones() {
            const osSelect = document.getElementById('os');  // Obtener el campo de "Sistema Operativo"
            const distSelect = document.getElementById('dist');  // Obtener el campo de "Distribución"
            const selectedOs = osSelect.value;  // Obtener el id del SO seleccionado

            // Limpiar las opciones actuales del campo "Distribución"
            distSelect.innerHTML = '<option selected>Seleccione Distribución</option>';

            // Filtrar y agregar las nuevas opciones
            distribuciones.forEach(distribucion => {
                if (distribucion.idSO == selectedOs) {
                    const option = document.createElement('option');
                    option.value = distribucion.idDistribucion;
                    option.textContent = distribucion.nombreDistribucion;
                    distSelect.appendChild(option);
                }
            });
        }

        const versions = <?php echo json_encode($version_languages); ?>;

        function updateVersions() {
            const distSelect = document.getElementById('dist');
            const versionSelect = document.getElementById('version_distribucion');
            const selectedDist = distSelect.value;

            // Limpiar las opciones actuales
            versionSelect.innerHTML = '<option selected>Seleccione Versión de Distribucion</option>';

            // Filtrar y agregar las nuevas opciones
            versions.forEach(version => {
                if (version.idDistribucion == selectedDist) {
                    const option = document.createElement('option');
                    option.value = version.numeroVersion;
                    option.textContent = version.numeroVersion;
                    versionSelect.appendChild(option);
                }
            });
}

    </script>
</html>