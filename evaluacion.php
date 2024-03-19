<?php
include("config/sesion.php");

if (empty($user_id)) {
    header("Location: index.php");
    exit();
}

$token = bin2hex(random_bytes(32));
$_SESSION['evaluation_token'] = $token;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['evaluation_id'])) {
    $evaluation_id = $_POST['evaluation_id'];
    $_SESSION['evaluation_id'] = $evaluation_id;

    // Obtener detalles de la evaluación
    $select_evaluation = $conn->prepare("SELECT * FROM evaluaciones WHERE id = ?");
    $select_evaluation->execute([$evaluation_id]);

    if ($select_evaluation->rowCount() > 0) {
        $evaluation_details = $select_evaluation->fetch(PDO::FETCH_ASSOC);
        $evaluation_name = $evaluation_details['nombre'];
        $description = $evaluation_details['descripcion'];
        $weight = $evaluation_details['ponderacion'];
        $duration = $evaluation_details['duracion_estimada'];
        $instructions = $evaluation_details['instrucciones'];

        // Formatear la fecha y hora de finalización para JavaScript
        $endDateTime = strtotime($evaluation_details['hora_fin']);
        $endDateTimeFormatted = date('Y-m-d H:i:s', $endDateTime); // Formato compatible con JavaScript

        // Obtener preguntas de la evaluación
        $select_questions = $conn->prepare("SELECT * FROM preguntas WHERE evaluacion_id = ?");
        $select_questions->execute([$evaluation_id]);
        $questions = $select_questions->fetchAll();

        // Insertar automáticamente en la tabla respuestas_alumnos
        $insert_response = $conn->prepare("INSERT INTO respuestas_alumnos (evaluacion_id, user_id) VALUES (?, ?)");
        $insert_response->execute([$evaluation_id, $user_id]);
    } else {
        // Si no se encuentra la evaluación, redirigir a la página de evaluaciones
        header("Location: evaluaciones.php");
        exit();
    }
} else {
    // Si no se ha establecido el ID de la evaluación en el formulario, redirigir al usuario nuevamente a la página de evaluaciones
    header("Location: evaluaciones.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluación - <?= $evaluation_name ?></title>
</head>

<body>
    <?php require("components/header.php"); ?>

    <h1><?= $evaluation_name ?></h1>
    <p><strong>Descripción:</strong> <?= $description ?></p>
    <p><strong>Ponderación:</strong> <?= $weight ?>%</p>
    <p><strong>Duración estimada:</strong> <?= $duration ?></p>
    <p><strong>Instrucciones:</strong> <?= $instructions ?></p>

    <div id="timer"></div>

    <h2>Preguntas:</h2>
    <form action="procesar_respuestas.php" method="post" id="evaluationForm">
        <input type="hidden" name="evaluation_id" value="<?= $evaluation_id ?>">
        <input type="hidden" name="security_token" value="<?= $token ?>">
        <input type="hidden" name="evaluation_id" value="<?= $evaluation_id ?>">
        <ol>
            <?php foreach ($questions as $question) : ?>
                <li>
                    <p><?= $question['pregunta'] ?></p>
                    <?php if ($question['tipo_pregunta'] === 'opcion_multiple') : ?>
                        <?php if ($question['opciones'] !== null) : ?>
                            <?php $options = explode(',', $question['opciones']); ?>
                            <?php foreach ($options as $option) : ?>
                                <label>
                                    <input type="radio" name="answer[<?= $question['id'] ?>]" value="<?= $option ?>">
                                    <?= $option ?>
                                </label><br>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php else : ?>
                        <input type="text" name="answer[<?= $question['id'] ?>]">
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
        <button type="submit" name="formRespuestas">Enviar respuestas</button>
    </form>

    <?php require("components/footer.php"); ?>

    <script>
        // Función para actualizar el temporizador
        function updateTimer() {
            // Fecha y hora de finalización (obtenida desde PHP)
            var endDateTime = new Date("<?= $endDateTimeFormatted ?>");

            // Fecha y hora actual
            var now = new Date();

            // Diferencia de tiempo en milisegundos
            var timeDiff = endDateTime - now;

            // Convertir la diferencia de tiempo a horas, minutos y segundos
            var hours = Math.floor(timeDiff / (1000 * 60 * 60));
            var minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);

            // Formatear el temporizador
            var timerText = hours + "h " + minutes + "m " + seconds + "s";

            // Actualizar el elemento HTML con el temporizador
            document.getElementById("timer").innerHTML = "Tiempo restante: " + timerText;

            // Si el tiempo se ha agotado, enviar automáticamente el formulario
            if (timeDiff <= 0) {
                document.getElementById("evaluationForm").submit();
            } else {
                // Actualizar el temporizador cada segundo
                setTimeout(updateTimer, 1000);
            }
        }

        // Llamar a la función para iniciar el temporizador
        updateTimer();
    </script>
</body>

</html>