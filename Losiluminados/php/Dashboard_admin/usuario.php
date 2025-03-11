<?php
session_start();

if (!isset($_SESSION['nickname']) || $_SESSION['dashboardAdmin'] != 1) {
    header("Location: /index.php");
    exit();
}

include '../database/db_connect.php'; // Conexión a la base de datos

$nickname = $_SESSION['nickname'];


// Comprobación de privilegios

$sqlEditar = "SELECT CASE WHEN COUNT(*) > 0 THEN '1' ELSE '0' END AS tienePrivilegio
            FROM UsuarioPrivilegio
            WHERE usuario = ? AND privilegio = 'Editar_Úsuarios'";
$stmt = $conn->prepare($sqlEditar);
$stmt->bind_param("s", $nickname);
$stmt->execute();
$result = $stmt->get_result();
$tienePrivilegioEditar = $result->fetch_assoc()['tienePrivilegio'] ?? '0'; // Maneja el caso en que no se devuelvan resultados
$stmt->close(); // Cierra el statement para liberar los resultados

// Consulta para verificar privilegios de eliminación
$sqlEliminar = "SELECT CASE WHEN COUNT(*) > 0 THEN '1' ELSE '0' END AS tienePrivilegio
            FROM UsuarioPrivilegio
            WHERE usuario = ? AND privilegio = 'Eliminar_Usuarios'";
$stmt = $conn->prepare($sqlEliminar);
$stmt->bind_param("s", $nickname);
$stmt->execute();
$result = $stmt->get_result();
$tienePrivilegioEliminar = $result->fetch_assoc()['tienePrivilegio'] ?? '0'; // Maneja el caso en que no se devuelvan resultados
$stmt->close(); // Cierra el statement para liberar los resultados

// Consulta para obtener los usuarios registrados
$sql = "SELECT nickname, nombreUsuario, emailUsuario FROM usuario WHERE nickname != ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("s", $_SESSION['nickname']);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Si la consulta falla, manejar el error
    die("Error al preparar la consulta: " . $conn->error);
}

