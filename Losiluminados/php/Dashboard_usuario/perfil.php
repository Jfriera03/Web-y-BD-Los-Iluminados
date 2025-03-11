<?php
    session_start();

    if (!isset($_SESSION['nickname']) || $_SESSION['dashboardUser'] != 1) {
        header("Location: /index.php");
        exit();
    }
    $nombreUsuario = $_SESSION['nombre'];
    $nickname = $_SESSION['nickname'];
    $email = $_SESSION['email'];
    $empresa = $_SESSION['empresa'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Perfil de Usuario</title>
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

        .profile-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 800px;
            margin: auto;
        }

        .profile-container h1 {
            font-size: 1.75rem;
            margin-bottom: 20px;
        }

        .profile-container .profile-info {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .profile-container .profile-info img {
            border-radius: 50%;
            width: 120px;
            height: 120px;
            object-fit: cover;
        }

        .profile-container .profile-info div {
            flex: 1;
        }

        .profile-container .profile-info div h3 {
            margin: 0;
        }

        .profile-container .profile-info div p {
            color: #6c757d;
        }

        .btn-edit-profile {
            background-color: #8fc19a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-edit-profile:hover {
            background-color: #6fa77d;
        }

        footer {
            background-color: #f1f1f1;
            padding: 35px 0;
            margin-top: 20px;
            text-align: center;
            position:fixed;
            bottom:0px;
            height:30px;
            width:100%;
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
        <div class="profile-container">
            <h1><strong>Mi Perfil</strong></h1>

            <h3>Detalles de Cuenta</h3>
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td><strong>Username</strong></td>
                        <td id="usernameCell"><?php echo htmlspecialchars($nickname); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Nombre</strong></td>
                        <td id="fullNameCell"><?php echo htmlspecialchars($nombreUsuario); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email</strong></td>
                        <td id="emailCell"><?php echo htmlspecialchars($email); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Empresa</strong></td>
                        <td id="companyCell"><?php echo htmlspecialchars($empresa); ?></td>
                </tbody>
            </table>
            <button class="btn-edit-profile" onclick="editProfile()">Editar Perfil</button>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>© 2024 LosIluminados. Todos los derechos reservados.</p>
    </footer>

    <script>

        // Función para editar el perfil
        function editProfile() {
            if (confirm("¿Estás seguro de que deseas editar tu perfil?")) {
                window.location.href = "/php/Dashboard_usuario/editarperfil.php";
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6SZXku3GK1zFk9F9KqpgJHFlvQeCIGvljKvC77x4P2Bl" crossorigin="anonymous"></script>
</body>
</html>