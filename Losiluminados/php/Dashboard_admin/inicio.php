<?php
    session_start();

    if (!isset($_SESSION['nickname']) || $_SESSION['dashboardAdmin'] != 1) {
        header("Location: /index.php");
        exit();
    }
    $nombreUsuario = $_SESSION['nombre'];

    // Verificar si hay un mensaje en la sesión
    $message = isset($_SESSION['profile_message']) ? $_SESSION['profile_message'] : '';
    $toastClass = isset($_SESSION['toast_class']) ? $_SESSION['toast_class'] : '';

    // Limpiar el mensaje después de mostrarlo
    unset($_SESSION['profile_message']);
    unset($_SESSION['toast_class']);
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
        .sidebar {
            height: calc(100vh - 56px); /* Adjust height excluding navbar */
            position: fixed;
            width: 250px;
            background-color: #5793CF;;
            color: white;
            padding-top: 20px;
            top: 56px; /* Ajuste para la altura de la navbar */
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 15px 20px; /* Adds internal spacing */
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: rgb(5, 71, 109);
            margin-bottom: 0; /* Ensure no visual gap when hovering */
        }
        .main-content {
            margin-left: 260px; /* Space for the sidebar */
            padding: 20px;
            margin-top: 56px; /* Space for the navbar */
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-grey px-3">
            <div class="container-fluid">
                <!-- Logo -->
                <a class="navbar-brand" href="/index.php">
                    <img src="/images/logo.svg" alt="Logo" width="100" height="40">
                </a>
                <!-- Opciones del menú -->
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
        <h1>Bienvenido, <?php echo htmlspecialchars($nombreUsuario); ?>!</h1>
        <p>Estamos encantados de verte aquí. ¡Esperamos que tengas un gran día!</p>

        <!-- Mostrar mensaje si existe -->
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $toastClass; ?>" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Si tienes el nombre del usuario desde el backend, puedes insertarlo dinámicamente.
        const userName = "Juan Pérez"; // Ejemplo de nombre obtenido
        document.getElementById('userName').innerText = userName;
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6SZXku3GK1zFk9F9KqpgJHFlvQeCIGvljKvC77x4P2Bl" crossorigin="anonymous"></script>
</body>
</html>
