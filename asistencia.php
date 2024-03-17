<?php
include("config/sesion.php");

// Check if user is logged in
if ($user_id == '') {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Check if ID parameter is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect to some error page or back to homepage if ID is not provided
    header("Location: index.php");
    exit();
}

// Assuming $conn is your database connection object
$id = $_GET['id'];

// Query to fetch subject details based on curso_id
$select_subject = $conn->prepare("SELECT * FROM `materias` WHERE id = ?");
$select_subject->execute([$id]);
$subject = $select_subject->fetch(PDO::FETCH_ASSOC);

// Query to fetch enrolled students based on materia_id
$select_enrolled_students = $conn->prepare("SELECT usuarios.* FROM `inscripciones_materias` INNER JOIN `usuarios` ON inscripciones_materias.user_id = usuarios.id WHERE inscripciones_materias.materia_id = ?");
$select_enrolled_students->execute([$subject['id']]);
$enrolled_students = $select_enrolled_students->fetchAll(PDO::FETCH_ASSOC);

if (!$subject || $subject['instructor'] != $user_id) {
    header("Location: index.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the current date and time
    $fecha = date("Y-m-d");
    $hora = date("H:i:s");

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

    // Redirect to some page after marking attendance
    header("Location: asistencia.php?id=$id"); // Redirect back to the attendance page for this subject
    exit();
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
    <h1>Asistencia - Materia: <?php echo $subject['nombre']; ?></h1>
    <h2>Alumnos Inscritos:</h2>
    <form method="post">
        <h2>Marcar Asistencia:</h2>
        <ul>
            <?php foreach ($enrolled_students as $student) : ?>
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
</body>

</html>