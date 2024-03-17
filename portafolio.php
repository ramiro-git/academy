<?php
include("config/sesion.php");

// Verificar si el usuario está autenticado
if ($user_id == '') {
    header("Location: login.php");
    exit();
}

// Consultar las asistencias del usuario actual donde estuvo presente
$select_present_attendance = $conn->prepare("SELECT * FROM `asistencia` WHERE FIND_IN_SET(?, presentes)");
$select_present_attendance->execute([$user_id]);
$present_attendances = $select_present_attendance->fetchAll(PDO::FETCH_ASSOC);

// Consultar las asistencias del usuario actual donde estuvo ausente
$select_absent_attendance = $conn->prepare("SELECT * FROM `asistencia` WHERE FIND_IN_SET(?, ausentes)");
$select_absent_attendance->execute([$user_id]);
$absent_attendances = $select_absent_attendance->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portafolio</title>
</head>

<body>
    <h1>Portafolio de Asistencias</h1>

    <h2>Tus Asistencias Presentes:</h2>
    <ul>
        <?php foreach ($present_attendances as $attendance) : ?>
            <li>
                <strong>Fecha:</strong> <?php echo $attendance['fecha']; ?><br>
                <strong>Hora:</strong> <?php echo $attendance['hora']; ?><br>
                <!-- Puedes mostrar más detalles según tus necesidades -->
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Tus Asistencias Ausentes:</h2>
    <ul>
        <?php foreach ($absent_attendances as $attendance) : ?>
            <li>
                <strong>Fecha:</strong> <?php echo $attendance['fecha']; ?><br>
                <strong>Hora:</strong> <?php echo $attendance['hora']; ?><br>
                <!-- Puedes mostrar más detalles según tus necesidades -->
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Puedes agregar más contenido según tus necesidades -->

</body>

</html>