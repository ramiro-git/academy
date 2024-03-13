<?php
include("../config/sesion.php");

include("../config/functions.php");

isAdmin($user_id, $conn);

if (empty($_SESSION['admin_id'])) {
    header("Location: ../index.php"); // Redirigir al usuario al index.php
    exit(); // Detener la ejecución del script después de la redirección
}

// Inicializar variables de búsqueda
$search = isset($_GET['search']) ? $_GET['search'] : '';
$gender = isset($_GET['gender']) ? $_GET['gender'] : '';
$role = isset($_GET['role']) ? $_GET['role'] : '';
$active = isset($_GET['active']) ? $_GET['active'] : '';

/// Construir la URL base
$base_url = 'usuarios.php';
$query_string = '';

// Agregar parámetros de búsqueda a la URL base
if (!empty($search)) $query_string .= '&search=' . urlencode($search);

if (!empty($gender)) $query_string .= '&gender=' . urlencode($gender);

if (!empty($role)) $query_string .= '&role=' . urlencode($role);

if (!empty($active)) $query_string .= '&active=' . urlencode($active);

// Consulta base
$query = "SELECT * FROM `usuarios` WHERE 1";

// Aplicar filtros
if (!empty($search)) $query .= " AND (name LIKE '%$search%' OR email LIKE '%$search%')";

if (!empty($gender)) $query .= " AND gender = '$gender'";

if ($role === '0' || $role === '1') $query .= " AND role = '$role'";

if ($active === '0' || $active === '1') $query .= " AND active = '$active'";

// Ejecutar consulta
$get_users = $conn->prepare($query);
$get_users->execute();

// Obtener resultados
$resultados = $get_users->fetchAll();

// Paginación
$num_alumnos_por_pagina = 3;
$total_resultados = count($resultados);
$total_paginas = ceil($total_resultados / $num_alumnos_por_pagina);
$pagina_actual = isset($_GET['pagina']) ? min(max(1, $_GET['pagina']), $total_paginas) : 1;
$inicio = ($pagina_actual - 1) * $num_alumnos_por_pagina;
$resultados_paginados = array_slice($resultados, $inicio, $num_alumnos_por_pagina);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumnos</title>
    <link rel="stylesheet" href="../build/css/app.css" />
</head>

<body>
    <h2>Buscar Alumnos</h2>

    <div class="bloques">
        <form class="formulario" method="GET" action="<?= $base_url ?>">
            <input class="formulario__input" type="text" name="search" placeholder="Ingrese el nombre o email" value="<?= htmlspecialchars($search) ?>">
            <select class="formulario__select" name="gender">
                <option value="">Todos</option>
                <option value="male" <?= ($gender == 'male') ? 'selected' : '' ?>>Hombre</option>
                <option value="female" <?= ($gender == 'female') ? 'selected' : '' ?>>Mujer</option>
                <option value="ratherNotSay" <?= ($gender == 'ratherNotSay') ? 'selected' : '' ?>>Prefiere no decir</option>
            </select>
            <select class="formulario__select" name="role">
                <option value="">Todos</option>
                <option value="0" <?= ($role == '0') ? 'selected' : '' ?>>Usuario</option>
                <option value="1" <?= ($role == '1') ? 'selected' : '' ?>>Administrador</option>
            </select>
            <select class="formulario__select" name="active">
                <option value="">Todos</option>
                <option value="1" <?= ($active == '1') ? 'selected' : '' ?>>Sí</option>
                <option value="0" <?= ($active == '0') ? 'selected' : '' ?>>No</option>
            </select>
            <button class="formulario__submit" type="submit">Buscar</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Género</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados_paginados as $usuario) { ?>
                    <tr>
                        <td><?= $usuario['name']; ?></td>
                        <td><?= $usuario['surname']; ?></td>
                        <td><?= ($usuario['gender'] === 'male') ? 'Hombre' : (($usuario['gender'] === 'female') ? 'Mujer' : 'Prefiere no decir'); ?></td>
                        <td><?= $usuario['email']; ?></td>
                        <td><?= $usuario['role'] ? "Administrador" : "Usuario"; ?></td>
                        <td><?= $usuario['active'] ? "Sí" : "No"; ?></td>
                        <td><a href="updateUser?id=<?= $usuario["id"] ?>">Editar</a> <a href="deleteUser?id=<?= $usuario['id'] ?>">Eliminar</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php if ($total_paginas > 1) { ?>
            <div class='pagination'>
                <?php if ($pagina_actual > 1) { ?>
                    <a href='<?= "$base_url?pagina=1$query_string" ?>'> Primera </a>
                    <a href='<?= "$base_url?pagina=" . ($pagina_actual - 1) . "$query_string&role=$role&active=$active" ?>'> Anterior </a>
                <?php } ?>
                <?php for ($i = 1; $i <= $total_paginas; $i++) { ?>
                    <a <?= ($pagina_actual == $i) ? 'class="active"' : '' ?> href='<?= "$base_url?pagina=$i$query_string&role=$role&active=$active" ?>'><?= $i ?></a>
                <?php } ?>
                <?php if ($pagina_actual < $total_paginas) { ?>
                    <a href='<?= "$base_url?pagina=" . ($pagina_actual + 1) . "$query_string&role=$role&active=$active" ?>'> Siguiente </a>
                    <a href='<?= "$base_url?pagina=$total_paginas$query_string&role=$role&active=$active" ?>'> Última </a>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</body>

</html>