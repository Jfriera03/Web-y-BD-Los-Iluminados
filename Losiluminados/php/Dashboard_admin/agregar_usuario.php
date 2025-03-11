<?php
session_start(); // Reanudar la sesión
if (!isset($_SESSION['nickname']) || $_SESSION['dashboardAdmin'] != 1) {
    header("Location: /index.php");
    exit();
}

include '../database/db_connect.php'; // Conexión a la base de datos

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$message = ""; // Variable para mensajes al usuario
$toastClass = ""; // Clase para estilos de notificación

// Consultar ciudades
$sql_ciudad = "SELECT codigoCiudad, nombreCiudad FROM CIUDAD ORDER BY codigoCiudad ASC";
$result_ciudad = $conn->query($sql_ciudad);

// Verifica si las consultas devuelven resultados
$ciudades = [];
if ($result_ciudad && $result_ciudad->num_rows > 0) {
    while ($row = $result_ciudad->fetch_assoc()) {
        $ciudades[] = $row;
    }
}

// Consultar grupos
$sql_grupos = "SELECT idGrupo, nombreGrupo FROM GRUPO ORDER BY nombreGrupo ASC";
$result_grupos = $conn->query($sql_grupos);
$grupos = [];
if ($result_grupos && $result_grupos->num_rows > 0) {
    while ($row = $result_grupos->fetch_assoc()) {
        $grupos[] = $row;
    }
}

// Verificar si la solicitud es para comprobar el CIF (desde AJAX)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax_check_cif'])) {
    $cif = trim($_POST['cif']);
    $checkCifStmt = $conn->prepare("SELECT nombreEmpresa FROM EMPRESA WHERE CIF = ?");
    $checkCifStmt->bind_param("s", $cif);
    $checkCifStmt->execute();
    $checkCifStmt->store_result();

    if ($checkCifStmt->num_rows > 0) {
        $checkCifStmt->bind_result($nombreEmpresa);
        $checkCifStmt->fetch();
        echo json_encode(['exists' => true, 'nombreEmpresa' => $nombreEmpresa]);
    } else {
        echo json_encode(['exists' => false]);
    }
    $checkCifStmt->close();
    $conn->close();
    exit();
}

// Procesar el formulario de registro
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['ajax_check_cif'])) {
    $nombreUsuario = trim($_POST['usuario']);
    $emailUsuario = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $username = trim($_POST['username']);
    $cif = trim($_POST['cif']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    $ciudad = trim($_POST['ciudad']);
    $nombreEmpresa = trim($_POST['nombreEmpresa']);
    $emailEmpresa = trim($_POST['emailEmpresa']);
    $telEmpresa = trim($_POST['telEmpresa']);
    $dirEmpresa = trim($_POST['dirEmpresa']);
    $grupo = intval(trim($_POST['grupo'])); // Grupo seleccionado

    if ($password !== $confirmPassword) {
        $message = "Las contraseñas no coinciden.";
        $toastClass = "bg-danger";
    } else {
        // Verificar si el CIF ya existe
        $checkCifStmt = $conn->prepare("SELECT CIF FROM EMPRESA WHERE CIF = ?");
        $checkCifStmt->bind_param("s", $cif);
        $checkCifStmt->execute();
        $checkCifStmt->store_result();

        if ($checkCifStmt->num_rows == 0) {
            // Insertar empresa si no existe
            $insertEmpresaStmt = $conn->prepare("INSERT INTO EMPRESA (CIF, nombreEmpresa, emailEmpresa, telEmpresa, dirEmpresa, codigoCiudad) VALUES (?, ?, ?, ?, ?, ?)");
            $insertEmpresaStmt->bind_param("ssssss", $cif, $nombreEmpresa, $emailEmpresa, $telEmpresa, $dirEmpresa, $ciudad);
            if (!$insertEmpresaStmt->execute()) {
                $message = "Error al registrar la empresa: " . $insertEmpresaStmt->error;
                $toastClass = "bg-danger";
            }
            $insertEmpresaStmt->close();
        }
        $checkCifStmt->close();

        // Registrar usuario
        $hashContraseña = hash('sha256', $password);
        $insertUsuarioStmt = $conn->prepare("INSERT INTO USUARIO (nombreUsuario, emailUsuario, hashContraseña, CIF, nickname) VALUES (?, ?, ?, ?, ?)");
        $insertUsuarioStmt->bind_param("sssss", $nombreUsuario, $emailUsuario, $hashContraseña, $cif, $username);

        if ($insertUsuarioStmt->execute()) {
            // Asociar usuario al grupo
            $insertGrupoStmt = $conn->prepare("INSERT INTO PerteneceGrupo (nickname, idGrupo) VALUES (?, ?)");
            $insertGrupoStmt->bind_param("si", $username, $grupo);

            if ($insertGrupoStmt->execute()) {
                $message = "Cuenta creada exitosamente y asignada al grupo.";
                $toastClass = "bg-success";
                header("Location: /php/Dashboard_admin/usuario.php");
                exit();
            } else {
                $message = "Error al asignar el grupo: " . $insertGrupoStmt->error;
                $toastClass = "bg-danger";
            }

            $insertGrupoStmt->close();
        } else {
            $message = "Error al registrar el usuario: " . $insertUsuarioStmt->error;
            $toastClass = "bg-danger";
        }
        $insertUsuarioStmt->close();
    }
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <title>Light It Up! - Registro</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="author" content="">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="/css/vendor.css">
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Open+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
        }

        .register-container {
            width: 400px;
            margin: 100px auto;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .register-container h2 {
            margin-bottom: 30px;
            text-align: center;
        }

        .register-container form {
            text-align: center;
        }

        .register-container input[type="text"],
        .register-container input[type="email"],
        .register-container input[type="password"],
        .register-container input[type="email"],
        .register-container input#nombreEmpresa {
            width: 80%;
            padding: 12px 20px;
            margin: 10px auto; /* Asegura el centrado vertical */
            border: 1px solid #cccccc;
            border-radius: 4px;
            display: block; /* Garantiza que esté centrado horizontalmente */
            background-color: #ffffff; /* Fondo blanco */
        }

        .register-container input#nombreEmpresa {
            display: none; /* Oculto por defecto */
            background-color: #e9ecef; /* Fondo gris claro para denotar que es no editable */
            color: #6c757d; /* Texto gris */
        }

        .register-container button {
            width: 80%;
            padding: 12px 20px;
            background-color: #333291;
            border: none;
            border-radius: 4px;
            color: #ffffff;
            font-size: 16px;
        }

        .register-container button:hover {
            background-color: #302eb8;
        }

        .login-link {
            margin-top: 20px;
            display: block;
            text-align: center;
        }
    </style>


