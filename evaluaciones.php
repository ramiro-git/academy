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
            evaluaciones.id,
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

// Verificar si el usuario ya ha completado alguna evaluación
$select_completed_evaluations = $conn->prepare("SELECT DISTINCT evaluacion_id FROM respuestas_alumnos WHERE user_id = ?");
$select_completed_evaluations->execute([$user_id]);
$completed_evaluations = $select_completed_evaluations->fetchAll(PDO::FETCH_COLUMN);
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
                <!-- Resto del código... -->
                <?php if ($evaluation['estado_evaluacion'] === 'Habilitado' && !in_array($evaluation['id'], $completed_evaluations)) : ?>
                    <form method="post" action="evaluacion.php">
                        <input type="hidden" name="evaluation_id" value="<?= $evaluation['id'] ?>">
                        <button type="submit">Iniciar evaluación</button>
                    </form>
                <?php endif; ?>
            </li>
            <hr>
        <?php endforeach; ?>
    </ul>

    <script>
        function iniciarEvaluacion(id) {
            window.location.href = 'evaluacion.php?id=' + id; // Reemplaza 'evaluacion.php' por la URL real de tu página de evaluación.
        }
    </script>
</body>

</html>