// Consulta para obtener los usuarios registrados y sus grupos
$sql = "
    SELECT 
        u.nickname, 
        u.nombreUsuario, 
        u.emailUsuario,
        GROUP_CONCAT(g.nombreGrupo ORDER BY g.nombreGrupo ASC) AS grupo
    FROM 
        USUARIO u
    LEFT JOIN 
        PERTENECEGRUPO pg ON u.nickname = pg.nickname
    LEFT JOIN 
        GRUPO g ON pg.idGrupo = g.idGrupo
    WHERE 
        u.nickname != ?
    GROUP BY 
        u.nickname, u.nombreUsuario, u.emailUsuario";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("s", $_SESSION['nickname']);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die("Error al preparar la consulta: " . $conn->error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nickname'])) {
    $nicknameToDelete = $_POST['nickname'];

    try {
        // Iniciar transacción
        $conn->begin_transaction();

        // Obtener los IDs de los productos asociados al usuario
        $sql = "
            SELECT 
                P.idProducto, P.idVM, P.idBD, P.idED, P.idAC
            FROM 
                PEDIDO AS PD
            INNER JOIN PRODUCTO AS P ON PD.idProducto = P.idProducto
            WHERE 
                PD.nickname = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nicknameToDelete);
        $stmt->execute();
        $result = $stmt->get_result();

        $sql_grupo = "SELECT nombreGrupo FROM grupo g JOIN pertenecegrupo pg ON pg.idGrupo = g.idGrupo JOIN usuario u ON u.nickname = pg.nickname WHERE u.nickname = ?";
        $stmt = $conn->prepare($sql_grupo);
        $stmt->bind_param("s", $nicknameToDelete);
        $stmt->execute();
        $result_grupo = $stmt->get_result();

        // Almacenar los IDs en arrays
        $productos = [];
        $vms = [];
        $bds = [];
        $eds = [];
        $acs = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row['idProducto'];
            if ($row['idVM']) $vms[] = $row['idVM'];
            if ($row['idBD']) $bds[] = $row['idBD'];
            if ($row['idED']) $eds[] = $row['idED'];
            if ($row['idAC']) $acs[] = $row['idAC'];
        }

        // Eliminar Máquinas Virtuales
        if (!empty($vms)) {
            $vmIds = implode(',', array_map('intval', $vms));
            $conn->query("DELETE FROM VM WHERE idVM IN ($vmIds)");
        }

        // Eliminar Bases de Datos
        if (!empty($bds)) {
            $bdIds = implode(',', array_map('intval', $bds));
            $conn->query("DELETE FROM BD WHERE idBD IN ($bdIds)");
        }

        // Eliminar Entornos de Desarrollo
        if (!empty($eds)) {
            $edIds = implode(',', array_map('intval', $eds));
            $conn->query("DELETE FROM ENTORNO_DESAROLLO WHERE idED IN ($edIds)");
        }

        // Eliminar Almacenamiento en la Nube
        if (!empty($acs)) {
            $acIds = implode(',', array_map('intval', $acs));
            $conn->query("DELETE FROM ALMACENAMIENTO_CLOUD WHERE idAC IN ($acIds)");
        }

        // Eliminar Productos
        if (!empty($productos)) {
            $productIds = implode(',', array_map('intval', $productos));
            $conn->query("DELETE FROM PRODUCTO WHERE idProducto IN ($productIds)");
        }

        // Eliminar Pedidos
        $conn->query("DELETE FROM PEDIDO WHERE nickname = '$nicknameToDelete'");

        // Eliminar Usuario
        $stmt = $conn->prepare("DELETE FROM USUARIO WHERE nickname = ?");
        $stmt->bind_param("s", $nicknameToDelete);
        $stmt->execute();

        // Confirmar transacción
        $conn->commit();
        $message = "Usuario y sus datos asociados eliminados con éxito.";
        $toastClass = "alert-success";

        // Redirigir a la misma página
        header("Location: /php/Dashboard_admin/usuario.php");
        exit();

    } catch (Exception $e) {
        // Revertir cambios en caso de error
        $conn->rollback();
        $message = "Error al eliminar el usuario: " . $e->getMessage();
        $toastClass = "alert-danger";
    }
}
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <title>Gestión de Usuarios</title>
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
            height: calc(100vh - 56px); /* Excluye la altura del navbar */
            width: 250px; /* Asegura un ancho fijo */
            position: fixed;
            background-color: #5793CF;
            color: white;
            padding-top: 20px;
            top: 56px; /* Navbar height */
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 15px 20px; /* Espaciado interno consistente */
            width: 100%; /* Asegura que ocupen todo el ancho disponible */
            transition: background-color 0.3s ease;
        }

        .sidebar a:hover {
            background-color: rgb(5, 71, 109);
            margin-bottom: 0; /* Elimina cualquier margen visual al hacer hover */
        }

        .main-content {
            margin-left: 260px; /* Space for the sidebar */
            padding: 20px;
            margin-top: 56px; /* Space for the navbar */
        }
        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .table-container h1 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .btn-add-user {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="/index.php">
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

    <!-- Sidebar -->
    <div class="sidebar">
        <h3 class="text-center">Dashboard</h3>
        <a href="/php/Dashboard_admin/inicio.php">Inicio</a>
        <a href="/php/Dashboard_admin/grupo.php">Grupos</a>
        <a href="/php/Dashboard_admin/usuario.php">Usuarios</a>
        <a href="/php/Dashboard_admin/privilegio.php">Privilegios</a>
        <a href="/php/Dashboard_admin/monitorizacion.php">Monitorización</a>
        <a href="/php/Dashboard_admin/stock.php">Stock</a>
        <a href="/php/Dashboard_admin/productos.php">Productos</a>
    </div>
    

    <!-- Main Content -->
    <main class="main-content">
        <div class="table-container">
            <h1>Gestión de Usuarios</h1>
            <button class="btn btn-primary btn-add-user" onclick="addUser()">Agregar Usuario</button>
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Nombre del Usuario</th>
                        <th scope="col">Username</th>
                        <th scope="col">Correo Electrónico</th>
                        <th scope="col">Grupo de Privilegios</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['nombreUsuario'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['nickname'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['emailUsuario'], ENT_QUOTES, 'UTF-8') . "</td>";

                        // Mostrar grupo o nada si el usuario no pertenece a uno
                        $grupo = $row['grupo'] ?? ''; // Usar valor vacío si es NULL
                        echo "<td>" . htmlspecialchars($grupo, ENT_QUOTES, 'UTF-8') . "</td>";

                        echo "<td>
                            <a href=\"/php/Dashboard_admin/editar_usuario.php?nickname=" . htmlspecialchars($row['nickname'], ENT_QUOTES, 'UTF-8') . "\" 
                                class=\"btn btn-sm btn-warning " . ($tienePrivilegioEditar == '0' ? 'disabled' : '') . "\">
                                Editar
                            </a>
                            <button class='btn btn-sm btn-danger " . ($tienePrivilegioEliminar == '0' ? 'disabled' : '') . "' 
                                onclick=\"confirmDelete('" . htmlspecialchars($row['nickname'], ENT_QUOTES, 'UTF-8') . "')\" 
                                " . ($tienePrivilegioEliminar == '0' ? 'disabled' : '') . ">
                                Eliminar
                            </button>
                        </td>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center'>No hay usuarios disponibles.</td></tr>";
                }
                ?>
            </tbody>


            </table>
        </div>
        <form id="deleteForm" method="POST" action="/php/Dashboard_admin/usuario.php" style="display: none;">
            <input type="hidden" name="nickname">
        </form>

    </main>

    <script>
        function addUser() {
            window.location.href = '/php/Dashboard_admin/agregar_usuario.php';
        }

        function editUser() {
            window.location.href = '/php/Dashboard_admin/editar_usuario.php';
        }

        function confirmDelete(nickname) {
            if (confirm("¿Estás seguro de que deseas eliminar este usuario?")) {
                const deleteForm = document.getElementById('deleteForm');
                deleteForm.nickname.value = nickname;
                deleteForm.submit();
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6SZXku3GK1zFk9F9KqpgJHFlvQeCIGvljKvC77x4P2Bl" crossorigin="anonymous"></script>
</body>
</html>

<?php
// Cerrar la conexión
$conn->close();
?>