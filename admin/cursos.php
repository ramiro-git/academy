<?php
include("../config/sesion.php");

include("../config/functions.php");

isAdmin($user_id, $conn);

if (empty($_SESSION['admin_id'])) {
    header("Location: ../index.php"); // Redirigir al usuario al index.php
    exit(); // Detener la ejecución del script después de la redirección
}

// Inicializar variables de búsqueda
$search = isset($_GET['search']) ? $_GET['search'] : '';
$instructor = isset($_GET['instructor']) ? $_GET['instructor'] : '';
$begin = isset($_GET['begin']) ? $_GET['begin'] : '';
$duration = isset($_GET['duration']) ? $_GET['duration'] : '';

/// Construir la URL base
$base_url = 'cursos.php';
$query_string = '';

// Agregar parámetros de búsqueda a la URL base
if (!empty($search)) $query_string .= '&search=' . urlencode($search);
if (!empty($instructor)) $query_string .= '&instructor=' . urlencode($instructor);
if (!empty($begin)) $query_string .= '&role=' . urlencode($begin);
if (!empty($duration)) $query_string .= '&duration=' . urlencode($duration);

// Consulta base
$query = "SELECT * FROM `cursos` WHERE 1";

// Aplicar filtros
if (!empty($search)) $query .= " AND (title LIKE '%$search%' OR description LIKE '%$search%')";
if (!empty($instructor)) $query .= " AND instructor = '$instructor'";
if (!empty($begin)) $query .= " AND begin = '$begin'";
if (!empty($duration)) $query .= " AND duration = '$duration'";

// Ejecutar consulta
$get_courses = $conn->prepare($query);
$get_courses->execute();

// Obtener resultados
$resultados = $get_courses->fetchAll();

// Paginación
$num_cursos_por_pagina = 3;
$total_resultados = count($resultados);
$total_paginas = ceil($total_resultados / $num_cursos_por_pagina);
$pagina_actual = isset($_GET['pagina']) ? min(max(1, $_GET['pagina']), $total_paginas) : 1;
$inicio = ($pagina_actual - 1) * $num_cursos_por_pagina;
$resultados_paginados = array_slice($resultados, $inicio, $num_cursos_por_pagina);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array();

    $titulo = (isset($_POST["title"])) ? $_POST["title"] : NULL;
    $descripcion = (isset($_POST["description"])) ? $_POST["description"] : NULL;
    $instructor = (isset($_POST["instructor"])) ? $_POST["instructor"] : NULL;
    $empieza = (isset($_POST["begin"])) ? $_POST["begin"] : NULL;
    $duracion = (isset($_POST["duration"])) ? $_POST["duration"] : NULL;

    if (empty($titulo)) $errors['title'] = "El título es obligatorio.";
    if (empty($descripcion)) $errors['description'] = "La descripción es obligatoria.";
    if (empty($instructor)) $errors['instructor'] = "El instructor es obligatorio.";
    if (empty($empieza)) $errors['begin'] = "El comienzo es obligatorio.";
    if (empty($duracion)) $errors['duration'] = "La duración es obligatoria.";

    if (strlen($titulo) > 255) $errors['title'] = "El título es demasiado largo.";
    if (strlen($descripcion) > 255) $errors['description'] = "La descripción es demasiado larga.";
    if (strlen($instructor) > 255) $errors['instructor'] = "El instructor es demasiado largo.";

    // Verificar si el instructor seleccionado es válido
    $sql_instructor = $conn->prepare("SELECT id FROM `instructores` WHERE id = ?");
    $sql_instructor->execute([$instructor]);

    if ($sql_instructor->rowCount() == 0) $errors['instructor'] = "El instructor seleccionado no es válido.";

    // Verificar si la fecha seleccionada es válida y no es menor que la fecha actual
    $today = date("Y-m-d");

    if ($empieza < $today) $errors['begin'] = "La fecha de inicio no puede ser anterior a la fecha actual.";

    if ($duracion > 100) $errors['duration'] = "La duración no puede ser mayor que 100 semanas.";

    if (empty($errors)) {
        $sql = "INSERT INTO cursos (title, description, instructor, begin, duration) VALUES (:title, :description, :instructor, :begin, :duration)";

        $result = $conn->prepare($sql);

        $result = $result->execute(array(
            ':title' => $titulo,
            ':description' => $descripcion,
            ':instructor' => $instructor,
            ':begin' => $empieza,
            ':duration' => $duracion,
        ));

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
            <input class="formulario__input" type="text" name="search" placeholder="Ingrese el título o la descripción" value="<?= htmlspecialchars($search) ?>">
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
            <input class="formulario__input" type="date" name="begin" id="begin" value="<?= htmlspecialchars($begin) ?>">
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