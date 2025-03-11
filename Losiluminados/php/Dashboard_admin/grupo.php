<?php
    session_start();

    if (!isset($_SESSION['nickname']) || $_SESSION['dashboardAdmin'] != 1) {
        header("Location: /index.php");
        exit();
    }

    include '../database/db_connect.php'; // Conexión a la base de datos

    $message = ""; // Variable para mensajes al usuario
    $toastClass = ""; // Clase para estilos de notificación

    // Consulta para obtener los grupos
    $sql = "SELECT idGrupo, nombreGrupo FROM grupo";
    $result = $conn->query($sql);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['add_grupo'])) {
            // Agregar un nuevo grupo
            $nombre_grupo = $_POST['nombre_grupo'];
            $sql_add_grupo = "INSERT INTO grupo (nombreGrupo) VALUES (?)";
            $stmt = $conn->prepare($sql_add_grupo);
            $stmt->bind_param("s", $nombre_grupo);
            $stmt->execute();
            header("Location: /php/Dashboard_admin/grupo.php");
            exit();
        } elseif (isset($_POST['delete_idGrupo'])) {
            // Eliminar un grupo existente
            $idGrupo = $_POST['delete_idGrupo'];

            try {
                // Iniciar transacción para garantizar integridad
                $conn->begin_transaction();

                // Eliminar las asociaciones del grupo con privilegios y usuarios
                $conn->query("DELETE FROM PerteneceGrupo WHERE idGrupo = $idGrupo");
                $conn->query("DELETE FROM PrivilegioDeGrupo WHERE idGrupo = $idGrupo");

                // Eliminar el grupo
                $stmt_delete = $conn->prepare("DELETE FROM grupo WHERE idGrupo = ?");
                $stmt_delete->bind_param("i", $idGrupo);
                $stmt_delete->execute();

                // Confirmar transacción
                $conn->commit();

                $message = "Grupo eliminado con éxito.";
                $toastClass = "alert-success";

                // Redirigir a la misma página
                header("Location: /php/Dashboard_admin/grupo.php");
                exit();
            } catch (Exception $e) {
                // Revertir cambios en caso de error
                $conn->rollback();
                $message = "Error al eliminar el grupo: " . $e->getMessage();
                $toastClass = "alert-danger";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Grupos de Privilegios</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding-top: 20px; /* Espacio adicional para no tapar el logo */
            min-height: 100vh; /* Hace que el contenido ocupe toda la pantalla */
            overflow: hidden; /* Esto elimina la barra de desplazamiento */
        }
        .sidebar {
            height: 100vh; /* Ajustar para ocupar toda la altura de la ventana */
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
            min-height: 100vh; /* Ajuste para ocupar el espacio disponible */
            overflow: hidden; /* Evita desplazamientos dentro del contenido principal */
        }
        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .table-container h1 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .btn-add-group {
            margin-bottom: 20px;
        }
        /* Estilo para el logo fijo */
        .navbar-brand img {
            position: fixed;
            top: 10px;  /* Espacio desde la parte superior */
            left: 20px; /* Espacio desde el borde izquierdo */
            z-index: 1000; /* Asegura que esté por encima de otros elementos */
        }
        .navbar-nav .nav-item:last-child {
            position: fixed;
            top: 20px;     /* Ajusta la distancia desde la parte superior */
            right: 20px;   /* Ajusta la distancia desde el borde derecho */
            z-index: 9999; /* Asegura que el enlace esté por encima de otros elementos */
        }
        .btn-add-group {
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
        <h1>Gestión de Grupos</h1>
        <div class="table-container">
            <!-- Formulario para agregar grupo -->
            <form method="POST">
                <div class="mb-3">
                    <label for="nombre_grupo" class="form-label">Nombre de Grupo</label>
                    <input type="text" class="form-control" id="nombre_grupo" name="nombre_grupo" required>
                </div>
                <button type="submit" name="add_grupo" class="btn btn-primary mb-3">Agregar grupo</button>
            </form>

            <h1>Grupos de Privilegios</h1>
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Nombre del Grupo</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['nombreGrupo'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>
                                    <a href=\"/php/Dashboard_admin/editar_grupo.php?idGrupo=" . htmlspecialchars($row['idGrupo'], ENT_QUOTES, 'UTF-8') . "\" class=\"btn btn-sm btn-warning\">Editar</a>
                                    <button class='btn btn-sm btn-danger' onclick=\"confirmDelete('" . htmlspecialchars($row['idGrupo'], ENT_QUOTES, 'UTF-8') . "')\">Eliminar</button>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2' class='text-center'>No hay grupos disponibles.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <!-- Formulario oculto para eliminación -->
            <form id="deleteForm" method="POST" style="display: none;">
                <input type="hidden" name="delete_idGrupo" id="delete_idGrupo">
            </form>
        </div>
    </main>

    <script>
        function confirmDelete(idGrupo) {
            if (confirm("¿Estás seguro de que deseas eliminar este grupo?")) {
                const deleteForm = document.getElementById('deleteForm');
                document.getElementById('delete_idGrupo').value = idGrupo;
                deleteForm.submit();
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6SZXku3GK1zFk9F9KqpgJHFlvQeCIGvljKvC77x4P2Bl" crossorigin="anonymous"></script>
</body>
</html>

<?php
// Cerrar la conexión
$conn->close();
?>