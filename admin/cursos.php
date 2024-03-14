<?php
// Incluir archivos de configuración y funciones
include("../config/sesion.php");

include("../config/functions.php");

// Verificar si el usuario actual es un administrador
isAdmin($user_id, $conn);

// Si el ID de administrador no está en la sesión, redirigir al usuario a la página de inicio
if (empty($_SESSION['admin_id'])) {
    header("Location: ../index.php"); // Redirigir al usuario al index.php
    exit(); // Detener la ejecución del script después de la redirección
}

// Inicializar variables de búsqueda con valores de la URL o cadenas vacías
$search = isset($_GET['search']) ? $_GET['search'] : '';
$instructor = isset($_GET['instructor']) ? $_GET['instructor'] : '';
$begin = isset($_GET['begin']) ? $_GET['begin'] : '';
$duration = isset($_GET['duration']) ? $_GET['duration'] : '';

// Construir la URL base para la paginación
$base_url = 'cursos.php';
$query_string = '';

// Agregar parámetros de búsqueda a la URL base
if (!empty($search)) $query_string .= '&search=' . urlencode($search);
if (!empty($instructor)) $query_string .= '&instructor=' . urlencode($instructor);
if (!empty($begin)) $query_string .= '&role=' . urlencode($begin);
if (!empty($duration)) $query_string .= '&duration=' . urlencode($duration);

// Consulta base para seleccionar todos los cursos
$query = "SELECT * FROM `cursos` WHERE 1";

// Aplicar filtros si se han proporcionado
if (!empty($search)) $query .= " AND (title LIKE '%$search%' OR description LIKE '%$search%')";
if (!empty($instructor)) $query .= " AND instructor = '$instructor'";
if (!empty($begin)) $query .= " AND begin = '$begin'";
if (!empty($duration)) $query .= " AND duration = '$duration'";

// Ejecutar la consulta para obtener los cursos
$get_courses = $conn->prepare($query);
$get_courses->execute();

// Obtener todos los resultados de la consulta
$resultados = $get_courses->fetchAll();

// Definir la paginación
$num_cursos_por_pagina = 3; // Número de cursos por página
$total_resultados = count($resultados); // Total de resultados
$total_paginas = ceil($total_resultados / $num_cursos_por_pagina); // Total de páginas
$pagina_actual = isset($_GET['pagina']) ? min(max(1, $_GET['pagina']), $total_paginas) : 1; // Página actual
$inicio = ($pagina_actual - 1) * $num_cursos_por_pagina; // Índice de inicio para la paginación
$resultados_paginados = array_slice($resultados, $inicio, $num_cursos_por_pagina); // Recortar resultados para la página actual

