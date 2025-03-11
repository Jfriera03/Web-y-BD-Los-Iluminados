<?php
session_start();

if (!isset($_SESSION['nickname']) || $_SESSION['dashboardUser'] != 1) {
    header("Location: /index.php");
    exit();
}

$idAC = isset($_GET['idAC']) ? (int)$_GET['idAC'] : 0;

include '../database/db_connect.php'; // Conexión a la base de datos

// Validar si se recibió un ID de Almacenamiento válido
if ($idAC === 0) {
    die("Error: ID de Almacenamiento Virtual no válido.");
}

// Obtener los detalles del AC
$sql = "SELECT idAC, nombreCapacidad FROM capacidadCloud WHERE idAC = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idAC);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Almacenamiento Virtual no encontrado.");
}

$ac = $result->fetch_assoc();

// Consultas SQL para obtener capacidades en GB y TB
$sql_capacidad_GB = "
    SELECT CONCAT(c.nombreCapacidad, ' - ', u.unidadMedida) AS concat 
    FROM CAPACIDAD c
    JOIN capacidad_unidad cu ON c.nombreCapacidad = cu.nombreCapacidad
    JOIN unidad u ON cu.unidadMedida = u.unidadMedida AND u.unidadMedida = 'GB'
    ORDER BY c.nombreCapacidad ASC";
$result_capacidad_GB = $conn->query($sql_capacidad_GB);

$sql_capacidad_TB = "
    SELECT CONCAT(c.nombreCapacidad, ' - ', u.unidadMedida) AS concat 
    FROM CAPACIDAD c
    JOIN capacidad_unidad cu ON c.nombreCapacidad = cu.nombreCapacidad
    JOIN unidad u ON cu.unidadMedida = u.unidadMedida AND u.unidadMedida = 'TB'
    ORDER BY c.nombreCapacidad ASC";
$result_capacidad_TB = $conn->query($sql_capacidad_TB);

// Comprobar resultados y almacenar en un array
$storages = [];
if ($result_capacidad_GB && $result_capacidad_GB->num_rows > 0) {
    while ($row = $result_capacidad_GB->fetch_assoc()) {
        $storages[] = $row['concat'];
    }
}

if ($result_capacidad_TB && $result_capacidad_TB->num_rows > 0) {
    while ($row = $result_capacidad_TB->fetch_assoc()) {
        $storages[] = $row['concat'];
    }
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $capacidadSeleccionada = $_POST['capacidad'] ?? '';

    // Validar que se recibió una capacidad válida
    if (empty($capacidadSeleccionada)) {
        echo "<script>alert('Por favor, selecciona una capacidad de almacenamiento.'); window.history.back();</script>";
        exit();
    }

    // Actualizar los datos de capacidadCloud
    $sql_updateCapacidad = "
        UPDATE capacidadCloud 
        SET nombreCapacidad = ? 
        WHERE idAC = ?";
    $stmt = $conn->prepare($sql_updateCapacidad);
    $stmt->bind_param("si", $capacidadSeleccionada, $idAC);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "<script>alert('Almacenamiento Virtual actualizado correctamente.'); window.location.href = '/php/Dashboard_usuario/productos.php';</script>";
        } else {
            echo "<script>alert('No se realizaron cambios en el Almacenamiento Virtual.'); window.location.href = '/php/Dashboard_usuario/productos.php';</script>";
        }
    } else {
        echo "Error al actualizar el Almacenamiento Virtual: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Editar Almacenamiento Virtual</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Editar Almacenamiento Virtual</h1>

    <form method="POST" action="">
        <input type="hidden" name="idAC" value="<?= htmlspecialchars($ac['idAC']) ?>">

        <div class="mb-3">
            <label for="capacidad" class="form-label">Capacidad de Almacenamiento</label>
            <select class="form-select" id="capacidad" name="capacidad">
                <option disabled>Selecciona una capacidad</option>
                <?php foreach ($storages as $storage): ?>
                    <option value="<?= htmlspecialchars($storage) ?>" <?= ($storage === $ac['nombreCapacidad']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($storage) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </form>
</div>
</body>
</html>
