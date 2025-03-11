<?php
    session_start();

    if (!isset($_SESSION['nickname']) || $_SESSION['dashboardUser'] != 1) {
        header("Location: /index.php");
        exit();
    }

    include '../database/db_connect.php'; // Conexión a la base de datos

    $nickname = $_SESSION['nickname'];
    
    //////////////////////////////////////
    ///////COMPROBACION DE permisos///////

    $sqlEditar = "SELECT CASE WHEN COUNT(*) > 0 THEN '1' ELSE '0' END AS tienePrivilegio
                FROM UsuarioPrivilegio
                WHERE usuario = ? AND privilegio = 'Editar_Productos'";
    $stmt = $conn->prepare($sqlEditar);
    $stmt->bind_param("s", $nickname);
    $stmt->execute();
    $result = $stmt->get_result();
    $tienePrivilegioEditar = $result->fetch_assoc()['tienePrivilegio'] ?? '0'; // Maneja el caso en que no se devuelvan resultados
    $stmt->close(); // Cierra el statement para liberar los resultados

    // Consulta para verificar privilegios de eliminación
    $sqlEliminar = "SELECT CASE WHEN COUNT(*) > 0 THEN '1' ELSE '0' END AS tienePrivilegio
                FROM UsuarioPrivilegio
                WHERE usuario = ? AND privilegio = 'Eliminar_Productos'";
    $stmt = $conn->prepare($sqlEliminar);
    $stmt->bind_param("s", $nickname);
    $stmt->execute();
    $result = $stmt->get_result();
    $tienePrivilegioEliminar = $result->fetch_assoc()['tienePrivilegio'] ?? '0'; // Maneja el caso en que no se devuelvan resultados
    $stmt->close(); // Cierra el statement para liberar los resultados

    //Consultas para obtener los productos de la empresa del usuario que está logueado
    $sql_vm = "
        SELECT 
            p.idProducto, 
            p.nombreProducto, 
            p.ipPrivada, 
            p.ipPublica, 
            v.nombreVLAN,
            e.nombreEtapa, 
            p.idVM,
            vm.precioBase + c.precio + r.precio + a.precio + MAX(d.precio) AS precioTotal
        FROM PRODUCTO p
        JOIN ETAPA e ON p.idEtapa = e.idEtapa
        JOIN VLAN v ON p.idVLAN = v.idVLAN
        JOIN VM vm ON p.idVM = vm.idVM
        JOIN CPU c ON vm.idCPU = c.idCPU
        JOIN RAM r ON vm.idRAM = r.idRAM
        JOIN ALMACENAMIENTO a ON vm.idAlmacenamiento = a.idAlmacenamiento
        JOIN SISTEMA_OPERATIVO so ON so.idSO = vm.idSO
        JOIN DISTRIBUCION d ON d.idSO = so.idSO 
        WHERE p.idVM IS NOT NULL AND p.idProducto IN (
            SELECT idProducto 
            FROM PEDIDO 
            JOIN USUARIO ON PEDIDO.nickname = USUARIO.nickname 
            JOIN EMPRESA ON USUARIO.CIF = EMPRESA.CIF 
            WHERE EMPRESA.CIF = (
                SELECT CIF 
                FROM USUARIO 
                WHERE nickname = ?
            )
        )
        GROUP BY p.idProducto, p.nombreProducto, p.ipPrivada, p.ipPublica, v.nombreVLAN, e.nombreEtapa, p.idVM, vm.precioBase, c.precio, r.precio, a.precio
        ";

    $sql_bd = "
        SELECT 
            p.idProducto, 
            p.nombreProducto, 
            p.ipPrivada, 
            p.ipPublica, 
            v.nombreVLAN, 
            e.nombreEtapa,
            bd.numConexiones, 
            p.idBD,
            (bd.precioBase + s.precio + (
                SELECT MAX(cu.precio)
                FROM CAPACIDADBD cbd
                JOIN CAPACIDAD c ON cbd.nombreCapacidad = c.nombreCapacidad
                JOIN CAPACIDAD_UNIDAD cu ON cu.nombreCapacidad = c.nombreCapacidad
                WHERE cbd.idBD = bd.idBD
            )) AS precioTotal 
        FROM PRODUCTO p
        JOIN VLAN v ON p.idVLAN = v.idVLAN
        JOIN BD bd ON p.idBD = bd.idBD
        JOIN SGBD s ON bd.idSGBD = s.idSGBD
        JOIN ETAPA e ON p.idEtapa = e.idEtapa
        WHERE p.idBD IS NOT NULL AND p.idProducto IN (
            SELECT idProducto 
            FROM PEDIDO 
            JOIN USUARIO ON PEDIDO.nickname = USUARIO.nickname 
            JOIN EMPRESA ON USUARIO.CIF = EMPRESA.CIF 
            WHERE EMPRESA.CIF = (
                SELECT CIF 
                FROM USUARIO 
                WHERE nickname = ?
            )
        )";
    

    $sql_ed = "
        SELECT 
            p.idProducto, 
            p.nombreProducto, 
            p.ipPrivada, 
            p.ipPublica, 
            v.nombreVLAN, 
            p.idED,
            e.nombreEtapa,
            ed.precioBase + g.precioGit AS precioTotal
        FROM PRODUCTO p
        JOIN VLAN v ON p.idVLAN = v.idVLAN
        JOIN ENTORNO_DESAROLLO ed ON p.idED = ed.idED
        JOIN GIT g ON ed.idGit = g.idGit
        JOIN ETAPA e ON p.idEtapa = e.idEtapa
        WHERE p.idED IS NOT NULL AND p.idProducto IN (
            SELECT idProducto 
            FROM PEDIDO 
            JOIN USUARIO ON PEDIDO.nickname = USUARIO.nickname 
            JOIN EMPRESA ON USUARIO.CIF = EMPRESA.CIF 
            WHERE EMPRESA.CIF = (
                SELECT CIF 
                FROM USUARIO 
                WHERE nickname = ?
            )
        )";
    

        $sql_ac = "
        SELECT 
            p.idProducto, 
            p.nombreProducto, 
            p.ipPrivada, 
            p.ipPublica, 
            v.nombreVLAN, 
            p.idAC,
            e.nombreEtapa,
            ac.precioBase + MAX(cu.precio) AS precioTotal
        FROM PRODUCTO p
        JOIN VLAN v ON p.idVLAN = v.idVLAN
        JOIN ALMACENAMIENTO_CLOUD ac ON p.idAC = ac.idAC
        JOIN CapacidadCloud cc ON ac.idAC = cc.idAC
        JOIN ETAPA e ON p.idEtapa = e.idEtapa
        JOIN CAPACIDAD_UNIDAD cu ON cc.nombreCapacidad = cu.nombreCapacidad
        WHERE p.idAC IS NOT NULL AND p.idProducto IN (
            SELECT idProducto 
            FROM PEDIDO 
            JOIN USUARIO ON PEDIDO.nickname = USUARIO.nickname 
            JOIN EMPRESA ON USUARIO.CIF = EMPRESA.CIF 
            WHERE EMPRESA.CIF = (
                SELECT CIF 
                FROM USUARIO 
                WHERE nickname = ?
            )
        )
        GROUP BY p.idProducto, p.nombreProducto, p.ipPrivada, p.ipPublica, v.nombreVLAN, e.nombreEtapa, p.idAC, ac.precioBase
        ";
    

    //Ejecutar consultas para cada tipo de producto
    $stmt_vm = $conn->prepare($sql_vm);
    $stmt_vm->bind_param("s", $nickname);
    $stmt_vm->execute();
    $result_vm = $stmt_vm->get_result();

    $stmt_bd = $conn->prepare($sql_bd);
    $stmt_bd->bind_param("s", $nickname);
    $stmt_bd->execute();
    $result_bd = $stmt_bd->get_result();

    $stmt_ed = $conn->prepare($sql_ed);
    $stmt_ed->bind_param("s", $nickname);
    $stmt_ed->execute();
    $result_ed = $stmt_ed->get_result();

    $stmt_ac = $conn->prepare($sql_ac);
    $stmt_ac->bind_param("s", $nickname);
    $stmt_ac->execute();
    $result_ac = $stmt_ac->get_result();

    // Procesar eliminación de componentes
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete_vm'])) {
        $idVM = filter_var($_GET['delete_vm'], FILTER_VALIDATE_INT);

        if (!$idVM) {
            echo "ID inválido";
            exit();
        }

        // Eliminar el VM
        $sql_delete_VM = "DELETE FROM vm WHERE idVM = ?";
        $stmt = $conn->prepare($sql_delete_VM);
        $stmt->bind_param("i", $idVM);

        if ($stmt->execute()) {
            echo "VM eliminado correctamente.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Error al eliminar VM: " . $stmt->error;
        }

        $stmt->close();
    }
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete_bd'])) {
        $idBD = filter_var($_GET['delete_bd'], FILTER_VALIDATE_INT);

        if (!$idBD) {
            echo "ID inválido";
            exit();
        }

        // Eliminar el BD
        $sql_delete_BD = "DELETE FROM bd WHERE idBD = ?";
        $stmt = $conn->prepare($sql_delete_BD);
        $stmt->bind_param("i", $idBD);

        if ($stmt->execute()) {
            echo "BD eliminado correctamente.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Error al eliminar BD: " . $stmt->error;
        }

        $stmt->close();
    }
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete_ed'])) {
        $idED = filter_var($_GET['delete_ed'], FILTER_VALIDATE_INT);
    
        if (!$idED) {
            echo "ID inválido";
            exit();
        }
    
        // Eliminar el Entorno de Desarrollo
        $sql_delete_ED = "DELETE FROM ENTORNO_DESAROLLO WHERE idED = ?";
        $stmt = $conn->prepare($sql_delete_ED);
        $stmt->bind_param("i", $idED);
    
        if ($stmt->execute()) {
            echo "Entorno de Desarrollo eliminado correctamente.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Error al eliminar Entorno de Desarrollo: " . $stmt->error;
        }
    
        $stmt->close();
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete_ac'])) {
        $idAC = filter_var($_GET['delete_ac'], FILTER_VALIDATE_INT);

        if (!$idAC) {
            echo "ID inválido";
            exit();
        }

        // Eliminar el AC
        $sql_delete_AC = "DELETE FROM almacenamiento_cloud WHERE idAC = ?";
        $stmt = $conn->prepare($sql_delete_AC);
        $stmt->bind_param("i", $idAC);

        if ($stmt->execute()) {
            echo "AC eliminado correctamente.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Error al eliminar AC: " . $stmt->error;
        }

        $stmt->close();
    }

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <title>Mis Productos</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
        }

        /* Sidebar */
        .sidebar {
            height: calc(100vh - 56px); /* Ajusta la altura sin contar la navbar */
            position: fixed;
            width: 250px;
            background-color: #9357cf;
            color: white;
            padding-top: 20px;
            top: 56px; /* Ajuste para la altura de la navbar */
        }

        .sidebar h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 15px 20px; /* Espaciado interno */
            transition: background-color 0.3s ease;
        }

        .sidebar a:hover {
            background-color: #4d2674;
        }

        /* Main content */
        .main-content {
            margin-left: 260px; /* Espacio para el sidebar */
            padding: 20px;
            margin-top: 56px; /* Espacio para la navbar */
        }

        .product-table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 1000px;
            margin: auto;
            margin-bottom: 20px;
        }

        .product-table-container h1 {
            font-size: 1.75rem;
            margin-bottom: 20px;
        }

        .product-table-container table th, .product-table-container table td {
            vertical-align: middle;
        }

        .btn-edit, .btn-delete, .btn-recover {
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-edit {
            background-color: #6fa77d;
            color: white;
        }

        .btn-edit:hover {
            background-color: #5c8e6a;
        }

        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }

        .btn-delete:hover {
            background-color: #c0392b;
        }

        .btn-recover {
            background-color: #3498db;
            color: white;
        }

        .btn-recover:hover {
            background-color: #2980b9;
        }

        footer {
            background-color: #f1f1f1;
            padding: 35px 0;
            margin-top: 20px;
            text-align: center;
        }

        #footer-bottom {
            background-color: #e1e1e1;
            padding: 10px 0;
        }

        .disabled {
            pointer-events: none;
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-grey px-3">
            <div class="container-fluid">
                <a class="navbar-brand" href="/index.php">
                    <img src="/images/logo.svg" alt="Logo" width="100" height="40">
                </a>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/php/Cuenta/logout.php"><i></i>Cerrar Sesión</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Dashboard</h3>
        <a href="/php/Dashboard_usuario/inicio.php">Inicio</a>
        <a href="/php/Dashboard_usuario/perfil.php">Mi Perfil</a>
        <a href="/php/Dashboard_usuario/productos.php">Mis Productos</a>
        <a href="/php/Dashboard_usuario/ajustes.php">Ajustes</a>

    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="product-table-container">
            <h2>Máquinas Virtuales</h2>
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Nombre</th>
                        <th scope="col">IP Privada</th>
                        <th scope="col">IP Pública</th>
                        <th scope="col">VLAN</th>
                        <th scope="col">Precio</th>
                        <th scope="col">Etapa</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_vm->num_rows > 0): ?>
                        <?php while ($row = $result_vm->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nombreProducto']) ?></td>
                                <td><?= htmlspecialchars($row['ipPrivada']) ?></td>
                                <td><?= htmlspecialchars($row['ipPublica']) ?></td>
                                <td><?= htmlspecialchars($row['nombreVLAN']) ?></td>
                                <td><?= number_format($row['precioTotal'], 2) ?> €</td>
                                <td><?= htmlspecialchars($row['nombreEtapa']) ?></td>
                                <td>
                                    <a href="/php/Dashboard_usuario/editar_productoVM.php?idVM=<?= htmlspecialchars($row['idVM'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-warning <?= ($tienePrivilegioEditar == '0') ? 'disabled' : '' ?>">Editar</a>
                                    <a href="?delete_vm=<?= htmlspecialchars($row['idVM'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-danger <?= ($tienePrivilegioEliminar == '0') ? 'disabled' : '' ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay productos que mostrar</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="product-table-container">
            <h2>Bases de Datos</h2>
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Nombre</th>
                        <th scope="col">IP Privada</th>
                        <th scope="col">IP Pública</th>
                        <th scope="col">VLAN</th>
                        <th scope="col">Precio</th>
                        <th scope="col">Etapa</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_bd->num_rows > 0): ?>
                        <?php while ($row = $result_bd->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nombreProducto']) ?></td>
                                <td><?= htmlspecialchars($row['ipPrivada']) ?></td>
                                <td><?= htmlspecialchars($row['ipPublica']) ?></td>
                                <td><?= htmlspecialchars($row['nombreVLAN']) ?></td>
                                <td><?= number_format($row['precioTotal'], 2) ?> €</td>
                                <td><?= htmlspecialchars($row['nombreEtapa']) ?></td>
                                <td>
                                    <a href="/php/Dashboard_usuario/editar_productoBD.php?idBD=<?= htmlspecialchars($row['idBD'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-warning <?= ($tienePrivilegioEditar == '0') ? 'disabled' : '' ?>">Editar</a>
                                    <a href="?delete_bd=<?= htmlspecialchars($row['idBD'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-danger <?= ($tienePrivilegioEliminar == '0') ? 'disabled' : '' ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?')">Eliminar</a>
                                </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay productos que mostrar</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="product-table-container">
            <h2>Entornos de Desarrollo</h2>
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Nombre</th>
                        <th scope="col">IP Privada</th>
                        <th scope="col">IP Pública</th>
                        <th scope="col">VLAN</th>
                        <th scope="col">Precio</th>
                        <th scope="col">Etapa</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_ed->num_rows > 0): ?>
                        <?php while ($row = $result_ed->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nombreProducto']) ?></td>
                                <td><?= htmlspecialchars($row['ipPrivada']) ?></td>
                                <td><?= htmlspecialchars($row['ipPublica']) ?></td>
                                <td><?= htmlspecialchars($row['nombreVLAN']) ?></td>
                                <td><?= number_format($row['precioTotal'], 2) ?> €</td>
                                <td><?= htmlspecialchars($row['nombreEtapa']) ?></td>
                                <td>
                                    <a href="/php/Dashboard_usuario/editar_productoED.php?idED=<?= htmlspecialchars($row['idED'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-warning <?= ($tienePrivilegioEditar == '0') ? 'disabled' : '' ?>">Editar</a>
                                    <a href="?delete_ed=<?= htmlspecialchars($row['idED'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-danger <?= ($tienePrivilegioEliminar == '0') ? 'disabled' : '' ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay productos que mostrar</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="product-table-container">
            <h2>Almacenamiento en la Nube</h2>
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Nombre</th>
                        <th scope="col">IP Privada</th>
                        <th scope="col">IP Pública</th>
                        <th scope="col">VLAN</th>
                        <th scope="col">Precio</th>
                        <th scope="col">Etapa</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_ac->num_rows > 0): ?>
                        <?php while ($row = $result_ac->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nombreProducto']) ?></td>
                                <td><?= htmlspecialchars($row['ipPrivada']) ?></td>
                                <td><?= htmlspecialchars($row['ipPublica']) ?></td>
                                <td><?= htmlspecialchars($row['nombreVLAN']) ?></td>
                                <td><?= number_format($row['precioTotal'], 2) ?> €</td>
                                <td><?= htmlspecialchars($row['nombreEtapa']) ?></td>
                                <td>
                                    <a href="/php/Dashboard_usuario/editar_productoAC.php?idAC=<?= htmlspecialchars($row['idAC'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-warning <?= ($tienePrivilegioEditar == '0') ? 'disabled' : '' ?>">Editar</a>
                                    <a href="?delete_ac=<?= htmlspecialchars($row['idAC'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-danger <?= ($tienePrivilegioEliminar == '0') ? 'disabled' : '' ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay productos que mostrar</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>© 2024 LosIluminados. Todos los derechos reservados.</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6SZXku3GK1zFk9F9KqpgJHFlvQeCIGvljKvC77x4P2Bl" crossorigin="anonymous"></script>
</body>
</html>