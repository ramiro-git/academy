<?php

include("../config/sesion.php");

include("../config/functions.php");

isAdmin($user_id, $conn);

if (empty($_SESSION['admin_id'])) {
    header("Location: ../index.php"); // Redirigir al usuario al index.php
    exit(); // Detener la ejecución del script después de la redirección
}

// Procesar el formulario de agregar cursos si se ha enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array(); // Inicializar un array para almacenar errores

    // Recoger los datos del formulario
    $user_id = (isset($_POST["user"])) ? $_POST["user"] : NULL;
    $course_id = (isset($_POST["course"])) ? $_POST["course"] : NULL;

    // Validar los campos del formulario
    if (empty($user_id)) $errors['user'] = "Por favor, selecciona un usuario.";
    if (empty($course_id)) $errors['course'] = "Por favor, selecciona un curso.";

    // Verificar sí el usuario existe
    $userExistsQuery = "SELECT id FROM usuarios WHERE id = ?";

    $userExistsStmt = $conn->prepare($userExistsQuery);
    $userExistsStmt->execute([$user_id]);

    if ($userExistsStmt->rowCount() == 0) $errors['user'] = "El usuario seleccionado no es válido.";

    // Verificar sí el curso existe
    $courseExistsQuery = "SELECT id FROM cursos WHERE id = ?";

    $courseExistsStmt = $conn->prepare($courseExistsQuery);
    $courseExistsStmt->execute([$course_id]);

    if ($courseExistsStmt->rowCount() == 0) $errors['course'] = "El curso seleccionado no es válido.";

    // Verificar sí el usuario ya está en dicho curso
    $ExistsQuery = "SELECT id FROM inscripciones WHERE user_id = ? AND course_id = ?";

    $ExistsStmt = $conn->prepare($ExistsQuery);
    $ExistsStmt->execute([$user_id, $course_id]);

    if ($ExistsStmt->rowCount() > 0) $errors['user'] = "El usuario ya está inscrito en este curso.";

    // Si no hay errores, insertar el nuevo curso en la base de datos
    if (empty($errors)) {
        $sql = "INSERT INTO inscripciones (user_id, course_id) VALUES (:user_id, :course_id)";

        $result = $conn->prepare($sql);

        // Ejecutar la consulta preparada con los datos del formulario
        $result = $result->execute(array(
            ':user_id' => $user_id,
            ':course_id' => $course_id,
        ));

        // Redirigir de vuelta a la página de cursos después de agregar el curso
        header("Location: inscripciones.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripciones</title>
    <link rel="stylesheet" href="../build/css/app.css" />
</head>

<body>
    <h2>Inscribir</h2>

    <div class="bloques">
        <form action="inscripciones.php" method="POST" class="formulario">
            <?php if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error . "<br/>"; ?>

            <label for="user" class="formulario__label">Selecciona un alumno:</label>
            <select name="user" id="user" class="formulario__select">
                <?php
                $sql = $conn->prepare("SELECT id, name FROM usuarios WHERE role = 0");
                $sql->execute();

                if ($sql->rowCount() > 0) {
                    while ($user = $sql->fetch(PDO::FETCH_ASSOC)) : ?>
                        <option value="<?= $user['id']; ?>"><?= $user['name']; ?></option>
                <?php endwhile;
                } ?>
            </select>

            <label for="course" class="formulario__label">Selecciona un curso:</label>
            <select name="course" id="course" class="formulario__select">
                <?php
                $sql = $conn->prepare("SELECT id, title FROM cursos");
                $sql->execute();

                if ($sql->rowCount() > 0) {
                    while ($course = $sql->fetch(PDO::FETCH_ASSOC)) : ?>
                        <option value="<?= $course['id']; ?>"><?= $course['title']; ?></option>
                <?php endwhile;
                } ?>
            </select>

            <input class="formulario__submit" type="submit" value="Inscribir">
        </form>
    </div>
</body>

</html>