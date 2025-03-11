<?php
    session_start();

    if (!isset($_SESSION['nickname']) || $_SESSION['dashboardUser'] != 1) {
        header("Location: /index.php");
        exit();
    }

    include '../database/db_connect.php'; // Conexión a la base de datos

    $message = ""; // Variable para mensajes al usuario
    $toastClass = ""; // Clase para estilos de notificación

    $nickname = $_SESSION['nickname'];
    $nombreUsuario = $_SESSION['nombre'];
    $emailUsuario = $_SESSION['email'];

    // Procesar el formulario
    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        $nuevo_nombre = $_POST['newname'];
        $nuevo_email = $_POST['newemail'];

        // Comprobar si el nombre y el email son válidos
        if (empty($nuevo_nombre) || empty($nuevo_email)) {
            $message = "Por favor, completa todos los campos.";
            $toastClass = "alert-danger";
        } else {
            // Comprobar si el email ya existe en la base de datos
            $sql = "SELECT * FROM usuario WHERE emailUsuario = '$nuevo_email'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $message = "El correo electrónico ya está en uso.";
                $toastClass = "alert-danger";
            } else {
                // Actualizar el email y el nombre del usuario
                $sql = "UPDATE usuario SET nombreUsuario = '$nuevo_nombre', emailUsuario = '$nuevo_email' WHERE nickname = '$nickname'";
                if ($conn->query($sql) === TRUE) {
                    // Guardar mensaje de éxito en la sesión
                    $_SESSION['profile_message'] = "Cambios guardados con éxito!";
                    $_SESSION['toast_class'] = "alert-success";
                    $_SESSION['nombre'] = $nuevo_nombre;
                    $_SESSION['email'] = $nuevo_email;

                    // Redirigir al inicio
                    header("Location: /php/Dashboard_usuario/inicio.php");
                    exit();
                } else {
                    $message = "Error al guardar los cambios: " . $conn->error;
                    $toastClass = "alert-danger";
                }
            }
        }

        $conn->close();
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

        .profile-edit-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 800px;
            margin: auto;
        }

        .profile-edit-container h1 {
            font-size: 1.75rem;
            margin-bottom: 20px;
        }

        .profile-edit-container img {
            border-radius: 50%;
            width: 120px;
            height: 120px;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .profile-edit-container .form-group {
            margin-bottom: 15px;
        }

        .profile-edit-container .form-group label {
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
        <h3>Dashboard</h3>
        <a href="/php/Dashboard_usuario/inicio.php">Inicio</a>
        <a href="/php/Dashboard_usuario/perfil.php">Mi Perfil</a>
        <a href="/php/Dashboard_usuario/productos.php">Mis Productos</a>
        <a href="/php/Dashboard_usuario/ajustes.php">Ajustes</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="profile-edit-container">
            <h1>Editar Perfil</h1>

            <!-- Formulario de edición de perfil -->
            <form id="editProfileForm" method="POST">
                <div class="form-group">
                    <label for="newname">Nombre Completo</label>
                    <input type="text" class="form-control" id="newname" name="newname" value="<?php echo $nombreUsuario; ?>">
                </div>
                <div class="form-group">
                    <label for="newemail">Correo Electrónico</label>
                    <input type="email" class="form-control" id="newemail" name="newemail" value="<?php echo $emailUsuario; ?>">
                </div>
                <!-- Botón para guardar los cambios -->
                <button type="submit" class="btn-save-changes mt-3">Guardar Cambios</button>
            </form>

            <?php if (!empty($message)): ?>
            <div class="message <?php echo $toastClass; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>© 2024 LosIluminados. Todos los derechos reservados.</p>
    </footer>

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