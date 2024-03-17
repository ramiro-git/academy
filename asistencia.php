<?php
include("config/sesion.php");

if ($user_id == '') {
    header("Location: login.php");
    exit();
}

$select_teacher = $conn->prepare("SELECT * FROM `materias` WHERE instructor = ?");
$select_teacher->execute([$user_id]);

if ($select_teacher->rowCount() > 0) {
    $subjects = $select_teacher->fetchAll(PDO::FETCH_ASSOC);
} else {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Iterate through each subject for attendance marking
    foreach ($subjects as $subject) {
        // Get the current date and time
        $fecha = date("Y-m-d");
        $hora = date("H:i:s");

        // Get enrolled students for the current subject
        $select_enrolled_students = $conn->prepare("SELECT usuarios.* FROM `inscripciones_materias` INNER JOIN `usuarios` ON inscripciones_materias.user_id = usuarios.id WHERE inscripciones_materias.materia_id = ?");
        $select_enrolled_students->execute([$subject['id']]);
        $enrolled_students = $select_enrolled_students->fetchAll(PDO::FETCH_ASSOC);

        // Initialize arrays to store present and absent student IDs
        $presentes = [];
        $ausentes = [];

        // Loop through enrolled students to determine their attendance status
        foreach ($enrolled_students as $student) {
            // Check if the student is marked present
            if (isset($_POST['attendance'][$student['id']])) {
                $presentes[] = $student['id'];
            } else {
                $ausentes[] = $student['id'];
            }
        }

        // Convert arrays to comma-separated strings
        $presentes_str = implode(",", $presentes);
        $ausentes_str = implode(",", $ausentes);

        // Query to fetch course details based on subject ID
        $select_course = $conn->prepare("SELECT * FROM `cursos` WHERE id = ?");
        $select_course->execute([$subject['curso_id']]);
        $course = $select_course->fetch(PDO::FETCH_ASSOC);

        // Insert attendance record into the database
        $insert_attendance = $conn->prepare("INSERT INTO `asistencia` (`materia_id`, `curso_id`, `fecha`, `hora`, `presentes`, `ausentes`) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_attendance->execute([$subject['id'], $course['id'], $fecha, $hora, $presentes_str, $ausentes_str]);

        // Redirect back to the attendance page for this subject
        header("Location: asistencia.php?id={$subject['id']}");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencia</title>
</head>

<body>
    <?php foreach ($subjects as $subject) : ?>
        <h1>Asistencia - Materia: <?php echo $subject['nombre']; ?></h1>
        <h2>Alumnos Inscritos:</h2>
        <!-- Form to mark attendance -->
        <form method="post">
            <h2>Marcar Asistencia:</h2>
            <ul>
                <!-- Loop through enrolled students for the current subject -->
                <?php
                $select_enrolled_students = $conn->prepare("SELECT usuarios.* FROM `inscripciones_materias` INNER JOIN `usuarios` ON inscripciones_materias.user_id = usuarios.id WHERE inscripciones_materias.materia_id = ?");
                $select_enrolled_students->execute([$subject['id']]);
                $enrolled_students = $select_enrolled_students->fetchAll(PDO::FETCH_ASSOC);

                foreach ($enrolled_students as $student) : ?>
                    <li>
                        <label>
                            <input type="checkbox" name="attendance[<?php echo $student['id']; ?>]">
                            <?php echo $student['name']; ?>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
            <button type="submit">Guardar Asistencia</button>
        </form>
    <?php endforeach; ?>
</body>

</html>