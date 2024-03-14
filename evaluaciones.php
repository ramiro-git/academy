<?php

include("config/sesion.php");

if (empty($user_id)) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id']) && $_GET['id'] != $user_id) {
    header("Location: index.php");
    exit();
}

$select_profile = $conn->prepare("SELECT * FROM `usuarios` WHERE id = ?");
$select_profile->execute([$user_id]);

if ($select_profile->rowCount() > 0) {
    $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
    $page_title = $fetch_profile['name'];
} else header("Location: index.php");

$query = "SELECT 
            evaluaciones.nombre,
            evaluaciones.intentos_permitidos,
            CASE 
                WHEN evaluaciones.estado = 'activo' AND CURTIME() BETWEEN evaluaciones.hora_inicio AND evaluaciones.hora_fin THEN 'Habilitado'
                ELSE 'Deshabilitado'
            END AS estado_evaluacion
          FROM 
            evaluaciones
          INNER JOIN materias ON evaluaciones.materia_id = materias.id
          INNER JOIN inscripciones ON materias.curso_id = inscripciones.course_id
          WHERE 
            inscripciones.user_id = :user_id";

$get_evaluations = $conn->prepare($query);
$get_evaluations->bindParam(':user_id', $user_id);
$get_evaluations->execute();

$evaluations = $get_evaluations->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluaciones - <?= $fetch_profile['name']; ?></title>
</head>

<body>
    <ul>
        <?php foreach ($evaluations as $evaluation) : ?>
            <li>
                <strong>Nombre:</strong> <?= $evaluation['nombre'] ?><br>
                <strong>Intentos Permitidos:</strong> <?= $evaluation['intentos_permitidos'] ?><br>
                <strong>Estado:</strong> <?= $evaluation['estado_evaluacion'] ?>
            </li>
            <hr>
        <?php endforeach; ?>
    </ul>
</body>

</html>