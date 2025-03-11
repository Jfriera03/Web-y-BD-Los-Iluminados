<?php
session_start();

if (!isset($_SESSION['nickname']) || $_SESSION['dashboardAdmin'] != 1) {
    header("Location: /index.php");
    exit();
}

$idBD = isset($_GET['idBD']) ? (int)$_GET['idBD'] : 0;

include '../database/db_connect.php'; // Conexión a la base de datos

// Obtener los detalles de la base de datos
$sql = "
    SELECT 
        bd.idBD, 
        bd.numConexiones, 
        bd.recuperarDatos,
        bd.controlAcceso,
        bd.idSGBD,
        p.idEtapa
    FROM bd
    JOIN producto p ON bd.idBD = p.idBD
    WHERE bd.idBD = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idBD);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Base de Datos no encontrada.";
    exit();
}

$bd = $result->fetch_assoc();

$sql = "SELECT idBD, nombreCapacidad FROM capacidadBD WHERE idBD = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idBD);
$stmt->execute();
$result = $stmt->get_result();

$capacidadBD = $result->fetch_assoc()['nombreCapacidad'] ?? null;

// Obtener capacidades y SGBD
$sql_capacidad_GB = "SELECT CONCAT(c.nombreCapacidad , ' - ' , u.unidadMedida) AS concat 
FROM CAPACIDAD c
JOIN capacidad_unidad cu ON c.nombreCapacidad = cu.nombreCapacidad
JOIN unidad u ON cu.unidadMedida = u.unidadMedida AND u.unidadMedida='GB'
ORDER BY c.nombreCapacidad ASC";
$result_capacidad_GB = $conn->query($sql_capacidad_GB);

$sql_sgbd = "SELECT idSGBD, nombreSGBD FROM SGBD";
$result_sgbd = $conn->query($sql_sgbd);

$sql_capacidad_TB = "SELECT CONCAT(c.nombreCapacidad , ' - ' , u.unidadMedida) AS concat 
FROM CAPACIDAD c
JOIN capacidad_unidad cu ON c.nombreCapacidad = cu.nombreCapacidad
JOIN unidad u ON cu.unidadMedida = u.unidadMedida AND u.unidadMedida='TB'
ORDER BY c.nombreCapacidad ASC";
$result_capacidad_TB = $conn->query($sql_capacidad_TB);

$sql_etapa = "SELECT idEtapa, nombreEtapa FROM ETAPA";
$result_etapa = $conn->query($sql_etapa);

$storages = [];
if ($result_capacidad_GB) {
    while ($row = $result_capacidad_GB->fetch_assoc()) {
        $storages[] = $row['concat'];
    }
}
if ($result_capacidad_TB) {
    while ($row = $result_capacidad_TB->fetch_assoc()) {
        $storages[] = $row['concat'];
    }
}

