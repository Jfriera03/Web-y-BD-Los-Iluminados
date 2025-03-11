<?php 
  include '../database/db_connect.php'; //Conexión a la base de datos

session_start(); // Inicia la sesión

// Verifica si el usuario ha iniciado sesión
$isLoggedIn = isset($_SESSION['nickname']);

// Consulta para los privilegios
$sqlComprar = "SELECT CASE WHEN COUNT(*) > 0 THEN '1' ELSE '0' END AS tienePrivilegio
                FROM UsuarioPrivilegio
                WHERE usuario = ? AND privilegio = 'Comprar_Productos'";
$stmt = $conn->prepare($sqlComprar);
$stmt->bind_param("s", $_SESSION['nickname']);
$stmt->execute();
$result = $stmt->get_result();
$privilegioCompra = $result->fetch_assoc()['tienePrivilegio'] ?? '0'; // Maneja el caso en que no se devuelvan resultados
$stmt->close(); // Cierra el statement para liberar los resultados

// Consultas SQL para obtener los datos
$sql_languages = "SELECT nombreLenguaje FROM LENGUAJE";
$sql_libraries = "SELECT idLibreria, nombreLibreria FROM LIBRERIA";
$sql_git_types = "SELECT idGit, tipoGit FROM GIT";
$sql_version_languages = "SELECT nombreLenguaje, numeroVersion FROM versiondelenguaje";

// Ejecutar las consultas
$result_languages = $conn->query($sql_languages);
$result_libraries = $conn->query($sql_libraries);
$result_git_types = $conn->query($sql_git_types);
$result_version_languages = $conn->query($sql_version_languages);

// Verifica si las consultas devuelven resultados
$languages = [];
if ($result_languages->num_rows > 0) {
    while($row = $result_languages->fetch_assoc()) {
        $languages[] = $row;
    }
}

$libraries = [];
if ($result_libraries->num_rows > 0) {
    while($row = $result_libraries->fetch_assoc()) {
        $libraries[] = $row;
    }
}

$git_types = [];
if ($result_git_types->num_rows > 0) {
    while($row = $result_git_types->fetch_assoc()) {
        $git_types[] = $row;
    }
}

$version_languages = [];
if ($result_version_languages->num_rows > 0) {
    while($row = $result_version_languages->fetch_assoc()) {
        $version_languages[] = $row;
    }
}
?>

