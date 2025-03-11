<?php
session_start();

if (!isset($_SESSION['nickname']) || $_SESSION['dashboardAdmin'] != 1) {
    header("Location: /index.php");
    exit();
}
$nombreUsuario = $_SESSION['nombre'];

include '../database/db_connect.php'; // Conexión a la base de datos

// Consultar los últimos logs para VM, BD, ED y AC
$sql_vm = "
    SELECT p.nombreProducto, l.almacenamientoUtilizado, l.porcentajeUsoCPU, l.porcentajeUsoRAM, l.timestamp, a.unidadCapacidad AS result_vm_unidad
    FROM LOG_VM l 
    INNER JOIN PRODUCTO p ON l.idProducto = p.idProducto
    JOIN VM vm ON vm.idVM = p.idVM
    JOIN ALMACENAMIENTO a ON a.idAlmacenamiento = vm.idAlmacenamiento
    WHERE l.timestamp = (
        SELECT MAX(timestamp) 
        FROM LOG_VM l2 
        WHERE l2.idProducto = l.idProducto
    )";

    

$sql_bd = "
    SELECT p.nombreProducto, l.almacenamientoUtilizado, l.numConexiones, l.timestamp 
    FROM LOG_BD l 
    INNER JOIN PRODUCTO p ON l.idProducto = p.idProducto
    WHERE l.timestamp = (
        SELECT MAX(timestamp) 
        FROM LOG_BD l2 
        WHERE l2.idProducto = l.idProducto
    )";



$sql_ed = "
    SELECT p.nombreProducto, l.almacenamientoUtilizado, l.timestamp 
    FROM LOG_ED l 
    INNER JOIN PRODUCTO p ON l.idProducto = p.idProducto
    WHERE l.timestamp = (
        SELECT MAX(timestamp) 
        FROM LOG_ED l2 
        WHERE l2.idProducto = l.idProducto
    )";



$sql_ac = "
    SELECT p.nombreProducto, l.almacenamientoUtilizado, l.timestamp 
    FROM LOG_ALMACENAMIENTO_CLOUD l 
    INNER JOIN PRODUCTO p ON l.idProducto = p.idProducto
    WHERE l.timestamp = (
        SELECT MAX(timestamp) 
        FROM LOG_ALMACENAMIENTO_CLOUD l2 
        WHERE l2.idProducto = l.idProducto
    )";




$result_vm = $conn->query($sql_vm);
$result_bd = $conn->query($sql_bd);
$result_ed = $conn->query($sql_ed);
$result_ac = $conn->query($sql_ac);



?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Monitorización de Productos</title>
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
            max-height: 400px; /* Ajuste para la altura de la tabla */
            overflow-y: auto; /* Permite desplazamiento solo vertical */
            margin-bottom: 40px;
        }
        .table-container h1 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .btn-add-product {
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
    <h1>Monitorización</h1>
    <!-- Botón para actualizar los logs -->
    <div class="text-center mb-3">
        <button id="actualizarLogs" class="btn btn-primary">Ver estado actual</button>
    </div>
        
        <!-- Formulario de VM -->
        <div class="table-container">
            <h1>Máquina Virtual</h1>
            

            
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Nombre del Producto</th>
                        <th scope="col">Almacenamiento Utilizado GB</th>
                        <th scope="col">Porcentaje de uso de CPU</th>
                        <th scope="col">Porcentaje de uso de RAM</th>
                        <th scope="col">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_vm->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nombreProducto']) ?></td>
                            <td><?= htmlspecialchars($row['almacenamientoUtilizado']) . ' ' . htmlspecialchars($row['result_vm_unidad']) ?></td>
                            <td><?= htmlspecialchars($row['porcentajeUsoCPU']) . ' %' ?></td>
                            <td><?= htmlspecialchars($row['porcentajeUsoRAM']) . ' %' ?></td>
                            <td><?= htmlspecialchars($row['timestamp']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Formulario de BD -->
        <div class="table-container">
            <h1>Base de datos</h1>
            
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Nombre del Producto</th>
                        <th scope="col">Almacenamiento Utilizado</th>
                        <th scope="col">Número de conexiones</th>
                        <th scope="col">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_bd->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nombreProducto']) ?></td>
                            <td><?= htmlspecialchars($row['almacenamientoUtilizado']) . ' GB' ?></td>
                            <td><?= htmlspecialchars($row['numConexiones']) ?></td>
                            <td><?= htmlspecialchars($row['timestamp']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Formulario de ED -->
        <div class="table-container">
            <h1>Entorno de Desarrollo</h1>
            
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Nombre del Producto</th>
                        <th scope="col">   </th><!-- no hay nada qeu poner -->
                        <th scope="col">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_ed->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nombreProducto']) ?></td>
                            <td><?= htmlspecialchars($row['almacenamientoUtilizado']) ?></td>
                            <td><?= htmlspecialchars($row['timestamp']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Formulario de AC -->
        <div class="table-container">
            <h1>Almacenamiento Cloud</h1>
            
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Nombre del Producto</th>
                        <th scope="col">Almacenamiento Utilizado</th>
                        <th scope="col">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_ac->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nombreProducto']) ?></td>
                            <td><?= htmlspecialchars($row['almacenamientoUtilizado']) . ' GB' ?></td>
                            <td><?= htmlspecialchars($row['timestamp']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        document.getElementById('actualizarLogs').addEventListener('click', function() {
            // Realizar una solicitud AJAX para ejecutar los procedimientos almacenados
            fetch('actualizar_logs.php', {
                method: 'GET',
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Logs actualizados con éxito');
                    location.reload(); // Recarga la página para mostrar los nuevos datos
                } else {
                    alert('Error al actualizar los logs: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Hubo un problema con la actualización: ' + error.message);
            });
        });
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6SZXku3GK1zFk9F9KqpgJHFlvQeCIGvljKvC77x4P2Bl" crossorigin="anonymous"></script>
</body>
</html>
