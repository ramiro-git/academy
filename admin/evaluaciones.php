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
$date = isset($_GET['date']) ? $_GET['date'] : '';

// Construir la URL base para la paginación
$base_url = 'evaluaciones.php';
$query_string = '';

// Agregar parámetros de búsqueda a la URL base
if (!empty($search)) $query_string .= '&search=' . urlencode($search);
if (!empty($date)) $query_string .= '&date=' . urlencode($date);

// Consulta base para seleccionar todos las inscripciones
$query = "SELECT * FROM evaluaciones WHERE 1";

// Aplicar filtros si se han proporcionado
if (!empty($search)) {
    // Consultar la base de datos para obtener el ID de la materia
    $query_materia_id = "SELECT id FROM materias WHERE nombre LIKE '%$search%'";
    $get_materia_id = $conn->prepare($query_materia_id);
    $get_materia_id->execute();
    $materia_id = $get_materia_id->fetchColumn();

    // Agregar el filtro a la consulta principal
    $query .= " AND (nombre_archivo LIKE '%$search%' OR materia_id = '$materia_id')";
}

if (!empty($date)) $query .= " AND fecha = '$date'";

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
    $hora_inicio = (isset($_POST["hora_inicio"])) ? $_POST["hora_inicio"] : NULL;
    $hora_fin = (isset($_POST["hora_fin"])) ? $_POST["hora_fin"] : NULL;
    $intentos = (isset($_POST["intentos"])) ? $_POST["intentos"] : NULL;
    $ponderacion = (isset($_POST["ponderacion"])) ? $_POST["ponderacion"] : NULL;
    $tipo = (isset($_POST["tipo"])) ? $_POST["tipo"] : NULL;
    $estado = (isset($_POST["estado"])) ? $_POST["estado"] : NULL;
    $instrucciones = (isset($_POST["instrucciones"])) ? $_POST["instrucciones"] : NULL;
    $materia_id = (isset($_POST["materia"])) ? $_POST["materia"] : NULL;

    // Validar los campos del formulario
    if (empty($nombre)) $errors['nombre'] = "El nombre es obligatorio.";
    if (empty($descripcion)) $errors['descripcion'] = "La descripcion de lectura es obligatoria.";
    if (empty($materia_id)) $errors[] = "La materia es obligatoria.";

    if (strlen($nombre) > 255) $errors['nombre'] = "El nombre es demasiado largo.";
    if (strlen($tipo) > 255) $errors['tipo'] = "El tipo de evaluación es demasiado largo.";

    // Si no hay errores, procede a subir el archivo y guardar en la base de datos
    if (empty($errors)) {
        // Convertir las horas de inicio y fin en objetos DateTime
        $hora_inicio_dt = new DateTime($hora_inicio);
        $hora_fin_dt = new DateTime($hora_fin);

        // Calcular la diferencia entre las horas de inicio y fin
        $duracion_estimada = $hora_inicio_dt->diff($hora_fin_dt)->format('%H:%I:%S');

        $sql = "INSERT INTO evaluaciones (nombre, descripcion, hora_inicio, hora_fin, intentos_permitidos, ponderacion, duracion_estimada, tipo_evaluacion, estado, instrucciones, materia_id) VALUES (:nombre, :descripcion, :hora_inicio, :hora_fin, :intentos_permitidos, :ponderacion, :duracion_estimada, :tipo_evaluacion, :estado, :instrucciones, :materia_id)";

        $result = $conn->prepare($sql);

        // Ejecutar la consulta preparada con los datos del formulario
        $result = $result->execute(array(
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':hora_inicio' => $hora_inicio,
            ':hora_fin' => $hora_fin,
            ':intentos_permitidos' => $intentos,
            ':ponderacion' => $ponderacion,
            ':duracion_estimada' => $duracion_estimada,
            ':tipo_evaluacion' => $tipo,
            ':estado' => $estado,
            ':instrucciones' => $instrucciones,
            ':materia_id' => $materia_id
        ));

        // Redirigir de vuelta a la página de cursos después de agregar el curso
        header("Location: evaluaciones.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluaciones</title>
    <link rel="stylesheet" href="../build/css/app.css" />
</head>

<body>
    <h2>Evaluaciones</h2>

    <div class="bloques">
        <form class="formulario" method="GET" action="<?= $base_url ?>">
            <label for="search" class="formulario__label">Nombre o Materia</label>
            <input class="formulario__input" type="text" name="search" placeholder="Ingrese el nombre o materia" value="<?= htmlspecialchars($search) ?>">

            <label for="date" class="formulario__label">Fecha:</label>
            <input class="formulario__input" type="date" name="date" id="date" value="<?= htmlspecialchars($date) ?>">

            <button class="formulario__submit" type="submit">Buscar</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Hora de Inicio</th>
                    <th>Hora de Fin</th>
                    <th>Intentos</th>
                    <th>Ponderación</th>
                    <th>Duración</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Instrucciones</th>
                    <th>Materia</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados_paginados as $evaluaciones) { ?>
                    <tr>
                        <td><?= $evaluaciones['nombre']; ?></td>
                        <td><?= $evaluaciones['hora_inicio']; ?></td>
                        <td><?= $evaluaciones['hora_fin']; ?></td>
                        <td><?= $evaluaciones['intentos_permitidos']; ?></td>
                        <td><?= $evaluaciones['ponderacion']; ?></td>
                        <td><?= $evaluaciones['duracion_estimada']; ?></td>
                        <td><?= ucfirst($evaluaciones['tipo_evaluacion']); ?></td>
                        <td><?= ucfirst($evaluaciones['estado']); ?></td>
                        <td><?= $evaluaciones['instrucciones']; ?></td>
                        <td>
                            <?php
                            // Consultar la base de datos para obtener el nombre de la materia
                            $query_materia = "SELECT nombre FROM materias WHERE id = ?";
                            $get_materia = $conn->prepare($query_materia);
                            $get_materia->execute([$evaluaciones['materia_id']]);
                            $materia = $get_materia->fetchColumn();
                            echo $materia;
                            ?>
                        </td>
                        <td><a href="updateInstructor?id=<?= $materiales["id"] ?>">Editar</a> <a href="deleteInstructor?id=<?= $materiales['id'] ?>">Eliminar</a></td>
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

    <h2>Añadir Evaluaciones</h2>

    <div class="bloques">
        <form action="evaluaciones.php" method="POST" class="formulario" enctype="multipart/form-data">
            <?php if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error . "<br/>"; ?>

            <label for="nombre" class="formulario__label">Nombre:</label>
            <input class="formulario__input" type="text" name="nombre" id="nombre" value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" />

            <label for="descripcion" class="formulario__label">Descripción:</label>
            <input class="formulario__input" type="text" name="descripcion" id="descripcion" value="<?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?>" />

            <label for="hora_inicio" class="formulario__label">Hora de Inicio:</label>
            <input class="formulario__input" type="time" name="hora_inicio" id="hora_inicio" value="<?php echo isset($_POST['hora_inicio']) ? htmlspecialchars($_POST['hora_inicio']) : ''; ?>" />

            <label for="hora_fin" class="formulario__label">Hora de Fin:</label>
            <input class="formulario__input" type="time" name="hora_fin" id="hora_fin" value="<?php echo isset($_POST['hora_fin']) ? htmlspecialchars($_POST['hora_fin']) : ''; ?>" />

            <label for="intentos" class="formulario__label">Intentos Permitidos:</label>
            <input class="formulario__input" type="number" name="intentos" id="intentos" value="<?php echo isset($_POST['intentos']) ? htmlspecialchars($_POST['intentos']) : ''; ?>" />

            <label for="ponderacion" class="formulario__label">Ponderación:</label>
            <input class="formulario__input" type="number" name="ponderacion" id="ponderacion" value="<?php echo isset($_POST['ponderacion']) ? htmlspecialchars($_POST['ponderacion']) : ''; ?>" />

            <label for="tipo" class="formulario__label">Tipo de Evaluación:</label>
            <select class="formulario__input" name="tipo" id="tipo">
                <option value="" disabled>Seleccione un tipo de evaluación</option>
                <option value="examen">Examen</option>
                <option value="cuestionario">Cuestionario</option>
                <option value="proyecto">Proyecto</option>
                <option value="otro">Otro</option>
            </select>

            <label for="estado" class="formulario__label">Estado de Evaluación:</label>
            <select class="formulario__input" name="estado" id="estado">
                <option value="" disabled>Seleccione un estado de evaluación</option>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
                <option value="programado">Programado</option>
            </select>

            <label for="instrucciones" class="formulario__label">Instrucciones:</label>
            <input class="formulario__input" type="text" name="instrucciones" id="instrucciones" value="<?php echo isset($_POST['instrucciones']) ? htmlspecialchars($_POST['instrucciones']) : ''; ?>" />

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