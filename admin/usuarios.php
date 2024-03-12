<?php
include("../config/sesion.php");

include("../config/functions.php");

isAdmin($user_id, $conn);

if (empty($_SESSION['admin_id'])) {
    header("Location: ../index.php"); // Redirigir al usuario al index.php
    exit(); // Detener la ejecución del script después de la redirección
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumnos</title>
</head>

<body>
    <!-- Acá iría la sección de gráficos -->

    <?php $get_cantidad_alumnos = $conn->query("SELECT COUNT(*) FROM `usuarios` WHERE role = 0");
    $cantAlumnosRegistrados = $get_cantidad_alumnos->fetchColumn();

    $lastMonthDate = date('Y-m-d', strtotime('-1 month'));

    $get_cantidad_alumnos_nuevos = $conn->query("SELECT COUNT(*) FROM `usuarios` WHERE role = 0 AND date >= '$lastMonthDate'");
    $cantAlumnosRegistradosNuevos = $get_cantidad_alumnos_nuevos->fetchColumn();
    ?>

    <h2>Cantidad de alumnos registrados: <?= $cantAlumnosRegistrados ?></h2>
    <h2>Cantidad de alumnos registrados el último mes: <?= $cantAlumnosRegistradosNuevos ?></h2>

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
            <?php
            $num_alumnos_por_pagina = 2;
            $pagina_actual = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
            $inicio = ($pagina_actual - 1) * $num_alumnos_por_pagina;

            $get_users = $conn->prepare("SELECT * FROM `usuarios` ORDER BY id DESC LIMIT $inicio, $num_alumnos_por_pagina");
            $get_users->execute();

            if ($get_users->rowCount() > 0) {
                while ($fetch_users = $get_users->fetch(PDO::FETCH_ASSOC)) { ?>
                    <tr>
                        <td><?= $fetch_users['name']; ?></td>
                        <td><?= $fetch_users['surname']; ?></td>
                        <td><?php
                            if ($fetch_users['gender'] === 'male') {
                                echo 'Hombre';
                            } elseif ($fetch_users['gender'] === 'female') {
                                echo 'Mujer';
                            } elseif ($fetch_users['gender'] === 'other' || $fetch_users['gender'] === 'ratherNotSay') {
                                echo 'Prefiere no decir';
                            } ?></td>
                        <td><?= $fetch_users['email']; ?></td>
                        <td><?= $fetch_users['role'] ? "Administrador" : "Usuario"; ?></td>
                        <td><?= $fetch_users['active'] ? "Sí" : "No"; ?></td>
                        <td><a href="updateUser?id=<?= $fetch_users["id"] ?>">Editar</a> <a href="deleteUser?id=<?= $fetch_users['id'] ?>">Eliminar</a></td>
                <?php }
            } else echo '<tr><td colspan="5">Aún no hay alumnos</td></tr>';
                ?>
        </tbody>
    </table>

    <!-- Controles de paginación -->
    <?php
    if ($num_alumnos_por_pagina != 0) $total_paginas = ceil($cantAlumnosRegistrados / $num_alumnos_por_pagina);
    ?>

    <div>
        <?php if ($pagina_actual > 1) : ?>
            <a href="?pagina=<?= $pagina_actual - 1 ?>">Anterior</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_paginas; $i++) : ?>
            <?php if ($i == $pagina_actual) : ?>
                <strong><?= $i ?></strong>
            <?php else : ?>
                <a href="?pagina=<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($pagina_actual < $total_paginas) : ?>
            <a href="?pagina=<?= $pagina_actual + 1 ?>">Siguiente</a>
        <?php endif; ?>
    </div>
</body>

</html>