$etapas = $result_etapa->fetch_all(MYSQLI_ASSOC);

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numConexiones = isset($_POST['numConexiones']) ? (int)$_POST['numConexiones'] : null;
    $recuperarDatos = isset($_POST['recuperarDatos']) ? (int)$_POST['recuperarDatos'] : null;
    $controlAcceso = isset($_POST['controlAcceso']) ? (int)$_POST['controlAcceso'] : null;
    $idSGBD = isset($_POST['idSGBD']) ? (int)$_POST['idSGBD'] : null;
    $capacidadSeleccionada = $_POST['capacidad'] ?? '';
    $idEtapa = isset($_POST['etapa']) ? (int)$_POST['etapa'] : null;

    if (!$numConexiones || $recuperarDatos === null || $controlAcceso === null || !$idSGBD || !$capacidadSeleccionada || !$idEtapa) {
        echo "<script>alert('Por favor, completa todos los campos.'); window.history.back();</script>";
        exit();
    }

    // Actualizar los datos en la tabla `bd`
    $sql_update = "
        UPDATE bd 
        SET 
            numConexiones = ?, 
            recuperarDatos = ?, 
            controlAcceso = ?, 
            idSGBD = ?
        WHERE idBD = ?
    ";
    $stmt1 = $conn->prepare($sql_update);
    $stmt1->bind_param("iiiii", $numConexiones, $recuperarDatos, $controlAcceso, $idSGBD, $idBD);

    // Actualizar la capacidad en `capacidadBD`
    $sql_updateCapacidad = "
        UPDATE capacidadBD 
        SET nombreCapacidad = ? 
        WHERE idBD = ?
    ";
    $stmt2 = $conn->prepare($sql_updateCapacidad);
    $stmt2->bind_param("si", $capacidadSeleccionada, $idBD);

    // Actualizar la etapa del producto
    $sql_update_etapa = "
        UPDATE producto 
        SET 
            idEtapa = ? 
        WHERE idBD = ?
    ";
    $stmt3 = $conn->prepare($sql_update_etapa);
    $stmt3->bind_param("ii", $idEtapa, $idBD);

    // Ejecutar ambas consultas y verificar resultados
    if ($stmt1->execute() && $stmt2->execute() && $stmt3->execute()) {
        if ($stmt1->affected_rows > 0 || $stmt2->affected_rows > 0 || $stmt3->affected_rows > 0) {
            echo "<script>alert('Base de Datos actualizada correctamente.'); window.location.href = '/php/Dashboard_admin/productos.php';</script>";
        } else {
            echo "<script>alert('No se realizaron cambios en la Base de Datos.'); window.location.href = '/php/Dashboard_admin/productos.php';</script>";
        }
    } else {
        echo "Error al actualizar la Base de Datos: " . $conn->error;
    }

    $stmt1->close();
    $stmt2->close();
    $stmt3->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <title>Editar Base de Datos</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Editar Base de Datos</h1>

    <form method="POST" action="">
        <input type="hidden" name="idBD" value="<?= htmlspecialchars($bd['idBD']) ?>">

        <div class="mb-3">
            <label for="numConexiones" class="form-label">Número de Conexiones</label>
            <input type="number" class="form-control" id="numConexiones" name="numConexiones" value="<?= htmlspecialchars($bd['numConexiones']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="recuperarDatos" class="form-label">Recuperar Datos</label>
            <select class="form-select" id="recuperarDatos" name="recuperarDatos">
                <option value="1" <?= $bd['recuperarDatos'] == 1 ? 'selected' : '' ?>>Sí</option>
                <option value="0" <?= $bd['recuperarDatos'] == 0 ? 'selected' : '' ?>>No</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="controlAcceso" class="form-label">Control de Acceso</label>
            <select class="form-select" id="controlAcceso" name="controlAcceso">
                <option value="1" <?= $bd['controlAcceso'] == 1 ? 'selected' : '' ?>>Sí</option>
                <option value="0" <?= $bd['controlAcceso'] == 0 ? 'selected' : '' ?>>No</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="idSGBD" class="form-label">Sistema de Gestión de Bases de Datos (SGBD)</label>
            <select class="form-select" id="idSGBD" name="idSGBD" required>
                <?php while ($row = $result_sgbd->fetch_assoc()): ?>
                    <option value="<?= $row['idSGBD'] ?>" <?= $row['idSGBD'] == $bd['idSGBD'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['nombreSGBD']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="capacidad" class="form-label">Capacidad de la Base de Datos</label>
            <select class="form-select" id="capacidad" name="capacidad" required>
                <option disabled>Selecciona una capacidad</option>
                <?php foreach ($storages as $storage): ?>
                    <option value="<?= htmlspecialchars($storage) ?>" <?= ($storage == $capacidadBD) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($storage) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="etapa" class="form-label">Etapa</label>
            <select class="form-select" id="etapa" name="etapa" required>
                <?php foreach ($etapas as $etapa): ?>
                    <option value="<?= $etapa['idEtapa'] ?>" <?= $etapa['idEtapa'] == $bd['idEtapa'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($etapa['nombreEtapa']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </form>
</div>
</body>
</html>
