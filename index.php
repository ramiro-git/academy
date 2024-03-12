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
</body>

</html>