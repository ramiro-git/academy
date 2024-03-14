<?php

include("../config/sesion.php");

include("../config/functions.php");

isAdmin($user_id, $conn);

if (empty($_SESSION['admin_id'])) {
    header("Location: ../index.php"); // Redirigir al usuario al index.php
    exit(); // Detener la ejecución del script después de la redirección
}

// Inicializar variables de búsqueda con valores de la URL o cadenas vacías
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Construir la URL base para la paginación
$base_url = 'inscripciones.php';
$query_string = '';

// Agregar parámetros de búsqueda a la URL base
if (!empty($search)) $query_string .= '&search=' . urlencode($search);

// Consulta base para seleccionar todos las inscripciones
$query = "SELECT inscripciones.*, usuarios.name AS user_name, usuarios.surname AS user_lastname, cursos.title AS course_title FROM `inscripciones` 
JOIN usuarios ON inscripciones.user_id = usuarios.id
JOIN cursos ON inscripciones.course_id = cursos.id
WHERE 1";

// Aplicar filtros si se han proporcionado
if (!empty($search)) $query .= " AND (usuarios.name LIKE '%$search%' OR usuarios.surname LIKE '%$search%' OR cursos.title LIKE '%$search%')";

// Ejecutar la consulta para obtener las inscripciones
$get_inscriptions = $conn->prepare($query);
$get_inscriptions->execute();

// Obtener todos los resultados de la consulta
$resultados = $get_inscriptions->fetchAll();

// Definir la paginación
$num_inscripciones_por_pagina = 5; // Número de inscripciones por página
$total_resultados = count($resultados); // Total de resultados
$total_paginas = ceil($total_resultados / $num_inscripciones_por_pagina); // Total de páginas
$pagina_actual = isset($_GET['pagina']) ? min(max(1, $_GET['pagina']), $total_paginas) : 1; // Página actual
$inicio = ($pagina_actual - 1) * $num_inscripciones_por_pagina; // Índice de inicio para la paginación
$resultados_paginados = array_slice($resultados, $inicio, $num_inscripciones_por_pagina); // Recortar resultados para la página actual

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
    <h2>Inscriptos</h2>

    <div class="bloques">
        <form class="formulario" method="GET" action="<?= $base_url ?>">
            <label for="search" class="formulario__label">Usuario o Curso:</label>
            <input class="formulario__input" type="text" name="search" placeholder="Ingrese el nombre del usuario o el título del curso" value="<?= htmlspecialchars($search) ?>">

            <button class="formulario__submit" type="submit">Buscar</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Nombre del usuario</th>
                    <th>Apellido del usuario</th>
                    <th>Curso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados_paginados as $inscripcion) { ?>
                    <tr>
                        <td><?= $inscripcion['user_name']; ?></td>
                        <td><?= $inscripcion['user_lastname']; ?></td>
                        <td><?= $inscripcion['course_title']; ?></td>
                        <td><a href="updateInscripcion?id=<?= $inscripcion["id"] ?>">Editar</a> <a href="deleteInscripcion?id=<?= $inscripcion['id'] ?>">Eliminar</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php if ($total_paginas > 1) { ?>
            <div class='pagination'>
                <?php if ($pagina_actual > 1) { ?>
                    <a href='<?= "$base_url?pagina=1$query_string" ?>'> Primera </a>
                    <a href='<?= "$base_url?pagina=" . ($pagina_actual - 1) . "$query_string" ?>'> Anterior </a>
                <?php } ?>
                <?php for ($i = 1; $i <= $total_paginas; $i++) { ?>
                    <a <?= ($pagina_actual == $i) ? 'class="active"' : '' ?> href='<?= "$base_url?pagina=$i$query_string" ?>'><?= $i ?></a>
                <?php } ?>
                <?php if ($pagina_actual < $total_paginas) { ?>
                    <a href='<?= "$base_url?pagina=" . ($pagina_actual + 1) . "$query_string" ?>'> Siguiente </a>
                    <a href='<?= "$base_url?pagina=$total_paginas$query_string" ?>'> Última </a>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

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