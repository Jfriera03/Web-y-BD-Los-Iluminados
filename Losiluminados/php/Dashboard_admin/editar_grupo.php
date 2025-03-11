<?php
session_start();

if (!isset($_SESSION['nickname']) || $_SESSION['dashboardAdmin'] != 1) {
    header("Location: /index.php");
    exit();
}

include '../database/db_connect.php'; // Conexión a la base de datos

$message = ""; // Variable para mensajes al usuario
$toastClass = ""; // Clase para estilos de notificación

if (!isset($_GET['idGrupo'])) {
    header("Location: /php/Dashboard_admin/grupo.php?error=IDGrupoFaltante");
    exit();
}

$idGrupo = $_GET['idGrupo']; // Captura el ID del grupo desde la URL

// Obtener los datos del grupo
$sql = "SELECT idGrupo, nombreGrupo FROM grupo WHERE idGrupo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idGrupo); // Usamos `i` porque es un entero
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $groupData = $result->fetch_assoc();
    $nombreGrupo = $groupData['nombreGrupo'];
} else {
    header("Location: /php/Dashboard_admin/grupo.php?error=GrupoNoEncontrado");
    exit();
}

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nuevo_nombre = $_POST['newname'] ?? '';

    // Validar datos
    if (empty($nuevo_nombre)) {
        $message = "Por favor, completa todos los campos.";
        $toastClass = "alert-danger";
    } else {
        // Actualizar datos
        $sql_update = "UPDATE grupo SET nombreGrupo = ? WHERE idGrupo = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $nuevo_nombre, $idGrupo);

        if ($stmt_update->execute()) {
            if ($stmt_update->affected_rows > 0) {
                $_SESSION['profile_message'] = "Cambios guardados con éxito.";
                header("Location: /php/Dashboard_admin/grupo.php?success=GrupoActualizado");
                exit();
            } else {
                $message = "No se realizaron cambios en los datos.";
                $toastClass = "alert-warning";
            }
        } else {
            $message = "Error al guardar los cambios: " . $stmt_update->error;
            $toastClass = "alert-danger";
        }
        $stmt_update->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Editar Perfil de Usuario</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
        }

        /* Sidebar */
        .sidebar {
            height: calc(100vh - 56px); /* Ajusta la altura sin contar la navbar */
            position: fixed;
            width: 250px;
            background-color: #5793CF;
            color: white;
            padding-top: 20px;
            top: 56px; /* Ajuste para la altura de la navbar */
        }

        .sidebar h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 15px 20px; /* Espaciado interno */
            transition: background-color 0.3s ease;
        }

        .sidebar a:hover {
            background-color: #4d2674;
        }

        /* Main content */
        .main-content {
            margin-left: 260px; /* Espacio para el sidebar */
            padding: 20px;
            margin-top: 56px; /* Espacio para la navbar */
        }

        .profile-edit-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 800px;
            margin: auto;
        }

        .btn-save-changes {
            background-color: #8fc19a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-save-changes:hover {
            background-color: #6fa77d;
        }

        footer {
            background-color: #f1f1f1;
            padding: 20px 0;
            margin-top: 20px;
            text-align: center;
        }

        #footer-bottom {
            background-color: #e1e1e1;
            padding: 10px 0;
        }

    </style>
</head>
<body>
    <!-- Navbar -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-grey px-3">
            <div class="container-fluid">
                <a class="navbar-brand" href="/index.php">
                    <img src="/images/logo.svg" alt="Logo" width="100" height="40">
                </a>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/php/Cuenta/logout.php"><i></i>Cerrar Sesión</a>
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
    <div class="main-content">
    <div class="profile-edit-container">
        <h1>Editar Grupo</h1>
        <form method="POST">
            <div class="form-group">
                <label for="newname">Nuevo Nombre</label>
                <input type="text" class="form-control" id="newname" name="newname" value="<?php echo htmlspecialchars($nombreGrupo, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Guardar Cambios</button>
        </form>

        <?php if (!empty($message)): ?>
        <div class="alert <?php echo $toastClass; ?> mt-3">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php endif; ?>
    </div>

    </div>

    <!-- Footer -->
    <footer>
        <p>© 2024 LosIluminados. Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6SZXku3GK1zFk9F9KqpgJHFlvQeCIGvljKvC77x4P2Bl" crossorigin="anonymous"></script>
</body>
</html>