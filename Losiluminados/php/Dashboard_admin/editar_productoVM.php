<?php
session_start();

if (!isset($_SESSION['nickname']) || $_SESSION['dashboardAdmin'] != 1) {
    header("Location: /index.php");
    exit();
}

$idVM = isset($_GET['idVM']) ? (int)$_GET['idVM'] : 0;

include '../database/db_connect.php'; // Conexión a la base de datos

// Obtener los detalles de la VM
$sql = "
    SELECT 
        vm.idVM, 
        vm.idCPU, 
        vm.idRAM, 
        vm.idAlmacenamiento, 
        vm.idSO,
        p.idEtapa
    FROM vm
    JOIN producto p ON vm.idVM = p.idVM
    WHERE vm.idVM = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idVM);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Máquina Virtual no encontrada.";
    exit();
}

$vm = $result->fetch_assoc();

// Consultar las opciones para los campos de Máquina Virtual
$sql_cpu = "SELECT idCPU, modelo FROM CPU";
$sql_ram = "SELECT idRAM, modelo FROM RAM";
$sql_storage = "SELECT idAlmacenamiento, CONCAT(nombreAlmacenamiento, ' - ', tipo, ' - ', capacidad, ' - ', unidadCapacidad) AS descCompleta FROM ALMACENAMIENTO";
$sql_os = "SELECT idSO, nombreSO FROM SISTEMA_OPERATIVO";
$sql_etapa = "SELECT idEtapa, nombreEtapa FROM ETAPA";

$result_cpu = $conn->query($sql_cpu);
$result_ram = $conn->query($sql_ram);
$result_storage = $conn->query($sql_storage);
$result_os = $conn->query($sql_os);
$result_etapa = $conn->query($sql_etapa);

$cpus = $result_cpu->fetch_all(MYSQLI_ASSOC);
$rams = $result_ram->fetch_all(MYSQLI_ASSOC);
$storages = $result_storage->fetch_all(MYSQLI_ASSOC);
$oss = $result_os->fetch_all(MYSQLI_ASSOC);
$etapas = $result_etapa->fetch_all(MYSQLI_ASSOC);

// Procesar el formulario
// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idVM = isset($_POST['idVM']) ? (int)$_POST['idVM'] : 0;
    $idCPU = isset($_POST['cpu']) ? (int)$_POST['cpu'] : null;
    $idRAM = isset($_POST['ram']) ? (int)$_POST['ram'] : null;
    $idAlmacenamiento = isset($_POST['storage']) ? (int)$_POST['storage'] : null;
    $idSO = isset($_POST['os']) ? (int)$_POST['os'] : null;
    $idEtapa = isset($_POST['etapa']) ? (int)$_POST['etapa'] : null;

    // Validar que los datos no estén vacíos
    if (!$idVM || !$idCPU || !$idRAM || !$idAlmacenamiento || !$idSO || !$idEtapa) {
        echo "<script>alert('Por favor, completa todos los campos.'); window.history.back();</script>";
        exit();
    }

    // Actualizar los datos de la máquina virtual
    $sql_update = "
        UPDATE vm 
        SET 
            idCPU = ?, 
            idRAM = ?, 
            idAlmacenamiento = ?, 
            idSO = ? 
        WHERE idVM = ?
    ";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("iiiii", $idCPU, $idRAM, $idAlmacenamiento, $idSO, $idVM);

    // Actualizar
    if ($stmt->execute()) {
        // Actualizar la etapa del producto
        $sql_update_etapa = "
        UPDATE producto 
        SET 
            idEtapa = ? 
        WHERE idVM = ?
        ";
        $stmt_etapa = $conn->prepare($sql_update_etapa);
        $stmt_etapa->bind_param("ii", $idEtapa, $idVM);
        $stmt_etapa->execute();

        if ($stmt->affected_rows > 0 || $stmt_etapa->affected_rows > 0) {
        echo "<script>alert('Máquina Virtual actualizada correctamente.'); window.location.href = '/php/Dashboard_admin/productos.php';</script>";
        } else {
        echo "<script>alert('No se realizaron cambios en la Máquina Virtual.'); window.location.href = '/php/Dashboard_admin/productos.php';</script>";
        }
    } else {
        echo "Error al actualizar la Máquina Virtual: " . $stmt->error;
    }

    $stmt->close();
    $stmt_etapa->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Editar Máquina Virtual</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Editar Máquina Virtual</h1>

    <form method="POST" action="">
        <input type="hidden" name="idVM" value="<?= $vm['idVM'] ?>">

        <div class="mb-3">
            <label for="cpu" class="form-label">CPU</label>
            <select class="form-select" id="cpu" name="cpu">
                <?php foreach ($cpus as $cpu): ?>
                    <option value="<?= $cpu['idCPU'] ?>" <?= $cpu['idCPU'] == $vm['idCPU'] ? 'selected' : '' ?>>
                        <?= $cpu['modelo'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="ram" class="form-label">RAM</label>
            <select class="form-select" id="ram" name="ram">
                <?php foreach ($rams as $ram): ?>
                    <option value="<?= $ram['idRAM'] ?>" <?= $ram['idRAM'] == $vm['idRAM'] ? 'selected' : '' ?>>
                        <?= $ram['modelo'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="storage" class="form-label">Almacenamiento</label>
            <select class="form-select" id="storage" name="storage">
                <?php foreach ($storages as $storage): ?>
                    <option value="<?= $storage['idAlmacenamiento'] ?>" <?= $storage['idAlmacenamiento'] == $vm['idAlmacenamiento'] ? 'selected' : '' ?>>
                        <?= $storage['descCompleta'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="os" class="form-label">Sistema Operativo</label>
            <select class="form-select" id="os" name="os">
                <?php foreach ($oss as $os): ?>
                    <option value="<?= $os['idSO'] ?>" <?= $os['idSO'] == $vm['idSO'] ? 'selected' : '' ?>>
                        <?= $os['nombreSO'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="etapa" class="form-label">Etapa</label>
            <select class="form-select" id="etapa" name="etapa">
                <?php foreach ($etapas as $etapa): ?>
                    <option value="<?= $etapa['idEtapa'] ?>" <?= $etapa['idEtapa'] == $vm['idEtapa'] ? 'selected' : '' ?>>
                        <?= $etapa['nombreEtapa'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </form>
</div>
</body>
</html>
