<?php

include("../config/sesion.php");

include("../config/functions.php");

isAdmin($user_id, $conn);

if (empty($_SESSION['admin_id'])) {
    header("Location: ../index.php"); // Redirigir al usuario al index.php
    exit(); // Detener la ejecución del script después de la redirección
}

$select_profile = $conn->prepare("SELECT * FROM `usuarios` WHERE id = ?");
$select_profile->execute([$user_id]);

if ($select_profile->rowCount() > 0) $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../build/css/app.css" />
</head>

<body>
    <h1>Dashboard</h1>
    <h2>Bienvenido, <?php echo $fetch_profile['name']; ?></h2>

    <a href="usuarios.php">Usuarios</a>
    <a href="instructores.php">Instructores</a>
    <a href="inscripciones.php">Inscripciones</a>
    <a href="cursos.php">Cursos</a>
    <a href="materias.php">Materias</a>
    <a href="materiales.php">Materiales</a>
    <a href="tareas.php">Tareas</a>
    <a href="evaluaciones.php">Evaluaciones</a>
</body>

</html>