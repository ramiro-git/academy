<?php
include("config/sesion.php");

if (empty($user_id)) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['evaluation_id'])) {
    $evaluation_id = $_POST['evaluation_id'];
    $_SESSION['evaluation_id'] = $evaluation_id;
    $security_token = $_POST['security_token'];

    if (!empty($_SESSION['evaluation_token']) && $_SESSION['evaluation_token'] === $security_token) {
        // Obtener preguntas de la evaluación
        $select_questions = $conn->prepare("SELECT * FROM preguntas WHERE evaluacion_id = ?");
        $select_questions->execute([$evaluation_id]);
        $questions = $select_questions->fetchAll();

        // Preparar la consulta para insertar las respuestas del usuario
        $insert_response = $conn->prepare("INSERT INTO respuestas_alumnos (evaluacion_id, pregunta_id, user_id, respuesta) VALUES (?, ?, ?, ?)");

        // Iterar sobre las preguntas y guardar las respuestas del usuario
        foreach ($questions as $question) {
            $question_id = $question['id'];
            // Obtener la respuesta del formulario
            $answer = $_POST['answer'][$question_id];
            // Insertar la respuesta del usuario en la base de datos
            $insert_response->execute([$evaluation_id, $question_id, $user_id, $answer]);
        }

        // Redirigir al usuario a alguna página después de procesar las respuestas
        header("Location: evaluacion_completada.php?evaluation_id=$evaluation_id");
        exit();

        unset($_SESSION['evaluation_token']);

        echo '<script>localStorage.removeItem("evaluationAnswers");</script>';
    } else {
        header("Location: evaluaciones.php");
        exit();
    }
} else {
    // Si no se ha enviado el formulario correctamente, redirigir al usuario nuevamente a la página de evaluaciones
    header("Location: evaluaciones.php");
    exit();
}