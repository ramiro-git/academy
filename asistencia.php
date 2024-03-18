<?php
include("config/sesion.php");

if ($user_id == '') {
    header("Location: login.php");
    exit();
}

$select_teacher = $conn->prepare("SELECT materias.*, cursos.title AS nombre_curso FROM `materias` INNER JOIN `cursos` ON materias.curso_id = cursos.id WHERE materias.instructor = ?");
$select_teacher->execute([$user_id]);

if ($select_teacher->rowCount() > 0) {
    $subjects = $select_teacher->fetchAll(PDO::FETCH_ASSOC);
} else {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the current date and time
    $fecha = date("Y-m-d");
    $hora = date("H:i:s");

    foreach ($subjects as $subject) {
        if (isset($_POST['attendance'][$subject['id']])) {
            // Get enrolled students for the current subject
            $select_enrolled_students = $conn->prepare("SELECT usuarios.* FROM `inscripciones_materias` INNER JOIN `usuarios` ON inscripciones_materias.user_id = usuarios.id WHERE inscripciones_materias.materia_id = ?");
            $select_enrolled_students->execute([$subject['id']]);
            $enrolled_students = $select_enrolled_students->fetchAll(PDO::FETCH_ASSOC);

            // Initialize arrays to store present and absent student IDs for this subject
            $presentes = [];
            $ausentes = [];

            // Loop through enrolled students to determine their attendance status for this subject
            foreach ($enrolled_students as $student) {
                // Check if the student is marked present for this subject
                if (isset($_POST['attendance'][$subject['id']][$student['id']])) {
                    $presentes[] = $student['id'];
                } else {
                    $ausentes[] = $student['id'];
                }
            }

            // Convert arrays to comma-separated strings
            $presentes_str = implode(",", $presentes);
            $ausentes_str = implode(",", $ausentes);

            // Insert attendance record for this subject into the database
            $insert_attendance = $conn->prepare("INSERT INTO `asistencia` (`materia_id`, `curso_id`, `fecha`, `hora`, `presentes`, `ausentes`) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_attendance->execute([$subject['id'], $subject['curso_id'], $fecha, $hora, $presentes_str, $ausentes_str]);
        }
    }

    // Redirect back to the attendance page
    header("Location: asistencia.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencia</title>
    <link rel="stylesheet" href="build/css/app.css" />
</head>

<body>
    <?php require("components/header.php"); ?>

    <?php foreach ($subjects as $subject) : ?>
        <div class="subject-attendance">
            <h1 class="subject-title">Asistencia - Materia: <?php echo $subject['nombre']; ?></h1>
            <h2 class="subtitle">Curso: <?php echo $subject['nombre_curso']; ?></h2>

            <!-- Verificar si hay estudiantes inscritos para esta materia -->
            <?php
            $select_enrolled_students = $conn->prepare("SELECT usuarios.* FROM `inscripciones_materias` INNER JOIN `usuarios` ON inscripciones_materias.user_id = usuarios.id WHERE inscripciones_materias.materia_id = ?");
            $select_enrolled_students->execute([$subject['id']]);
            $enrolled_students = $select_enrolled_students->fetchAll(PDO::FETCH_ASSOC);

            if (empty($enrolled_students)) {
                echo "<p class='empty-message'>Aún no hay ningún estudiante inscrito en esta materia.</p>";
            } else {
            ?>
                <!-- Formulario para marcar asistencia para esta materia -->
                <form method="post" class="attendance-form">
                    <h2 class="subtitle">Marcar Asistencia:</h2>
                    <ul class="student-list">
                        <!-- Recorrer estudiantes inscritos para esta materia -->
                        <?php
                        foreach ($enrolled_students as $student) : ?>
                            <li>
                                <label class="checkbox-label">
                                    <span class="student-name"><?php echo $student['name']; ?></span>
                                    <input type="checkbox" name="attendance[<?php echo $subject['id']; ?>][<?php echo $student['id']; ?>]" class="attendance-checkbox">
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="submit" class="attendance-submit">Guardar Asistencia</button>
                </form>
            <?php } ?>
        </div>
    <?php endforeach; ?>
</body>

</html>