<?php
    session_start(); // Iniciar una nueva sesión o reanudar la existente
    
    if (!isset($_SESSION['nickname']) || $_SESSION['dashboardUser'] != 1) {
        header("Location: /index.php");
        exit();
    }
    
    $email = $_SESSION['email']; // Usamos el email de la sesión

    // Conexión a la base de datos
    include '../database/db_connect.php';

    // Inicialización de variables
    $message = "";
    $toastClass = "";

    // Verifica si el formulario fue enviado usando el método POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        // Recibimos las contraseñas proporcionadas
        $currentPassword = trim($_POST['currentPassword']);
        $newPassword = trim($_POST['newPassword']);
        $confirmPassword = trim($_POST['confirmPassword']);
        
        // Validamos si las contraseñas no están vacías
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $message = "Todos los campos son obligatorios.";
            $toastClass = "bg-danger";
        } else if (strlen($newPassword) < 8) {
            $message = "La contraseña debe tener al menos 8 caracteres.";
            $toastClass = "bg-danger";
        } else {
            // Verificamos si las contraseñas coinciden
            if ($newPassword !== $confirmPassword) {
                $message = "Las contraseñas no coinciden.";
                $toastClass = "bg-danger";
            } else {
                // Consultamos la contraseña actual de la base de datos
                $stmt = $conn->prepare("SELECT hashContraseña FROM usuario WHERE emailUsuario = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($storedPasswordHash);
                $stmt->fetch();
                $stmt->close();

                // Verificamos si la contraseña actual proporcionada es correcta
                if (hash('sha256', $currentPassword) === $storedPasswordHash) {
                    // Si la contraseña actual es correcta, actualizamos la nueva contraseña
                    $newPasswordHash = hash('sha256', $newPassword);

                    // Actualizamos la base de datos con la nueva contraseña
                    $stmt = $conn->prepare("UPDATE usuario SET hashContraseña = ? WHERE emailUsuario = ?");
                    $stmt->bind_param("ss", $newPasswordHash, $email);
                    if ($stmt->execute()) {
                        $message = "Contraseña actualizada correctamente.";
                        $toastClass = "bg-success";
                    } else {
                        $message = "Hubo un error al actualizar la contraseña.";
                        $toastClass = "bg-danger";
                    }
                    $stmt->close();
                } else {
                    // La contraseña actual no coincide
                    $message = "La contraseña actual es incorrecta.";
                    $toastClass = "bg-danger";
                }
            }
        }
        
        // Cerramos la conexión a la base de datos
        $conn->close();
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Ajustes de Cuenta</title>
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
            background-color: #9357cf;
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

        .settings-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 800px;
            margin: auto;
        }

        .settings-container h1 {
            font-size: 1.75rem;
            margin-bottom: 20px;
        }

        .settings-container .form-group {
            margin-bottom: 15px;
        }

        .settings-container .form-group label {
            font-weight: bold;
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
            padding: 35px 0;
            margin-top: 20px;
            text-align: center;
        }

        #footer-bottom {
            background-color: #e1e1e1;
            padding: 35px 0;
            text-align: center;
            position:fixed;
            bottom:0px;
            height:30px;
            width:100%;
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
        <h3>Dashboard</h3>
        <a href="/php/Dashboard_usuario/inicio.php">Inicio</a>
        <a href="/php/Dashboard_usuario/perfil.php">Mi Perfil</a>
        <a href="/php/Dashboard_usuario/productos.php">Mis Productos</a>
        <a href="/php/Dashboard_usuario/ajustes.php">Ajustes</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="settings-container">
            <h1>Ajustes de Cuenta</h1>

            <!-- Mostrar el mensaje -->
            <?php if (!empty($message)): ?>
                <div class="alert <?php echo $toastClass; ?>" role="alert">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Formulario de Cambiar Contraseña -->
            <form method="POST">
                <div class="form-group">
                    <label for="currentPassword">Contraseña Actual</label>
                    <input type="password" name="currentPassword" class="form-control" id="currentPassword" required>
                </div>
                <div class="form-group">
                    <label for="newPassword">Nueva Contraseña</label>
                    <input type="password" name="newPassword" class="form-control" id="newPassword" required>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirmar Nueva Contraseña</label>
                    <input type="password" name="confirmPassword" class="form-control" id="confirmPassword" required>
                </div>
                <button type="submit" class="btn-save-changes mt-3">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <div id="footer-bottom">
        <div class="container-lg">
            <div class="row">
                <div class="col-md-6 copyright">
                    <p>© 2024 LosIluminados. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6SZXku3GK1zFk9F9KqpgJHFlvQeCIGvljKvC77x4P2Bl" crossorigin="anonymous"></script>
</body>
</html>