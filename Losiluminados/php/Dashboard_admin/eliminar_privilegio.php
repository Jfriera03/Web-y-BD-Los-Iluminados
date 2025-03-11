<?php
session_start();

if (!isset($_SESSION['nickname']) || $_SESSION['dashboardAdmin'] != 1) {
    header("Location: /index.php");
    exit();
}
$nombreUsuario = $_SESSION['nombre'];

include '../database/db_connect.php'; // Conexión a la base de datos

// Obtener el ID del privilegio a eliminar
$idPrivilegio = isset($_GET['idPrivilegio']) ? $_GET['idPrivilegio'] : null;

if ($idPrivilegio) {
    // Eliminar el privilegio (y los roles asociados debido a ON DELETE CASCADE)
    $sqlDelete = "DELETE FROM privilegio WHERE idPrivilegio = ?";
    $stmt = $conn->prepare($sqlDelete);
    $stmt->bind_param("i", $idPrivilegio);

    if ($stmt->execute()) {
        $msg = "Privilegio eliminado exitosamente.";
    } else {
        $msg = "Error al eliminar el privilegio.";
    }

    $stmt->close();
} else {
    $msg = "No se especificó el privilegio a eliminar.";
}
// Redirigir después de eliminar (puedes redirigir a la página principal de gestión de privilegios)
header("Location: privilegio.php?msg=" . urlencode($msg));
exit();
?>
