<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
</head>

<body>
    <?php if ($_SESSION['user_id'] !== '') echo "<a href='logout.php'>Cerrar Sesión</a>"; ?>
</body>

</html>