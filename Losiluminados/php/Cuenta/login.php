<?php 
// Conexión a la base de datos
include '../database/db_connect.php';

// Inicialización de variables
$message = "";
$toastClass = "";
$dashboard_admin = 0;
// Verifica si el formulario fue enviado usando el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar y sanitizar entradas (se usa por temas de seguridad)
    $nickname = filter_var(trim($_POST['nickname']), FILTER_SANITIZE_STRING);
    $password = trim($_POST['password']);
    $hashed_password = hash('sha256', $password);

    

    if (!empty($nickname) && !empty($password)) {
        // Se prepara una consulta SQL para buscar al usuario por su correo
        $stmt = $conn->prepare("SELECT hashContraseña FROM usuario WHERE nickname = ?");
        $stmt->bind_param("s", $nickname); // Enlaza el parámetro $email con el marcador de posición ?
        
        // La consulta se ejecuta, y los resultados se almacenan
        $stmt->execute();
        $stmt->store_result();

        // Si hay un usuario con el correo electrónico proporcionado
        if ($stmt->num_rows > 0) {
            // Se extraen la contraseña hash y el nombre de usuario de la base de datos
            $stmt->bind_result($db_password);
            $stmt->fetch();

            // Se compara la contraseña proporcionada por el usuario con el hash almacenado en la base de datos
            if ($hashed_password == $db_password) {
                // Éxito: Se inicia una sesión y se redirige al usuario al panel de control
                $message = "Login successful";
                $toastClass = "bg-success";

                // Si la contraseña es correcta, procedemos a obtener el nombre del usuario
                // Realizamos una consulta adicional para obtener el nombre del usuario
                $stmt->close(); // Cerramos la consulta anterior

                // Nueva consulta para obtener el nombre del usuario
                $stmt = $conn->prepare("SELECT nombreUsuario FROM usuario WHERE nickname = ?");
                $stmt->bind_param("s", $nickname); // Enlaza el parámetro $email nuevamente
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($db_nombre);
                $stmt->fetch();
                $stmt->close(); // Cerramos la consulta anterior

                //Nueva consulta para obtener la empresa del usuario
                $stmt = $conn->prepare("SELECT nombreEmpresa FROM empresa JOIN usuario ON empresa.CIF = usuario.CIF WHERE nickname = ?");
                $stmt->bind_param("s", $nickname); // Enlaza el parámetro $email nuevamente
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($db_empresa);
                $stmt->fetch();
                $stmt->close(); // Cerramos la consulta anterior

                // Nueva consulta para obtener el email del usuario
                $stmt = $conn->prepare("SELECT emailUsuario FROM usuario WHERE nickname = ?");
                $stmt->bind_param("s", $nickname); // Enlaza el parámetro $email nuevamente
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($db_email);
                $stmt->fetch();
                $stmt->close(); // Cerramos la consulta anterior


                // Iniciar sesión y redirigir
                session_start();
                $_SESSION['nickname'] = $nickname;
                $_SESSION['nombre'] = $db_nombre;
                $_SESSION['empresa'] = $db_empresa;
                $_SESSION['email'] = $db_email;

                // Consulta para obtener el grupo del usuario
                $sql = "SELECT idGrupo FROM PerteneceGrupo WHERE nickname = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $nickname);
                $stmt->execute();
                $result = $stmt->get_result();
                $idGrupo = null;

                if ($result->num_rows > 0) {
                    $idGrupo = $result->fetch_assoc()['idGrupo'];
                }

                // Redirigir según el grupo al que pertenece
                switch ($idGrupo) {
                    case 1: // Grupo 1
                        header("Location: /php/Dashboard_admin/inicio.php");
                        break;
                    case 2: // Grupo 2
                        header("Location: /php/Dashboard_usuario/inicio.php");
                        break;
                    case 3: // Grupo 3
                        header("Location: /php/Dashboard_usuario/inicio.php");
                        break;
                    default: // Otros grupos
                        header("Location: /php/Dashboard_usuario/inicio.php");
                        break;
                }
                exit();

            } else { // Fracaso: Muestra un mensaje de error si la contraseña no coincide
                $message = "Contraseña incorrecta";
                $toastClass = "bg-danger";
            }
        } else { // Si no hay un usuario con ese correo, se muestra un mensaje de que el correo no fue encontrado
            $message = "Nombre de usuario no encontrado";
            $toastClass = "bg-warning";
        }
        
        // Se cierra la consulta
        $stmt->close();
    } else { 
        // Si no se proporcionaron datos, se muestra un mensaje de error
        $message = "Todos los campos son obligatorios";
        $toastClass = "bg-danger";
    }
    
    // Se cierra la conexión a la base de datos
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Light It Up! - Login</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url(/images/fondo_login.jpg);
            background-size: cover;
            background-position: center;
        }
        .login-container {
            width: 350px;
            margin: 100px auto;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .login-container h2 {
            text-align: center;
        }
        .login-container input {
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .login-container button {
            width: 100%;
            padding: 10px;
            background-color: #333291;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        .login-container button:hover {
            background-color: #302eb8;
        }
        .message {
            text-align: center;
            margin-top: 10px;
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
            </div>
        </nav>
    </header>

    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <form method="POST">
            <input type="text" name="nickname" placeholder="Nombre de Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Entrar</button>
        </form>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $toastClass; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div style="margin-top: 20px; text-align: center;">
            <p>¿No tienes una cuenta? <a href="/php/Cuenta/registro.php">Regístrate</a></p>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</html>
