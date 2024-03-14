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
$base_url = 'materias.php';
$query_string = '';

// Agregar parámetros de búsqueda a la URL base
if (!empty($search)) $query_string .= '&search=' . urlencode($search);

// Consulta base para seleccionar todos las inscripciones
$query = "SELECT * FROM materias WHERE 1";

// Aplicar filtros si se han proporcionado
if (!empty($search)) $query .= " AND (nombre LIKE '%$search%' OR apellido LIKE '%$search%' OR email LIKE '%$search%' OR dni LIKE '%$search%' OR especialidad LIKE '%$search%')";

// Ejecutar la consulta para obtener las inscripciones
$get_inscriptions = $conn->prepare($query);
$get_inscriptions->execute();

// Obtener todos los resultados de la consulta
$resultados = $get_inscriptions->fetchAll();

// Definir la paginación
$num_inscripciones_por_pagina = 1; // Número de inscripciones por página
$total_resultados = count($resultados); // Total de resultados
$total_paginas = ceil($total_resultados / $num_inscripciones_por_pagina); // Total de páginas
$pagina_actual = isset($_GET['pagina']) ? min(max(1, $_GET['pagina']), $total_paginas) : 1; // Página actual
$inicio = ($pagina_actual - 1) * $num_inscripciones_por_pagina; // Índice de inicio para la paginación
$resultados_paginados = array_slice($resultados, $inicio, $num_inscripciones_por_pagina); // Recortar resultados para la página actual

// Procesar el formulario de agregar cursos si se ha enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array(); // Inicializar un array para almacenar errores

    // Recoger los datos del formulario
    $nombre = (isset($_POST["nombre"])) ? $_POST["nombre"] : NULL;
    $curso = (isset($_POST["curso"])) ? $_POST["curso"] : NULL;

    // Validar los campos del formulario
    if (empty($nombre)) $errors['nombre'] = "El nombre es obligatorio.";
    if (empty($curso)) $errors['curso'] = "El curso es obligatorio.";

    if (strlen($nombre) > 255) $errors['nombre'] = "El nombre es demasiado largo.";
    if (strlen($curso) > 255) $errors['curso'] = "El curso es demasiado largo.";

    // Si no hay errores, insertar el nuevo curso en la base de datos
    if (empty($errors)) {
        $sql = "INSERT INTO materias (nombre, curso_id) VALUES (:nombre, :curso_id)";

        $result = $conn->prepare($sql);

        // Ejecutar la consulta preparada con los datos del formulario
        $result = $result->execute(array(
            ':nombre' => $nombre,
            ':curso_id' => $curso, // Cambiar ':curso' a ':curso_id'
        ));

        // Redirigir de vuelta a la página de cursos después de agregar el curso
        header("Location: materias.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materias</title>
    <link rel="stylesheet" href="../build/css/app.css" />
</head>

<body>
    <h2>Materias</h2>

    <div class="bloques">
        <form class="formulario" method="GET" action="<?= $base_url ?>">
            <label for="search" class="formulario__label">Nombre o Curso:</label>
            <input class="formulario__input" type="text" name="search" placeholder="Ingrese el nombre o curso" value="<?= htmlspecialchars($search) ?>">

            <button class="formulario__submit" type="submit">Buscar</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Curso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados_paginados as $instructor) { ?>
                    <tr>
                        <td><?= $instructor['nombre']; ?></td>
                        <td><?= $instructor['apellido']; ?></td>
                        <td><a href="updateInstructor?id=<?= $instructor["id"] ?>">Editar</a> <a href="deleteInstructor?id=<?= $instructor['id'] ?>">Eliminar</a></td>
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

    <h2>Añadir Materias</h2>

    <div class="bloques">
        <form action="materias.php" method="POST" class="formulario">
            <?php if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error . "<br/>"; ?>

            <label for="nombre" class="formulario__label">Nombre:</label>
            <input class="formulario__input" type="text" name="nombre" id="nombre" required value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" />

            <label for="curso" class="formulario__label">Curso:</label>
            <select class="formulario__input" name="curso" id="curso">
                <option value="" disabled>Seleccione un curso</option>
                <?php
                // Consultar la base de datos para obtener todos los cursos
                $query_cursos = "SELECT * FROM cursos";
                $get_cursos = $conn->prepare($query_cursos);
                $get_cursos->execute();
                $cursos = $get_cursos->fetchAll();

                // Iterar sobre los resultados y construir las opciones del select
                foreach ($cursos as $curso) {
                    echo "<option value='" . $curso['id'] . "'>" . $curso['title'] . "</option>";
                }
                ?>
            </select>

            <input class="formulario__submit" type="submit" value="Añadir">
        </form>
    </div>
</body>

</html>