<?php
// Cerrar la conexión
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Light It Up! - Entorno de Desarrollo</title>
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
            background-color: #f0f0f0;
        }
        .content-container {
            display: flex;
            align-items: flex-start;
            margin-top: 50px;
        }
        .content-container img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-right: 20px;
        }
        .content-container .info {
            max-width: 600px;
        }
        .image-resize {
            width: 600px; 
            height: auto;
        }
        .info {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .custom-checkbox .form-check-input {
            width: 1.25em;
            height: 1.25em;
            border: 1px solid #000; /* Color del contorno */
            border-radius: 0.25em; /* Bordes redondeados */
            margin-right: 0.5em; /* Espacio entre el checkbox y la etiqueta */
        }
        
        .footer-container {
          background-color: #f7f7f7;
          padding: 20px 0;
        }
        .language-container {
            background-color: #ffcccb; /* Fondo rosa */
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <header>
      <nav class="navbar navbar-expand-lg navbar-light bg-light px-3">
        <div class="container-fluid">
          <!-- Logo a la izquierda -->
          <a class="navbar-brand" href="/index.php">
            <img src="/images/logo.svg" alt="Logo" width="100" height="50">
          </a>
          <!-- Botón de colapso para móviles -->
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <!-- Opciones del menú -->
          <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Opciones a la derecha -->
            <ul class="navbar-nav d-flex align-items-center ms-auto">
              <!-- Iniciar sesión -->
              <li class="nav-item">
                <?php if ($isLoggedIn): ?>
                  <a class="nav-link" href="/php/Dashboard_usuario/inicio.php">
                    <svg width="24" height="24"><use xlink:href="#user"></use></svg>
                    Ver Perfil
                  </a>
                <?php else: ?>
                  <a class="nav-link" href="/php/Cuenta/login.php">
                    <svg width="24" height="24"><use xlink:href="#user"></use></svg>
                    Login
                  </a>
                <?php endif; ?>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </header>

    <main class="container mt-5">
        <div class="d-flex align-items-start">
            <img src="/images/entornoDesarrollo.png" alt="Entorno de Desarrollo" class="image-resize me-3">
            <!-- Cuadro de Compra -->
            <div class="purchase-box mb-5" style="background-color: #f8f9fa; padding: 20px; border-radius: 8px;">
                <h2>Configure su Entorno de Desarrollo</h2>
                <form method="GET" action="configProductoED.php">
                    <div class="mb-3">
                        <label for="numLanguages" class="form-label">Número de Lenguajes</label>
                        <input type="number" class="form-control" id="numLanguages" name="numLanguages" min="1" max="10" placeholder="Número de Lenguajes" required>
                    </div>
                    <div id="languagesContainer" style="display: none;"></div>

                    <div class="mb-3">
                        <label class="form-label">Librerías de Programación</label>
                        <div class="row">
                            <?php
                            foreach ($libraries as $library) {
                                echo '<div class="col-md-6">';
                                echo '<div class="form-check custom-checkbox">';
                                echo '<input class="form-check-input" type="checkbox" id="' . $library['nombreLibreria'] . '" name="libraries[]" value="' . $library['idLibreria'] . '">';
                                echo '<label class="form-check-label" for="' . $library['nombreLibreria'] . '">' . $library['nombreLibreria'] . '</label>';
                                echo '</div>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="gitType" class="form-label">Tipo de Git</label>
                        <select class="form-select" id="gitType" name="gitType" required>
                            <option value="" disabled selected>Seleccione Tipo de Git</option>
                            <?php
                            foreach ($git_types as $git_type) {
                                echo '<option value="' . $git_type['idGit'] . '">' . $git_type['tipoGit'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <?php if ($isLoggedIn && $privilegioCompra): ?>
                        <button type="submit" class="btn btn-primary">Comprar</button>
                  <?php else: ?>
                      <?php if (!$isLoggedIn): ?>
                        <button class="btn btn-secondary" disabled>Inicie sesión para comprar</button>
                      <?php else: ?>
                        <button class="btn btn-secondary" disabled>No tiene permisos para comprar</button>
                      <?php endif; ?> 
                  <?php endif; ?> 
                </form>
            </div>
        </div>
        <!-- Fin Cuadro de Compra -->
        <div class="content-container">
            <div>
                <h1>¿Qué es un Entorno de Desarrollo?</h1>
                <p>Un entorno de desarrollo es un conjunto de herramientas y procesos que los desarrolladores utilizan para crear, probar y mantener software. Incluye editores de código, compiladores, depuradores y otras herramientas necesarias para el desarrollo de software.</p>
                
                <h2>Ventajas</h2>
                <ul>
                    <li>Productividad: Facilitan la escritura y prueba de código de manera eficiente.</li>
                    <li>Colaboración: Permiten a los equipos de desarrollo trabajar juntos de manera efectiva.</li>
                    <li>Depuración: Ofrecen herramientas para identificar y corregir errores en el código.</li>
                    <li>Automatización: Ayudan a automatizar tareas repetitivas, como la compilación y las pruebas.</li>
                </ul>
            </div>
        </div>
    </main>

    <footer class="py-5 footer footer-container">
      <div class="container-lg">
        <div class="row">
          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="footer-menu">
              <img src="/images/logo.svg" width="240" height="70" alt="logo">
            </div>
          </div>
          <div class="col-md-2 col-sm-6">
            <div class="footer-menu">
              <h5 class="widget-title">Light It Up!</h5>
              <ul class="menu-list list-unstyled">
                <li class="menu-item">
                  <a href="/php/Otros/integrantes.php" class="nav-link">Sobre Nosotros</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </footer>

    <div id="footer-bottom" class="footer-container">
      <div class="container-lg">
        <div class="row">
          <div class="col-md-6 copyright">
            <p>© 2024 LosIluminados. All rights reserved.</p>
          </div>
        </div>
      </div>
    </div>

      <script src="/js/jquery-1.11.0.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
      <script src="/js/plugins.js"></script>
      <script src="/js/script.js"></script>
      <script>
        const versions = <?php echo json_encode($version_languages); ?>;

        function updateVersions(languageSelectId, versionSelectId) {
            const languageSelect = document.getElementById(languageSelectId);
            const versionSelect = document.getElementById(versionSelectId);
            const selectedLanguage = languageSelect.value;

            // Limpiar las opciones actuales
            versionSelect.innerHTML = '<option selected>Seleccione Versión de Lenguaje</option>';

            // Filtrar y agregar las nuevas opciones
            versions.forEach(version => {
                if (version.nombreLenguaje === selectedLanguage) {
                    const option = document.createElement('option');
                    option.value = version.numeroVersion;
                    option.textContent = version.numeroVersion;
                    versionSelect.appendChild(option);
                }
            });
        }
    </script>
    <script>
    document.getElementById('numLanguages').addEventListener('input', function () {
        const numLanguages = parseInt(this.value, 10);
        const languagesContainer = document.getElementById('languagesContainer');

        // Limpiar el contenedor de lenguajes antes de agregar nuevos campos
        languagesContainer.innerHTML = '';

        // Si no hay lenguajes (valor 0 o vacío), ocultar el contenedor
        if (isNaN(numLanguages) || numLanguages <= 0) {
            languagesContainer.style.display = 'none';
            return;
        }

        // Mostrar el contenedor de lenguajes si hay un número válido
        languagesContainer.style.display = 'block';

        // Generar campos para los lenguajes
        for (let i = 0; i < numLanguages; i++) {
            const languageDiv = document.createElement('div');
            languageDiv.classList.add('language-container');
            const languageSelectId = `language${i + 1}`;
            languageDiv.innerHTML = `
                <div class="mb-3">
                    <label for="${languageSelectId}" class="form-label">Lenguaje de Programación ${i + 1}</label>
                    <select class="form-select" id="${languageSelectId}" name="languages[]" required>
                        <option value="" disabled selected>Seleccione Lenguaje de Programación</option>
                        <?php
                        foreach ($languages as $language) {
                            echo '<option value="' . $language['nombreLenguaje'] . '">' . $language['nombreLenguaje'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            `;
            languagesContainer.appendChild(languageDiv);
        }
    });
    </script>
</body>
</html>