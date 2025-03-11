<?php
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

    if ($password !== $confirmPassword) {
        $message = "Las contraseñas no coinciden.";
        $toastClass = "bg-danger";
    } else {
        // Verificar si la empresa ya existe
        $checkCifStmt = $conn->prepare("SELECT CIF FROM EMPRESA WHERE CIF = ?");
        $checkCifStmt->bind_param("s", $cif);
        $checkCifStmt->execute();
        $checkCifStmt->store_result();

        if ($checkCifStmt->num_rows == 0) {
            // Insertar nueva empresa si no existe
            $codigoCiudad = trim($_POST['ciudad']);
            $nombreEmpresa = trim($_POST['nombreEmpresaExtra']);
            $emailEmpresa = trim($_POST['emailEmpresa']);
            $telEmpresa = trim($_POST['telEmpresa']);
            $dirEmpresa = trim($_POST['dirEmpresa']);

            $insertEmpresaStmt = $conn->prepare("INSERT INTO EMPRESA (CIF, nombreEmpresa, emailEmpresa, telEmpresa, dirEmpresa, codigoCiudad) VALUES (?, ?, ?, ?, ?, ?)");
            $insertEmpresaStmt->bind_param("ssssss", $cif, $nombreEmpresa, $emailEmpresa, $telEmpresa, $dirEmpresa, $codigoCiudad);
            if (!$insertEmpresaStmt->execute()) {
                $message = "Error al registrar la empresa: " . $insertEmpresaStmt->error;
                $toastClass = "bg-danger";
            }
    
            $insertEmpresaStmt->close();
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Insertar redes de la empresa
            $numRedes = isset($_POST['numRedes']) ? intval($_POST['numRedes']) : 0;

            $insertRedStmt = $conn->prepare("INSERT INTO RED (ipPublica, tipo, CIF) VALUES (?, ?, ?)");
            for ($i = 1; $i <= $numRedes; $i++) {
                $ipPublica = trim($_POST["ipPublicaRed$i"]);
                $tipoRed = trim($_POST["tipoRed$i"]);
                $cif = trim($_POST["cif"]);

                // Validar ipPublica, tipoRed y CIF
                if (strlen($ipPublica) <= 16 && strlen($cif) <= 16 && strlen($tipoRed) <= 32) {
                    $insertRedStmt->bind_param("sss", $ipPublica, $tipoRed, $cif);
                    if (!$insertRedStmt->execute()) {
                        $message = "Error al registrar la red $i: " . $insertRedStmt->error;
                        $toastClass = "bg-danger";
                    } else {
                        // Insertar VLANs de la red
                        $numVlans = isset($_POST["numVlansRed$i"]) ? intval($_POST["numVlansRed$i"]) : 0;
                        $insertVlanStmt = $conn->prepare("INSERT INTO VLAN (nombreVLAN, ipPublica) VALUES (?, ?)");
                        for ($j = 1; $j <= $numVlans; $j++) {
                            $nombreVLAN = trim($_POST["nombreVLANRed{$i}Vlan{$j}"]);
                            $insertVlanStmt->bind_param("ss", $nombreVLAN, $ipPublica);
                            if (!$insertVlanStmt->execute()) {
                                $message = "Error al registrar la VLAN $j de la red $i: " . $insertVlanStmt->error;
                                $toastClass = "bg-danger";
                            }
                        }
                        $insertVlanStmt->close();
                    }
                } else {
                    $message = "Error: Datos inválidos para la red $i.";
                    $toastClass = "bg-danger";
                }
            }
            $insertRedStmt->close();
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            

        }
        $checkCifStmt->close();

        // Registrar usuario
        $hashContraseña = hash('sha256', $password);
        $insertUsuarioStmt = $conn->prepare("INSERT INTO USUARIO (nombreUsuario, emailUsuario, hashContraseña, CIF, nickname) VALUES (?, ?, ?, ?, ?)");
        $insertUsuarioStmt->bind_param("sssss", $nombreUsuario, $emailUsuario, $hashContraseña, $cif, $username);
        // Registrar usuario
        $hashContraseña = hash('sha256', $password);
        $insertUsuarioStmt = $conn->prepare("INSERT INTO USUARIO (nombreUsuario, emailUsuario, hashContraseña, CIF, nickname) VALUES (?, ?, ?, ?, ?)");
        $insertUsuarioStmt->bind_param("sssss", $nombreUsuario, $emailUsuario, $hashContraseña, $cif, $username);
        if ($insertUsuarioStmt->execute()) {
            // Asignar al grupo de invitados (idGrupo = 3)
            $idGrupoInvitado = 3;
            $insertGrupoStmt = $conn->prepare("INSERT INTO PerteneceGrupo (nickname, idGrupo) VALUES (?, ?)");
            $insertGrupoStmt->bind_param("si", $username, $idGrupoInvitado);

            if ($insertGrupoStmt->execute()) {
                $message = "Cuenta creada exitosamente.";
                $toastClass = "bg-success";

                /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                ////////////////////////////////////////////////AQUI TE HACE ADMIN///////////////////////////////////////////////////////////////////////
                // Comprobar si el CIF coincide con uno específico y ejecutar el procedimiento hacer_admin
                if ($cif === 'Z87654321') { // Reemplaza 'CIF_ESPECIFICO' con el CIF que deseas comprobar
                    $hacerAdminStmt = $conn->prepare("CALL hacer_admin(?)");
                    $hacerAdminStmt->bind_param("s", $username);
                    $hacerAdminStmt->execute();
                    $hacerAdminStmt->close();
                    echo "<script>alert('¡Felicidades! Ahora eres un administrador.');</script>";
                }
                /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            } else {
                $message = "Error al asignar el usuario al grupo de invitados: " . $insertGrupoStmt->error;
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
        .red-container {
            background-color: #f0f0f0; /* Gris claro */
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .vlan-container {
            background-color: #ffe6e6; /* Rosa claro */
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
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

    <main class="container mt-5">
        <div class="register-container custom-width">
            <h2>Registro</h2>
            <form method="POST" action="" id="registerForm">
                <input type="text" name="usuario" placeholder="Nombre Completo" required>
                <input type="text" name="username" placeholder="Nombre de Usuario" required>
                <input type="email" name="email" placeholder="Correo Electrónico" required>
                <input type="text" id="cif" name="cif" placeholder="CIF de la Empresa" required>
                <input type="text" id="nombreEmpresa" name="nombreEmpresa" placeholder="Nombre de la Empresa" readonly>
                <div id="extraEmpresaFields" style="display: none;">
                    <input type="text" name="nombreEmpresaExtra" placeholder="Nombre de la Empresa">
                    <input type="email" name="emailEmpresa" placeholder="Correo de la Empresa">
                    <input type="text" name="telEmpresa" placeholder="Teléfono de la Empresa">
                    <input type="text" name="dirEmpresa" placeholder="Dirección de la Empresa">
                    <select class="form-select" id="ciudad" name="ciudad">
                        <option selected disabled>Seleccione Ciudad</option>
                        <?php
                        foreach ($ciudades as $ciudad) {
                            echo '<option value="' . $ciudad['codigoCiudad'] . '">' . $ciudad['nombreCiudad'] . '</option>';
                        }
                        ?>
                    </select>
                    <input type="number" class="form-control" id="numRedes" name="numRedes" min="1" max="10" placeholder='Número de Redes'>
                    <div id="redesContainer" style="display: none;">
                        <input type="text" name="ipPublicaEmpresa" placeholder="ipPublica de tu Empresa">
                        <select class="form-select" id="tipoRed" name="tipoRed" required>
                            <option selected disabled>Seleccione el tipo de red</option>
                            <option value="Privada">Privada</option>
                            <option value="Publica">Pública</option>
                        </select>
                        <input type="text" name="nombreVLAN" placeholder="Nombre VLAN de la Red de tu Empresa">
                    </div>
                </div>
                <input type="password" name="password" placeholder="Contraseña" required>
                <input type="password" name="confirm_password" placeholder="Confirmar Contraseña" required>
                <button type="submit">Registrarse</button>
            </form>


            <?php if (!empty($message)): ?>
            <div class="message <?php echo $toastClass; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <div class="login-link">
                ¿Ya tienes una cuenta? <a href="/php/Cuenta/login.php">Inicia sesión</a>
            </div>
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
            const cifInput = this.value;
            const nombreEmpresaField = document.getElementById('nombreEmpresa');
            const extraFields = document.getElementById('extraEmpresaFields');

            if (cifInput.trim() === '') {
                nombreEmpresaField.value = '';
                nombreEmpresaField.style.display = 'none';
                extraFields.style.display = 'none';
                return;
            }

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ ajax_check_cif: true, cif: cifInput })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        // Empresa encontrada
                        nombreEmpresaField.style.display = 'block';
                        nombreEmpresaField.value = data.nombreEmpresa;
                        extraFields.style.display = 'none';
                    } else {
                        // Empresa no encontrada
                        nombreEmpresaField.style.display = 'none';
                        extraFields.style.display = 'block';
                    }
                })
                .catch(error => console.error('Error verificando el CIF:', error));
        });

    </script>
    <!-- Campo para el número de redes -->
    <input type="number" class="form-control" id="numRedes" name="numRedes" min="1" max="10" placeholder='Número de Redes'>
    <div id="redesContainer" style="display: none;"></div>

    <script>
    document.getElementById('numRedes').addEventListener('input', function () {
        const numRedes = parseInt(this.value, 10);
        const redesContainer = document.getElementById('redesContainer');

        // Limpiar el contenedor de redes antes de agregar nuevos campos
        redesContainer.innerHTML = '';

        // Si no hay redes (valor 0 o vacío), ocultar el contenedor
        if (isNaN(numRedes) || numRedes <= 0) {
            redesContainer.style.display = 'none';
            return;
        }

        // Mostrar el contenedor de redes si hay un número válido
        redesContainer.style.display = 'block';

        // Generar campos para las redes
        for (let i = 0; i < numRedes; i++) {
            const redDiv = document.createElement('div');
            redDiv.classList.add('red-container');
            redDiv.innerHTML = `
                <div class="input-group">
                    <label for="ipPublicaRed${i + 1}">IP de la Red ${i + 1}:</label>
                    <input type="text" id="ipPublicaRed${i + 1}" name="ipPublicaRed${i + 1}" placeholder="IP de la Empresa" required>
                </div>
                <div class="input-group">
                    <label for="tipoRed${i + 1}">Tipo de Red ${i + 1}:</label>
                    <select id="tipoRed${i + 1}" name="tipoRed${i + 1}" required>
                        <option value="privada">Privada</option>
                        <option value="publica">Pública</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="numVlansRed${i + 1}">Número de VLANs para la Red ${i + 1}:</label>
                    <input type="number" id="numVlansRed${i + 1}" name="numVlansRed${i + 1}" min="1" max="10" placeholder="Número de VLANs" required>
                </div>
                <div id="vlansContainerRed${i + 1}" class="vlans-container"></div>
            `;
            redesContainer.appendChild(redDiv);

            // Agregar evento para generar campos de VLANs
            document.getElementById(`numVlansRed${i + 1}`).addEventListener('input', function () {
                const numVlans = parseInt(this.value, 10);
                const vlansContainer = document.getElementById(`vlansContainerRed${i + 1}`);

                // Limpiar el contenedor de VLANs antes de agregar nuevos campos
                vlansContainer.innerHTML = '';

                // Si no hay VLANs (valor 0 o vacío), ocultar el contenedor
                if (isNaN(numVlans) || numVlans <= 0) {
                    vlansContainer.style.display = 'none';
                    return;
                }

                // Mostrar el contenedor de VLANs si hay un número válido
                vlansContainer.style.display = 'block';

                // Generar campos para las VLANs
                for (let j = 0; j < numVlans; j++) {
                    const vlanDiv = document.createElement('div');
                    vlanDiv.classList.add('vlan-container');
                    vlanDiv.innerHTML = `
                        <div class="input-group">
                            <label for="nombreVLANRed${i + 1}Vlan${j + 1}">Nombre de la VLAN ${j + 1}:</label>
                            <input type="text" id="nombreVLANRed${i + 1}Vlan${j + 1}" name="nombreVLANRed${i + 1}Vlan${j + 1}" placeholder="Nombre de la VLAN" required>
                        </div>
                    `;
                    vlansContainer.appendChild(vlanDiv);
                }
            });
        }
    });
    </script>

</body>
</html>