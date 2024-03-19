<?php
include("config/sesion.php");

if (empty($user_id)) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['evaluation_id'])) {
    $evaluation_id = $_GET['evaluation_id'];

    // Obtener detalles de la evaluación
    $select_evaluation = $conn->prepare("SELECT * FROM evaluaciones WHERE id = ?");
    $select_evaluation->execute([$evaluation_id]);

    if ($select_evaluation->rowCount() > 0) {
        $evaluation_details = $select_evaluation->fetch(PDO::FETCH_ASSOC);
        $evaluation_name = $evaluation_details['nombre'];
        // Puedes obtener más detalles de la evaluación aquí si lo necesitas
    } else {
        // Si no se encuentra la evaluación, redirigir a alguna página apropiada
        header("Location: index.php");
        exit();
    }

    // Obtener respuestas del alumno para esta evaluación
    $select_responses = $conn->prepare("SELECT respuestas_alumnos.*, preguntas.pregunta
                                        FROM respuestas_alumnos
                                        JOIN preguntas ON respuestas_alumnos.pregunta_id = preguntas.id
                                        WHERE respuestas_alumnos.evaluacion_id = ? AND respuestas_alumnos.user_id = ?");
    $select_responses->execute([$evaluation_id, $user_id]);
    $responses = $select_responses->fetchAll();
} else {
    // Si no se ha proporcionado un ID de evaluación válido, redirigir a alguna página apropiada
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluación Completada</title>
</head>

<body>
    <?php require("components/header.php"); ?>

    <h1>Evaluación Completada: <?= $evaluation_name ?></h1>
    <h2>Respuestas:</h2>
    <ul>
        <?php foreach ($responses as $response) : ?>
            <li>
                <strong>Pregunta:</strong> <?= $response['pregunta'] ?><br>
                <strong>Respuesta:</strong> <?= $response['respuesta'] ?>
            </li>
        <?php endforeach; ?>
    </ul>
    
    <?php require("components/footer.php"); ?>
</body>

</html>