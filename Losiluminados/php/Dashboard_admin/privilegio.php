<?php
session_start();

if (!isset($_SESSION['nickname']) || $_SESSION['dashboardAdmin'] != 1) {
    header("Location: /index.php");
    exit();
}
$nombreUsuario = $_SESSION['nombre'];

include '../database/db_connect.php'; // Conexión a la base de datos

// Procesar el formulario para agregar un privilegio
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombrePrivilegio = $_POST['nombrePrivilegio'];
    $descripcion = $_POST['descripcion'];
    $rolesAsignados = isset($_POST['roles']) ? $_POST['roles'] : [];

    if ($nombrePrivilegio && $descripcion) {
        // Insertar el nuevo privilegio en la base de datos
        $sql = "INSERT INTO privilegio (nombrePrivilegio, descripcion) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $nombrePrivilegio, $descripcion);

        if ($stmt->execute()) {
            // Obtener el ID del nuevo privilegio
            $idPrivilegio = $stmt->insert_id;

            // Asignar roles al nuevo privilegio (ahora se permite más de uno)
            foreach ($rolesAsignados as $idGrupo) {
                $sqlRol = "INSERT INTO privilegiodegrupo (idPrivilegio, idGrupo) VALUES (?, ?)";
                $stmtRol = $conn->prepare($sqlRol);
                $stmtRol->bind_param("ii", $idPrivilegio, $idGrupo);
                $stmtRol->execute();
            }
            $msg = "Privilegio agregado exitosamente.";
        } else {
            $msg = "Error al agregar el privilegio.";
        }

        $stmt->close();
    } else {
        $msg = "Por favor complete todos los campos.";
    }
}

// Consulta para obtener los privilegios y los roles asociados
$sql = "SELECT p.nombrePrivilegio, p.descripcion, GROUP_CONCAT(g.nombreGrupo) AS roles_asignados, p.idPrivilegio
        FROM privilegio p
        LEFT JOIN privilegiodegrupo pg ON p.idPrivilegio = pg.idPrivilegio
        LEFT JOIN grupo g ON pg.idGrupo = g.idGrupo
        GROUP BY p.idPrivilegio";
$result = $conn->query($sql);

// Consulta para obtener todos los grupos disponibles
$sqlGrupos = "SELECT idGrupo, nombreGrupo FROM grupo";
$gruposResult = $conn->query($sqlGrupos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Gestión de Privilegios</title>
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
            height: 100vh; /* Ajustar para ocupar toda la altura de la ventana */
            position: fixed;
            width: 250px;
            background-color: #5793CF;
            padding-top: 20px;
            color: white;
            top: 56px; /* Altura de la navbar */
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 15px 20px;
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: rgb(5, 71, 109);
        }
        .main-content {
            margin-left: 260px; /* Espacio para la sidebar */
            padding: 20px;
            margin-top: 56px; /* Espacio para la navbar */
        }
        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-height: 900px; /* Ajusta la altura máxima para que la tabla se desplace */
            overflow-y: auto; /* Permite desplazamiento vertical */
        }
        .table-container h1 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .btn-add-privilege {
            margin-bottom: 20px;
        }
        .form-container {
            margin-bottom: 20px;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
        <h3>Dashboard</h3>
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
        <div class="table-container">
            <h1>Gestión de Privilegios</h1>

            <!-- Formulario para agregar privilegio -->
            <div class="form-container">
                <h3>Agregar Nuevo Privilegio</h3>
                <?php if (isset($msg)): ?>
                    <div class="alert alert-info">
                        <?php echo $msg; ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-info">
                        <?php echo htmlspecialchars($_GET['msg']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="nombrePrivilegio" class="form-label">Nombre del Privilegio</label>
                        <input type="text" class="form-control" id="nombrePrivilegio" name="nombrePrivilegio" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="roles" class="form-label">Roles Asignados</label>
                        <select class="form-control" id="roles" name="roles[]" multiple required>
                            <?php while ($grupo = $gruposResult->fetch_assoc()): ?>
                                <option value="<?php echo $grupo['idGrupo']; ?>"><?php echo $grupo['nombreGrupo']; ?></option>
                            <?php endwhile; ?>
                        </select>
                        <small class="form-text text-muted">Mantén presionada la tecla Ctrl para seleccionar múltiples roles.</small>
                    </div>
                    <button type="submit" class="btn btn-success">Crear Privilegio</button>
                </form>
            </div>

            <table class="table table-striped">
    <thead class="table-dark">
        <tr>
            <th scope="col">Nombre del Privilegio</th>
            <th scope="col">Descripción</th>
            <th scope="col">Roles Asignados</th>
            <th scope="col">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result->num_rows > 0) {
            // Mostrar los privilegios y roles asignados
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['nombrePrivilegio'] . "</td>";
                echo "<td>" . $row['descripcion'] . "</td>";
                echo "<td>" . $row['roles_asignados'] . "</td>";
                echo "<td>
                        <a href='editar_privilegio.php?idPrivilegio=" . $row['idPrivilegio'] . "' class='btn btn-sm btn-warning'>Editar</a>
                        <a href='eliminar_privilegio.php?idPrivilegio=" . $row['idPrivilegio'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"¿Estás seguro de eliminar este privilegio?\")'>Eliminar</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4' class='text-center'>No hay privilegios disponibles.</td></tr>";
        }
        ?>
    </tbody>
</table>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6SZXku3GK1zFk9F9KqpgJHFlvQeCIGvljKvC77x4P2Bl" crossorigin="anonymous"></script>
</body>
</html>

<?php
// Cerrar la conexión
$conn->close();
?>