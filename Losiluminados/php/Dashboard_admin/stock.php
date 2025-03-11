<?php
session_start();

if (!isset($_SESSION['nickname']) || $_SESSION['dashboardAdmin'] != 1) {
    header("Location: /index.php");
    exit();
}
$nombreUsuario = $_SESSION['nombre'];

include '../database/db_connect.php'; // Conexión a la base de datos

// Consultar los componentes en stock para cada categoría
$sql_cpu = "SELECT modelo, precio, c.idCPU FROM cpu c LEFT JOIN vm v ON c.idCPU = v.idCPU WHERE v.idCPU IS NULL";
$result_cpu = $conn->query($sql_cpu);

$sql_ram = "SELECT modelo, capacidad, precio, r.idRAM FROM ram r LEFT JOIN vm v ON r.idRAM = v.idRAM WHERE v.idRAM IS NULL";
$result_ram = $conn->query($sql_ram);

$sql_storage = "SELECT nombreAlmacenamiento, tipo, capacidad, precio, a.idAlmacenamiento FROM almacenamiento a LEFT JOIN vm v ON a.idAlmacenamiento = v.idAlmacenamiento WHERE v.idAlmacenamiento IS NULL";
$result_storage = $conn->query($sql_storage);

$sql_fabricantes_cpu = "SELECT idFabricante, nombreFabricante FROM fabricante";
$result_fabricantes_cpu = $conn->query($sql_fabricantes_cpu);

$sql_fabricantes_ram = "SELECT idFabricante, nombreFabricante FROM fabricante";
$result_fabricantes_ram = $conn->query($sql_fabricantes_ram);

$sql_fabricantes_almacenamiento = "SELECT idFabricante, nombreFabricante FROM fabricante";
$result_fabricantes_almacenamiento = $conn->query($sql_fabricantes_almacenamiento);

// Procesar formulario para agregar componentes (CPU, RAM, Almacenamiento)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_cpu'])) {
        $modelo_cpu = $_POST['modelo_cpu'];
        $precio_cpu = $_POST['precio_cpu'];
        $num_nucleos = $_POST['num_nucleos'];
        $frecuencia_cpu = $_POST['frecuencia_cpu'];
        $fabricante_cpu = $_POST['fabricante_cpu'];
        $sql_add_cpu = "INSERT INTO cpu (modelo, precio, numNucleos, frecuencia, idFabricante, unidadFrecuencia) VALUES ('$modelo_cpu', '$precio_cpu', '$num_nucleos', '$frecuencia_cpu', '$fabricante_cpu', 'GHz')";
        $conn->query($sql_add_cpu);
    }

    if (isset($_POST['add_ram'])) {
        $modelo_ram = $_POST['modelo_ram'];
        $tipo_ram = $_POST['tipo_ram'];
        $velocidad_ram = $_POST['velocidad_ram'];
        $capacidad_ram = $_POST['capacidad_ram'];
        $precio_ram = $_POST['precio_ram'];
        $fabricante_ram = $_POST['fabricante_ram'];
        $sql_add_ram = "INSERT INTO ram (modelo, tipo, velocidad, capacidad, precio, idFabricante, unidadVelocidadRAM, unidadCapacidadRAM) VALUES ('$modelo_ram', '$tipo_ram', '$velocidad_ram', '$capacidad_ram', '$precio_ram', '$fabricante_ram', 'MHz', 'MBps')";
        $conn->query($sql_add_ram);
    }

    if (isset($_POST['add_storage'])) {
        $nombre_storage = $_POST['nombre_storage'];
        $tipo_storage = $_POST['tipo_storage'];
        $capacidad_storage = $_POST['capacidad_storage'];
        $velocidad_storage = $_POST['velocidad_storage'];
        $precio_storage = $_POST['precio_storage'];
        $fabricante_storage = $_POST['fabricante_storage'];
        $sql_add_storage = "INSERT INTO almacenamiento (nombreAlmacenamiento, tipo, capacidad, velocidad, precio, idFabricante, unidadCapacidad, unidadVelocidad) VALUES ('$nombre_storage', '$tipo_storage', '$capacidad_storage', '$velocidad_storage', '$precio_storage', '$fabricante_storage', 'GB', 'MBps')";
        $conn->query($sql_add_storage);
    }
}

