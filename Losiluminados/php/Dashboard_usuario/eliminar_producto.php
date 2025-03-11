<?php
session_start();

if (!isset($_SESSION['nickname']) || $_SESSION['dashboardUser'] != 1) {
    header("Location: /index.php");
    exit();
}

$nickname = $_SESSION['nickname'];
$productoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productoId === 0) {
    echo "Producto no especificado.";
    exit();
}

include '../database/db_connect.php'; // Conexión a la base de datos

// Verificar si el producto existe y pertenece al usuario
$sql = "SELECT producto.idProducto, producto.nombreProducto FROM producto JOIN pedido ON producto.idProducto = pedido.idProducto JOIN usuario ON pedido.nickname = usuario.nickname WHERE producto.idProducto = ? AND usuario.nickname = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $productoId, $nickname); // Vinculamos el id del producto y el email del usuario
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No tienes permiso para eliminar este producto o el producto no existe.";
    exit();
}

// Eliminar el producto
$deleteSql = "DELETE FROM producto WHERE idProducto = ? AND email_usuario = ?";
$deleteStmt = $conn->prepare($deleteSql);
$deleteStmt->bind_param("is", $productoId, $nickname);

if ($deleteStmt->execute()) {
    // Redirigir con éxito
    header("Location: /php/Dashboard_usuario/productos.php?mensaje=Producto eliminado correctamente.");
    exit();
} else {
    echo "Error al eliminar el producto: " . $conn->error;
}

$conn->close();
?>