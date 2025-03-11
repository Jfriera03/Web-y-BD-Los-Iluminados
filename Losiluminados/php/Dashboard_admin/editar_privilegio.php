<?php
session_start();

if (!isset($_SESSION['nickname']) || $_SESSION['dashboardAdmin'] != 1) {
    header("Location: /index.php");
    exit();
}
$nombreUsuario = $_SESSION['nombre'];

include '../database/db_connect.php'; // Conexión a la base de datos

// Obtener el ID del privilegio a editar
$idPrivilegio = isset($_GET['idPrivilegio']) ? $_GET['idPrivilegio'] : null;

if ($idPrivilegio) {
    // Obtener el privilegio actual
    $sqlPrivilegio = "SELECT p.idPrivilegio, p.nombrePrivilegio, p.descripcion, GROUP_CONCAT(pg.idGrupo) AS rolesAsignados
                      FROM privilegio p
                      LEFT JOIN privilegiodegrupo pg ON p.idPrivilegio = pg.idPrivilegio
                      WHERE p.idPrivilegio = ?
                      GROUP BY p.idPrivilegio";
    $stmt = $conn->prepare($sqlPrivilegio);
    $stmt->bind_param("i", $idPrivilegio);
    $stmt->execute();
    $result = $stmt->get_result();
    $privilegio = $result->fetch_assoc();

    // Consulta para obtener todos los grupos disponibles
    $sqlGrupos = "SELECT idGrupo, nombreGrupo FROM grupo";
    $gruposResult = $conn->query($sqlGrupos);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nombrePrivilegio = $_POST['nombrePrivilegio'];
        $descripcion = $_POST['descripcion'];
        $rolesAsignados = isset($_POST['roles']) ? $_POST['roles'] : [];

        if ($nombrePrivilegio && $descripcion) {
            // Actualizar el privilegio en la base de datos
            $sqlUpdate = "UPDATE privilegio SET nombrePrivilegio = ?, descripcion = ? WHERE idPrivilegio = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("ssi", $nombrePrivilegio, $descripcion, $idPrivilegio);

            if ($stmtUpdate->execute()) {
                // Eliminar los roles asignados actuales
                $sqlDeleteRoles = "DELETE FROM privilegiodegrupo WHERE idPrivilegio = ?";
                $stmtDeleteRoles = $conn->prepare($sqlDeleteRoles);
                $stmtDeleteRoles->bind_param("i", $idPrivilegio);
                $stmtDeleteRoles->execute();

                // Asignar los nuevos roles
                foreach ($rolesAsignados as $idGrupo) {
                    $sqlRol = "INSERT INTO privilegiodegrupo (idPrivilegio, idGrupo) VALUES (?, ?)";
                    $stmtRol = $conn->prepare($sqlRol);
                    $stmtRol->bind_param("ii", $idPrivilegio, $idGrupo);
                    $stmtRol->execute();
                }

                $msg = "Privilegio actualizado exitosamente.";
                header("Location: /php/Dashboard_admin/privilegio.php?success=PrivilegioActualizado");
                
            } else {
                $msg = "Error al actualizar el privilegio.";
            }

            $stmtUpdate->close();
        } else {
            $msg = "Por favor complete todos los campos.";
        }
    }
} else {
    // Redirigir si no se pasa el ID
    header("Location: privilegio.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Editar Privilegio</title>
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
            height: 100vh; /* Ajustar para ocupar toda la altura de la ventana */
            position: fixed;
            width: 250px;
            background-color: #5793CF;
            padding-top: 20px;
            color: white;
            top: 56px; /* Altura de la navbar */
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 15px 20px;
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: rgb(5, 71, 109);
        }
        .main-content {
            margin-left: 260px; /* Espacio para la sidebar */
            padding: 20px;
            margin-top: 56px; /* Espacio para la navbar */
        }
        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-height: 900px; /* Ajusta la altura máxima para que la tabla se desplace */
            overflow-y: auto; /* Permite desplazamiento vertical */
        }
        .table-container h1 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .btn-add-privilege {
            margin-bottom: 20px;
        }
        .form-container {
            margin-bottom: 20px;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="/php/Dashboard_admin/privilegio.php">
                    <img src="/images/logo.svg" alt="Logo" width="100" height="40">
                </a>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/php/Cuenta/logout.php">Cerrar Sesión</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="form-container">
            <h3>Editar Privilegio</h3>
            <?php if (isset($msg)): ?>
                <div class="alert alert-info">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="nombrePrivilegio" class="form-label">Nombre del Privilegio</label>
                    <input type="text" class="form-control" id="nombrePrivilegio" name="nombrePrivilegio" value="<?php echo $privilegio['nombrePrivilegio']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?php echo $privilegio['descripcion']; ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="roles" class="form-label">Roles Asignados</label>
                    <select class="form-control" id="roles" name="roles[]" multiple required>
                        <?php while ($grupo = $gruposResult->fetch_assoc()): ?>
                            <option value="<?php echo $grupo['idGrupo']; ?>" 
                                <?php echo in_array($grupo['idGrupo'], explode(',', $privilegio['rolesAsignados'])) ? 'selected' : ''; ?>>
                                <?php echo $grupo['nombreGrupo']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <small class="form-text text-muted">Mantén presionada la tecla Ctrl para seleccionar múltiples roles.</small>
                </div>
                <button type="submit" class="btn btn-success">Actualizar Privilegio</button>
            </form>
        </div>
    </main>
</body>
</html>

<?php
$conn->close();
?>