<?php include("config/sesion.php"); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
</head>

<body>
    <h1>Inicio</h1>

    <?php if ($user_id != '') echo "<a href='update.php?id=$user_id'>Actualizar</a><br /><a href='logout.php'>Cerrar Sesión</a>" ?>

    <?php if ($user_id == '') echo "<a href='login.php'>Login</a><br /><a href='registro.php'>Registro</a>"; ?>

    <?php
    $select_teacher = $conn->prepare("SELECT * FROM `materias` WHERE instructor = ?");
    $select_teacher->execute([$user_id]);

    if ($select_teacher->rowCount() > 0) {
        $fetch_teacher = $select_teacher->fetch(PDO::FETCH_ASSOC);

        echo "<br /><a href='asistencia.php?id=" . $fetch_teacher['id'] . "'>Asistencia</a>";
    }
    ?>
</body>

</html>