</head>
<body>
    <!-- Navbar -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-grey px-3">
            <div class="container-fluid">
                <a class="navbar-brand" href="/php/Dashboard_admin/usuario.php">
                    <img src="/images/logo.svg" alt="Logo" width="100" height="40">
                </a>
            </div>
        </nav>
    </header>

    <main class="container mt-5">
        <div class="register-container custom-width">
            <h2>Registro de Usuario</h2>
            <form method="POST" action="" id="registerForm">
                <input type="text" name="usuario" placeholder="Nombre Completo" required>
                <input type="text" name="username" placeholder="Nombre de Usuario" required>
                <input type="email" name="email" placeholder="Correo Electrónico" required>
                <select name="grupo" class="form-select" required>
                    <option selected disabled>Seleccione un grupo</option>
                    <?php foreach ($grupos as $grupo): ?>
                        <option value="<?php echo $grupo['idGrupo']; ?>">
                            <?php echo htmlspecialchars($grupo['nombreGrupo'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" id="cif" name="cif" placeholder="CIF de la Empresa" required>
                <input type="text" id="nombreEmpresa" name="nombreEmpresa" placeholder="Nombre de la Empresa" readonly>
                <div id="extraEmpresaFields" style="display: none;">
                    <input type="text" name="nombreEmpresa" placeholder="Nombre de la Empresa">
                    <input type="text" name="emailEmpresa" placeholder="Correo de la Empresa">
                    <input type="text" name="telEmpresa" placeholder="Teléfono de la Empresa">
                    <input type="text" name="dirEmpresa" placeholder="Dirección de la Empresa">
                    <select class="form-select" id="ciudad" name="ciudad" style= width:20px>
                        <option selected disabled>Seleccione Ciudad</option>
                        <?php
                        foreach ($ciudades as $ciudad) {
                            echo '<option value="' . $ciudad['codigoCiudad'] . '">' . $ciudad['nombreCiudad'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <input type="password" name="password" placeholder="Contraseña" required>
                <input type="password" name="confirm_password" placeholder="Confirmar Contraseña" required>
                <button type="submit">Registrar</button>
            </form>

            <?php if (!empty($message)): ?>
            <div class="message <?php echo $toastClass; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>



    <footer class="py-5">
        <div class="container-lg">
            <div class="row">
                <div class="col-md-6">
                    <p>© 2024 LosIluminados. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </footer>
    <script src="/js/jquery-1.11.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
    <script src="/js/plugins.js"></script>
    <script src="/js/script.js"></script>
    <script>
        document.getElementById('cif').addEventListener('blur', function () {
            const cifInput = this.value; //Se captura el valor ingresado en el campo CIF
            const nombreEmpresaField = document.getElementById('nombreEmpresa'); //Campo para mostrar el nombre de la empresa
            const extraFields = document.getElementById('extraEmpresaFields'); //Contenedor de campos adicionales

            // Verificar si el campo CIF está vacío
            if (cifInput.trim() === '') {
                nombreEmpresaField.style.display = 'none'; //Ocultar campo de nombre de empresa
                extraFields.style.display = 'none'; //Ocultar campos adicionales
                return;
            }

            // Enviar solicitud AJAX para verificar si el CIF existe
            fetch('', {
                method: 'POST', //Método HTTP
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded', //Tipo de contenido
                },
                body: new URLSearchParams({
                    ajax_check_cif: true, //Indicador de que es una solicitud de verificación
                    cif: cifInput //CIF ingresado por el usuario
                })
            })
            .then(response => response.json()) //Convertir la respuesta a JSON
            .then(data => {
                if (data.exists) {
                    //Si el CIF existe, mostrar el nombre de la empresa
                    nombreEmpresaField.style.display = 'block';
                    nombreEmpresaField.value = data.nombreEmpresa; // Asignar el nombre de la empresa
                    extraFields.style.display = 'none'; //Ocultar campos adicionales
                } else {
                    //Si el CIF no existe, mostrar campos adicionales
                    nombreEmpresaField.style.display = 'none';
                    extraFields.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error verificando el CIF:', error); //Manejo de errores
            });
        });
    </script>
</body>
</html>