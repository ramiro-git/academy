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
$base_url = 'tareas.php';
$query_string = '';

// Agregar parámetros de búsqueda a la URL base
if (!empty($search)) $query_string .= '&search=' . urlencode($search);

// Consulta base para seleccionar todos las inscripciones
$query = "SELECT * FROM tareas WHERE 1";

// Aplicar filtros si se han proporcionado
if (!empty($search)) $query .= " AND (materias.nombre LIKE '%$search%' OR cursos.title LIKE '%$search%')";

// Ejecutar la consulta para obtener las inscripciones
$get_inscriptions = $conn->prepare($query);
$get_inscriptions->execute();

// Obtener todos los resultados de la consulta
$resultados = $get_inscriptions->fetchAll();

// Definir la paginación
$num_inscripciones_por_pagina = 3; // Número de inscripciones por página
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
    $descripcion = (isset($_POST["descripcion"])) ? $_POST["descripcion"] : NULL;
    $fecha_entrega = (isset($_POST["fecha_entrega"])) ? $_POST["fecha_entrega"] : NULL;
    $materia_id = (isset($_POST["materia"])) ? $_POST["materia"] : NULL;

    // Validar los campos del formulario
    if (empty($nombre)) $errors['nombre'] = "El nombre es obligatorio.";
    if (empty($descripcion)) $errors['descripcion'] = "La descripcion es obligatoria.";
    if (empty($fecha_entrega)) $errors['fecha_entrega'] = "La fecha de entrega es obligatoria.";
    if (empty($materia_id)) $errors[] = "La materia es obligatoria.";

    if (strlen($nombre) > 255) $errors['nombre'] = "El nombre es demasiado largo.";
    if (strlen($descripcion) > 255) $errors['descripcion'] = "La descripcion es demasiado larga.";

    // Si no hay errores, insertar el nuevo curso en la base de datos
    if (empty($errors)) {
        $sql = "INSERT INTO tareas (nombre, descripcion, fecha_entrega, materia_id) VALUES (:nombre, :descripcion, :fecha_entrega, :materia_id)";

        $result = $conn->prepare($sql);

        // Ejecutar la consulta preparada con los datos del formulario
        $result = $result->execute(array(
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':fecha_entrega' => $fecha_entrega,
            ':materia_id' => $materia_id,
        ));

        // Redirigir de vuelta a la página de cursos después de agregar el curso
        header("Location: tareas.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tareas</title>
    <link rel="stylesheet" href="../build/css/app.css" />
</head>

<body>
    <h2>Tareas</h2>

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
                    <th>Fecha de Entrega</th>
                    <th>Fecha</th>
                    <th>Materia</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados_paginados as $tareas) { ?>
                    <tr>
                        <td><?= $tareas['nombre']; ?></td>
                        <td><?= $tareas['fecha_entrega']; ?></td>
                        <td><?= $tareas['fecha_emision']; ?></td>
                        <td>
                            <?php
                            // Consultar la base de datos para obtener el nombre de la materia
                            $query_materia = "SELECT nombre FROM materias WHERE id = ?";
                            $get_materia = $conn->prepare($query_materia);
                            $get_materia->execute([$tareas['materia_id']]);
                            $materia = $get_materia->fetchColumn();
                            echo $materia;
                            ?>
                        </td>
                        <td><a href="updateInstructor?id=<?= $tareas["id"] ?>">Editar</a> <a href="deleteInstructor?id=<?= $tareas['id'] ?>">Eliminar</a></td>
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

    <h2>Añadir Tareas</h2>

    <div class="bloques">
        <form action="tareas.php" method="POST" class="formulario">
            <?php if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error . "<br/>"; ?>

            <label for="nombre" class="formulario__label">Nombre:</label>
            <input class="formulario__input" type="text" name="nombre" id="nombre" required value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" />

            <label for="descripcion" class="formulario__label">Descripción:</label>
            <input class="formulario__input" type="text" name="descripcion" id="descripcion" required value="<?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?>" />

            <label for="fecha_entrega" class="formulario__label">Fecha de Entrega:</label>
            <input class="formulario__input" type="date" name="fecha_entrega" id="fecha_entrega" required value="<?php echo isset($_POST['fecha_entrega']) ? htmlspecialchars($_POST['fecha_entrega']) : ''; ?>" />

            <label for="materia" class="formulario__label">Materia:</label>
            <select class="formulario__input" name="materia" id="materia">
                <option value="" disabled>Seleccione una materia</option>
                <?php
                // Consultar la base de datos para obtener todos los cursos
                $query_cursos = "SELECT * FROM materias";
                $get_cursos = $conn->prepare($query_cursos);
                $get_cursos->execute();
                $materias = $get_cursos->fetchAll();

                // Iterar sobre los resultados y construir las opciones del select
                foreach ($materias as $materia) echo "<option value='" . $materia['id'] . "'>" . $materia['nombre'] . "</option>";
                ?>
            </select>

            <input class="formulario__submit" type="submit" value="Añadir">
        </form>
    </div>
</body>

</html>