// Procesar eliminación de componentes
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete'])) {
    $id_componente = $_GET['delete'];
    $tipo_componente = $_GET['tipo'];

    if ($tipo_componente == 'cpu') {
        // Eliminar CPU
        $sql_delete_cpu = "DELETE FROM cpu WHERE idCPU = ?";
        $stmt = $conn->prepare($sql_delete_cpu);
        $stmt->bind_param("i", $id_componente);
        $stmt->execute();
    } elseif ($tipo_componente == 'ram') {
        // Eliminar RAM
        $sql_delete_ram = "DELETE FROM ram WHERE idRAM = ?";
        $stmt = $conn->prepare($sql_delete_ram);
        $stmt->bind_param("i", $id_componente);
        $stmt->execute();
    } elseif ($tipo_componente == 'almacenamiento') {
        // Eliminar Almacenamiento
        $sql_delete_storage = "DELETE FROM almacenamiento WHERE idAlmacenamiento = ?";
        $stmt = $conn->prepare($sql_delete_storage);
        $stmt->bind_param("i", $id_componente);
        $stmt->execute();
    }

    // Redirigir a la misma página después de la eliminación
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Stock de Componentes</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
        }
        .sidebar {
            height: calc(100vh - 56px); /* Altura ajustada menos la navbar */
            position: fixed;
            width: 250px;
            background-color: #5793CF;
            color: white;
            padding-top: 20px;
            top: 56px; /* Ajuste para la altura de la navbar */
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 15px 20px; /* Espaciado interno */
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: rgb(5, 71, 109);
        }
        .main-content {
            margin-left: 260px; /* Espacio para el sidebar */
            padding: 20px;
            margin-top: 56px; /* Espacio para la navbar */
        }
        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px; /* Espacio entre las tablas */
        }
        .table-striped{
            margin-top: 30px;
        }
        .table-container h1 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .btn-add-component {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="/index.php">
                    <img src="/images/logo.svg" alt="Logo" width="100" height="40">
                </a>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/php/Cuenta/logout.php">Cerrar Sesión</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Sidebar -->
    <div class="sidebar">
        <h3 class="text-center">Dashboard</h3>
        <a href="/php/Dashboard_admin/inicio.php">Inicio</a>
        <a href="/php/Dashboard_admin/grupo.php">Grupos</a>
        <a href="/php/Dashboard_admin/usuario.php">Usuarios</a>
        <a href="/php/Dashboard_admin/privilegio.php">Privilegios</a>
        <a href="/php/Dashboard_admin/monitorizacion.php">Monitorización</a>
        <a href="/php/Dashboard_admin/stock.php">Stock</a>
        <a href="/php/Dashboard_admin/productos.php">Productos</a>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <h1>Stock de Componentes</h1>
        
        <!-- Formulario de CPU -->
        <div class="table-container">
            <h1>CPU</h1>
            <!-- Formulario para agregar CPU -->
            <form method="POST">
                <div class="mb-3">
                    <label for="modelo_cpu" class="form-label">Modelo</label>
                    <input type="text" class="form-control" id="modelo_cpu" name="modelo_cpu" required>
                </div>
                <div class="mb-3">
                    <label for="num_nucleos" class="form-label">Número de núcleos</label>
                    <input type="number" class="form-control" id="num_nucleos" name="num_nucleos" required>
                </div>
                <div class="mb-3">
                    <label for="frecuencia_cpu" class="form-label">Frecuencia (GHz)</label>
                    <input type="number" class="form-control" id="frecuencia_cpu" name="frecuencia_cpu" required>
                </div>
                <div class="mb-3">
                    <label for="precio_cpu" class="form-label">Precio</label>
                    <input type="number" class="form-control" id="precio_cpu" name="precio_cpu" required>
                </div>
                <div class="mb-3">
                <label for="fabricante_cpu" class="form-label">Fabricante</label>
                    <select class="form-select" id="fabricante_cpu" name="fabricante_cpu" required>
                        <option value="" disabled selected>Selecciona un fabricante</option>
                        <?php while ($row = $result_fabricantes_cpu->fetch_assoc()) { ?>
                            <option value="<?php echo $row['idFabricante']; ?>"><?php echo $row['nombreFabricante']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <button type="submit" name="add_cpu" class="btn btn-primary">Agregar CPU</button>
            </form>
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Modelo</th>
                        <th scope="col">Precio</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_cpu->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['modelo']; ?></td>
                        <td><?php echo $row['precio']; ?></td>
                        <td>
                            <a href="?delete=<?php echo $row['idCPU']; ?>&tipo=cpu" 
                            class="btn btn-sm btn-danger" 
                            onclick="return confirm('¿Estás seguro de que quieres eliminar esta CPU?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Formulario de RAM -->
        <div class="table-container">
            <h1>RAM</h1>
            <!-- Formulario para agregar RAM -->
            <form method="POST">
                <div class="mb-3">
                    <label for="modelo_ram" class="form-label">Modelo</label>
                    <input type="text" class="form-control" id="modelo_ram" name="modelo_ram" required>
                </div>
                <div class="mb-3">
                    <label for="tipo_ram" class="form-label">Tipo de RAM</label>
                    <input type="text" class="form-control" id="tipo_ram" name="tipo_ram" required>
                </div>
                <div class="mb-3">
                    <label for="velocidad_ram" class="form-label">Velocidad de la RAM (MHz)</label>
                    <input type="number" class="form-control" id="velocidad_ram" name="velocidad_ram" required>
                </div>
                <div class="mb-3">
                    <label for="capacidad_ram" class="form-label">Capacidad</label>
                    <input type="number" class="form-control" id="capacidad_ram" name="capacidad_ram" required>
                </div>
                <div class="mb-3">
                    <label for="precio_ram" class="form-label">Precio</label>
                    <input type="number" class="form-control" id="precio_ram" name="precio_ram" required>
                </div>
                <div class="mb-3">
                <label for="fabricante_ram" class="form-label">Fabricante</label>
                    <select class="form-select" id="fabricante_ram" name="fabricante_ram" required>
                        <option value="" disabled selected>Selecciona un fabricante</option>
                        <?php while ($row = $result_fabricantes_ram->fetch_assoc()) { ?>
                            <option value="<?php echo $row['idFabricante']; ?>"><?php echo $row['nombreFabricante']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <button type="submit" name="add_ram" class="btn btn-primary">Agregar RAM</button>
            </form>
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Modelo</th>
                        <th scope="col">Capacidad</th>
                        <th scope="col">Precio</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_ram->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['modelo']; ?></td>
                        <td><?php echo $row['capacidad']; ?></td>
                        <td><?php echo $row['precio']; ?></td>
                        <td><a href="?delete=<?php echo $row['idRAM']; ?>&tipo=ram" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que quieres eliminar esta RAM?')">Eliminar</a></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Formulario de Almacenamiento -->
        <div class="table-container">
            <h1>Almacenamiento</h1>
            <!-- Formulario para agregar Almacenamiento -->
            <form method="POST">
                <div class="mb-3">
                    <label for="nombre_storage" class="form-label">Modelo</label>
                    <input type="text" class="form-control" id="nombre_storage" name="nombre_storage" required>
                </div>
                <div class="mb-3">
                    <label for="tipo_storage" class="form-label">Tipo de Almacenamiento</label>
                    <input type="text" class="form-control" id="tipo_storage" name="tipo_storage" required>
                </div>
                <div class="mb-3">
                    <label for="capacidad_storage" class="form-label">Capacidad (GB)</label>
                    <input type="number" class="form-control" id="capacidad_storage" name="capacidad_storage" required>
                </div>
                <div class="mb-3">
                    <label for="velocidad_storage" class="form-label">Velocidad (MBps)</label>
                    <input type="number" class="form-control" id="velocidad_storage" name="velocidad_storage" required>
                </div>
                <div class="mb-3">
                    <label for="precio_storage" class="form-label">Precio</label>
                    <input type="number" class="form-control" id="precio_storage" name="precio_storage" required>
                </div>
                <div class="mb-3">
                <label for="fabricante_storage" class="form-label">Fabricante</label>
                    <select class="form-select" id="fabricante_storage" name="fabricante_storage" required>
                        <option value="" disabled selected>Selecciona un fabricante</option>
                        <?php while ($row = $result_fabricantes_almacenamiento->fetch_assoc()) { ?>
                            <option value="<?php echo $row['idFabricante']; ?>"><?php echo $row['nombreFabricante']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <button type="submit" name="add_storage" class="btn btn-primary">Agregar Almacenamiento</button>
            </form>
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Nombre</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Capacidad</th>
                        <th scope="col">Precio</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_storage->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['nombreAlmacenamiento']; ?></td>
                        <td><?php echo $row['tipo']; ?></td>
                        <td><?php echo $row['capacidad']; ?></td>
                        <td><?php echo $row['precio']; ?></td>
                        <td><a href="?delete=<?php echo $row['idAlmacenamiento']; ?>&tipo=almacenamiento" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que quieres eliminar este almacenamiento?')">Eliminar</a></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6SZXku3GK1zFk9F9KqpgJHFlvQeCIGvljKvC77x4P2Bl" crossorigin="anonymous"></script>
</body>
</html>