// Procesar el formulario de agregar cursos si se ha enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array(); // Inicializar un array para almacenar errores

    // Recoger los datos del formulario
    $titulo = (isset($_POST["title"])) ? $_POST["title"] : NULL;
    $descripcion = (isset($_POST["description"])) ? $_POST["description"] : NULL;
    $instructor = (isset($_POST["instructor"])) ? $_POST["instructor"] : NULL;
    $empieza = (isset($_POST["begin"])) ? $_POST["begin"] : NULL;
    $duracion = (isset($_POST["duration"])) ? $_POST["duration"] : NULL;

    // Validar los campos del formulario
    if (empty($titulo)) $errors['title'] = "El título es obligatorio.";
    if (empty($descripcion)) $errors['description'] = "La descripción es obligatoria.";
    if (empty($instructor)) $errors['instructor'] = "El instructor es obligatorio.";
    if (empty($empieza)) $errors['begin'] = "El comienzo es obligatorio.";
    if (empty($duracion)) $errors['duration'] = "La duración es obligatoria.";

    if (strlen($titulo) > 255) $errors['title'] = "El título es demasiado largo.";
    if (strlen($descripcion) > 255) $errors['description'] = "La descripción es demasiado larga.";
    if (strlen($instructor) > 255) $errors['instructor'] = "El instructor es demasiado largo.";

    // Verificar si el instructor seleccionado es válido consultando la base de datos
    $sql_instructor = $conn->prepare("SELECT id FROM `instructores` WHERE id = ?");
    $sql_instructor->execute([$instructor]);

    // Si el instructor seleccionado no existe, agregar un error
    if ($sql_instructor->rowCount() == 0) $errors['instructor'] = "El instructor seleccionado no es válido.";

    // Verificar si la fecha seleccionada es válida y no es menor que la fecha actual
    $today = date("Y-m-d");

    if ($empieza < $today) $errors['begin'] = "La fecha de inicio no puede ser anterior a la fecha actual.";

    if ($duracion > 100) $errors['duration'] = "La duración no puede ser mayor que 100 semanas.";

    // Si no hay errores, insertar el nuevo curso en la base de datos
    if (empty($errors)) {
        $sql = "INSERT INTO cursos (title, description, instructor, begin, duration) VALUES (:title, :description, :instructor, :begin, :duration)";

        $result = $conn->prepare($sql);

        // Ejecutar la consulta preparada con los datos del formulario
        $result = $result->execute(array(
            ':title' => $titulo,
            ':description' => $descripcion,
            ':instructor' => $instructor,
            ':begin' => $empieza,
            ':duration' => $duracion,
        ));

        // Redirigir de vuelta a la página de cursos después de agregar el curso
        header("Location: cursos.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos</title>
    <link rel="stylesheet" href="../build/css/app.css" />
</head>

<body>
    <h2>Cursos</h2>
    <div class="bloques">
        <form class="formulario" method="GET" action="<?= $base_url ?>">
            <label for="search" class="formulario__label">Título o Descripción:</label>
            <input class="formulario__input" type="text" name="search" placeholder="Ingrese el título o la descripción" value="<?= htmlspecialchars($search) ?>">

            <label for="instructor" class="formulario__label">Instructor:</label>
            <select class="formulario__select" name="instructor">
                <option value="">Todos</option>
                <?php
                $sql_instructores = $conn->query("SELECT id, nombre FROM instructores");
                while ($row = $sql_instructores->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='" . $row["id"] . "'";
                    if ($instructor == $row["id"]) echo " selected";
                    echo ">" . $row["nombre"] . "</option>";
                }
                ?>
            </select>

            <label for="begin" class="formulario__label">Comienzo:</label>
            <input class="formulario__input" type="date" name="begin" id="begin" value="<?= htmlspecialchars($begin) ?>">

            <label for="duration" class="formulario__label">Duración:</label>
            <input class="formulario__input" type="number" name="duration" id="duration" value="<?= htmlspecialchars($duration) ?>">

            <button class="formulario__submit" type="submit">Buscar</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Instructor</th>
                    <th>Comienzo</th>
                    <th>Duración</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados_paginados as $curso) { ?>
                    <tr>
                        <td><?= $curso['title']; ?></td>
                        <td><?= $curso['description']; ?></td>
                        <td><?= $curso['instructor']; ?></td>
                        <td><?= $curso['begin']; ?></td>
                        <td><?= $curso['duration']; ?></td>
                        <td><a href="updateCourse?id=<?= $curso["id"] ?>">Editar</a> <a href="deleteCourse?id=<?= $curso['id'] ?>">Eliminar</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php if ($total_paginas > 1) { ?>
            <div class='pagination'>
                <?php if ($pagina_actual > 1) { ?>
                    <a href='<?= "$base_url?pagina=1$query_string" ?>'> Primera </a>
                    <a href='<?= "$base_url?pagina=" . ($pagina_actual - 1) . "$query_string&role=$role&active=$active" ?>'> Anterior </a>
                <?php } ?>
                <?php for ($i = 1; $i <= $total_paginas; $i++) { ?>
                    <a <?= ($pagina_actual == $i) ? 'class="active"' : '' ?> href='<?= "$base_url?pagina=$i$query_string&role=$role&active=$active" ?>'><?= $i ?></a>
                <?php } ?>
                <?php if ($pagina_actual < $total_paginas) { ?>
                    <a href='<?= "$base_url?pagina=" . ($pagina_actual + 1) . "$query_string&role=$role&active=$active" ?>'> Siguiente </a>
                    <a href='<?= "$base_url?pagina=$total_paginas$query_string&role=$role&active=$active" ?>'> Última </a>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <h2>Añadir Cursos</h2>

    <div class="bloques">
        <form action="cursos.php" method="POST" class="formulario">
            <?php if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error . "<br/>"; ?>

            <label for="title" class="formulario__label">Título:</label>
            <input class="formulario__input" type="text" name="title" id="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" />

            <label for="description" class="formulario__label">Descripción:</label>
            <input class="formulario__input" type="text" name="description" id="description" value="<?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?>" />

            <?php $sql = $conn->prepare("SELECT id, nombre FROM instructores");
            $sql->execute();
            if ($sql->rowCount() > 0) {
                echo "<label for='instructor' class='formulario__label'>Instructor:</label> <select class='formulario__select' name='instructor'>";
                while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='" . $row["id"] . "'>" . $row["nombre"] . "</option>";
                }
                echo "</select>";
            } else {
                echo "No hay instructores disponibles.";
            }
            ?>

            <label for="begin" class="formulario__label">Comienza:</label>
            <input class="formulario__input" type="datetime-local" name="begin" id="begin" value="<?php echo isset($_POST['begin']) ? htmlspecialchars($_POST['begin']) : ''; ?>" />

            <label for="duration" class="formulario__label">Duración (en semanas):</label>
            <input class="formulario__input" type="number" name="duration" id="duration" value="<?php echo isset($_POST['duration']) ? htmlspecialchars($_POST['duration']) : ''; ?>" />

            <input class="formulario__submit" type="submit" value="Añadir">
        </form>
    </div>
</body>

</html>