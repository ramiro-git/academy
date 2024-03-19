<?php
include("config/sesion.php");

// Verificar si el usuario está autenticado
if ($user_id == '') {
    header("Location: login.php");
    exit();
}

// Consultar las asistencias del usuario actual donde estuvo presente
$select_attendance = $conn->prepare("SELECT * FROM `asistencia` WHERE FIND_IN_SET(?, presentes) OR FIND_IN_SET(?, ausentes)");
$select_attendance->execute([$user_id, $user_id]);
$attendances = $select_attendance->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portafolio</title>
    <link rel="stylesheet" href="build/css/app.css" />
</head>

<body>
    <?php require("components/header.php"); ?>

    <h2>Asistencias</h2>

    <div class="asistencia-englobador">
        <div class="asistencia-margenes">
            <?php foreach ($attendances as $attendance) : ?>
                <div class="asistencia-alumno">
                    <?php $formatted_date = date("d/m/Y", strtotime($attendance['fecha'])); ?>

                    <div class="asistencia-fecha"><?= $formatted_date; ?></div>

                    <?php
                    $status = (strpos($attendance['presentes'], $user_id) !== false) ? 'Presente' : 'Ausente';

                    $status_class = ($status == 'Presente') ? 'presente' : 'ausente';
                    ?>

                    <div class="asistencia-cuadrado <?= $status_class; ?>"><?= $status; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>