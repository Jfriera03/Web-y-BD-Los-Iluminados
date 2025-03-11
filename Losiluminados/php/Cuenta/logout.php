<?php
    //Inicia la sesión
    session_start();

    //Desactivar todas las variables de sesión
    $_SESSION = array();

    //Destruye la sesión
    session_destroy();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Cerrar Sesión</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .logout-container {
            text-align: center;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .logout-container h1 {
            color: #343a40;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <h1>Has cerrado sesión</h1>
        <p>Gracias por visitarnos. Te redirigiremos en unos segundos...</p>
        <p>Si no eres redirigido automáticamente, haz clic en el botón de abajo.</p>
        <a href="/php/Cuenta/login.php" class="btn btn-primary">Ir a Inicio de Sesión</a>
    </div>

    <script>
        // Redirigir automáticamente a la página de inicio de sesión después de 5 segundos
        setTimeout(function() {
            window.location.href = "/php/Cuenta/login.php";
        }, 5000);
    </script>
</body>
</html>