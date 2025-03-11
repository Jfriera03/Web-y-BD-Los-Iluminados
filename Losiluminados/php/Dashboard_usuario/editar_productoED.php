<?php
session_start();

if (!isset($_SESSION['nickname']) || $_SESSION['dashboardUser'] != 1) {
    header("Location: /index.php");
    exit();
}

$idED = isset($_GET['idED']) ? (int)$_GET['idED'] : 0;

include '../database/db_connect.php'; // Conexión a la base de datos

// Obtener los detalles del Entorno de Desarrollo
$sql = "
    SELECT 
        ed.idED, 
        ed.idGit
    FROM ENTORNO_DESAROLLO ed
    WHERE ed.idED = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idED);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Entorno de Desarrollo no encontrado.";
    exit();
}

$entorno = $result->fetch_assoc();

// Obtener tipos de Git
$sql_git = "SELECT idGit, tipoGit FROM GIT";
$result_git = $conn->query($sql_git);

// Obtener lenguajes asociados al entorno
$sql_languages = "
    SELECT le.nombreLenguaje 
    FROM lenguajedeentorno le
    WHERE le.idEntornoDesarrollo = ?
";
$stmt = $conn->prepare($sql_languages);
$stmt->bind_param("i", $idED);
$stmt->execute();
$result_languages = $stmt->get_result();

$languages = [];
while ($row = $result_languages->fetch_assoc()) {
    $languages[] = $row['nombreLenguaje'];
}

// Obtener todas las lenguajes disponibles
$sql_all_languages = "SELECT nombreLenguaje FROM LENGUAJE";
$result_all_languages = $conn->query($sql_all_languages);

// Obtener librerías asociadas al entorno
$sql_libraries = "
    SELECT l.idLibreria 
    FROM libreriadeentorno le
    JOIN LIBRERIA l ON le.idLibreria = l.idLibreria
    WHERE le.idEntornoDesarrollo = ?
";
$stmt = $conn->prepare($sql_libraries);
$stmt->bind_param("i", $idED);
$stmt->execute();
$result_libraries = $stmt->get_result();

$libraries = [];
while ($row = $result_libraries->fetch_assoc()) {
    $libraries[] = $row['idLibreria'];
}

// Obtener todas las librerías disponibles
$sql_all_libraries = "SELECT idLibreria, nombreLibreria FROM LIBRERIA";
$result_all_libraries = $conn->query($sql_all_libraries);

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idGit = isset($_POST['idGit']) ? (int)$_POST['idGit'] : null;
    $selectedLanguages = isset($_POST['languages']) ? $_POST['languages'] : [];
    $selectedLibraries = isset($_POST['libraries']) ? $_POST['libraries'] : [];

    if (!$idGit) {
        echo "<script>alert('Por favor, selecciona un tipo de Git.'); window.history.back();</script>";
        exit();
    }

    // Actualizar Entorno de Desarrollo
    $sql_update = "
        UPDATE ENTORNO_DESAROLLO
        SET idGit = ?
        WHERE idED = ?
    ";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ii", $idGit, $idED);

    if ($stmt->execute()) {
        // Actualizar los lenguajes asociados
        $sql_delete_languages = "DELETE FROM lenguajedeentorno WHERE idEntornoDesarrollo = ?";
        $stmt = $conn->prepare($sql_delete_languages);
        $stmt->bind_param("i", $idED);
        $stmt->execute();

        foreach ($selectedLanguages as $language) {
            $sql_insert_language = "
                INSERT INTO lenguajedeentorno (idEntornoDesarrollo, nombreLenguaje)
                VALUES (?, ?)
            ";
            $stmt = $conn->prepare($sql_insert_language);
            $stmt->bind_param("is", $idED, $language);
            $stmt->execute();
        }

        // Actualizar las librerías asociadas
        $sql_delete_libraries = "DELETE FROM libreriadeentorno WHERE idEntornoDesarrollo = ?";
        $stmt = $conn->prepare($sql_delete_libraries);
        $stmt->bind_param("i", $idED);
        $stmt->execute();

        foreach ($selectedLibraries as $libraryId) {
            $sql_insert_library = "
                INSERT INTO libreriadeentorno (idEntornoDesarrollo, idLibreria)
                VALUES (?, ?)
            ";
            $stmt = $conn->prepare($sql_insert_library);
            $stmt->bind_param("ii", $idED, $libraryId);
            $stmt->execute();
        }

        echo "<script>alert('Entorno de Desarrollo actualizado correctamente.'); window.location.href = '/php/Dashboard_usuario/productos.php';</script>";
    } else {
        echo "Error al actualizar el Entorno de Desarrollo: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Editar Entorno de Desarrollo</title>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Editar Entorno de Desarrollo</h1>
    <form method="POST" action="">
        <input type="hidden" name="idED" value="<?= htmlspecialchars($entorno['idED']) ?>">

        <div class="mb-3">
            <label for="idGit" class="form-label">Tipo de Git</label>
            <select class="form-select" id="idGit" name="idGit" required>
                <?php while ($row = $result_git->fetch_assoc()): ?>
                    <option value="<?= $row['idGit'] ?>" <?= $row['idGit'] == $entorno['idGit'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['tipoGit']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="languages" class="form-label">Lenguajes</label>
            <select class="form-select" id="languages" name="languages[]" multiple>
                <?php while ($row = $result_all_languages->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($row['nombreLenguaje']) ?>" <?= in_array($row['nombreLenguaje'], $languages) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['nombreLenguaje']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <small class="text-muted">Usa Ctrl (Windows) o Cmd (Mac) para seleccionar múltiples lenguajes.</small>
        </div>

        <div class="mb-3">
            <label for="libraries" class="form-label">Librerías</label>
            <select class="form-select" id="libraries" name="libraries[]" multiple>
                <?php while ($row = $result_all_libraries->fetch_assoc()): ?>
                    <option value="<?= $row['idLibreria'] ?>" <?= in_array($row['idLibreria'], $libraries) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['nombreLibreria']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <small class="text-muted">Usa Ctrl (Windows) o Cmd (Mac) para seleccionar múltiples librerías.</small>
        </div>

        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </form>
</div>
</body>
</html>
