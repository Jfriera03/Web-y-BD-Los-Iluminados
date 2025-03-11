<?php
session_start(); // Iniciar una nueva sesión o reanudar la existente
if (!isset($_SESSION['nickname']) || $_SESSION['dashboardAdmin'] != 1) {
    header("Location: /index.php");
    exit();
}

header('Content-Type: application/json');

include '../database/db_connect.php'; // Conexión a la base de datos

try {
    // Ejecutar el procedimiento para actualizar todos los logs
    $conn->query("CALL insertar_logs()");

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>