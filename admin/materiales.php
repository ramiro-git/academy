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
$base_url = 'materiales.php';
$query_string = '';

// Agregar parámetros de búsqueda a la URL base
if (!empty($search)) $query_string .= '&search=' . urlencode($search);
if (!empty($date)) $query_string .= '&date=' . urlencode($date);

// Consulta base para seleccionar todos las inscripciones
$query = "SELECT * FROM materiales WHERE 1";

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
    $nombre_archivo = (isset($_POST["nombre_archivo"])) ? $_POST["nombre_archivo"] : NULL;
    $tipo_lectura = (isset($_POST["tipo"])) ? $_POST["tipo"] : NULL;
    $archivo_temporal = $_FILES['archivo']['tmp_name'];
    $archivo_nombre = $_FILES['archivo']['name'];
    $materia_id = (isset($_POST["materia"])) ? $_POST["materia"] : NULL;

    // Validar los campos del formulario
    if (empty($nombre_archivo)) $errors[] = "El nombre del archivo es obligatorio.";
    if (empty($tipo_lectura)) $errors[] = "El tipo de lectura es obligatorio.";
    if (empty($materia_id)) $errors[] = "La materia es obligatoria.";

    if (strlen($nombre_archivo) > 255) $errors['nombre_archivo'] = "El nombre es demasiado largo.";
    if (strlen($tipo_lectura) > 255) $errors['tipo_lectura'] = "El tipo de lectura es demasiado largo.";

    // Verificar si se ha subido un archivo
    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] === UPLOAD_ERR_NO_FILE) $errors[] = "Por favor, seleccione un archivo.";
    else {
        // Obtener la extensión del archivo
        $extension = strtolower(pathinfo($archivo_nombre, PATHINFO_EXTENSION));

        // Verificar si la extensión es válida
        $extensiones_validas = array("pdf", "xls", "xlsx", "doc", "docx");

        if (!in_array($extension, $extensiones_validas)) $errors[] = "La extensión del archivo no es válida. Por favor, seleccione un archivo PDF, Excel o Word.";
    }

    // Si no hay errores, procede a subir el archivo y guardar en la base de datos
    if (empty($errors)) {
        // Establecer la ruta de destino para la subida del archivo
        $directorio_destino = $_SERVER['DOCUMENT_ROOT'] . "/academia/uploads/materiales/";

        // Comprobar si el directorio de destino existe, si no, intenta crearlo
        if (!file_exists($directorio_destino)) mkdir($directorio_destino, 0777, true); // Crea el directorio recursivamente con permisos de escritura

        // Generar un nombre único para el archivo
        $archivo_destino = $directorio_destino . uniqid() . "_" . $archivo_nombre;

        if (move_uploaded_file($archivo_temporal, $archivo_destino)) {
            // Obtener el tamaño del archivo
            $tamano = filesize($archivo_destino);

            // Insertar los datos en la base de datos
            $sql = "INSERT INTO materiales (nombre_archivo, tipo_lectura, archivo, tamano, materia_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(1, $nombre_archivo);
            $stmt->bindParam(2, $tipo_lectura);
            $stmt->bindParam(3, $archivo_destino);
            $stmt->bindParam(4, $tamano);
            $stmt->bindParam(5, $materia_id);
            $stmt->execute();
        } else $errors[] = "Error al subir el archivo. Por favor, inténtalo de nuevo.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materiales</title>
    <link rel="stylesheet" href="../build/css/app.css" />
</head>

<body>
    <h2>Materiales</h2>

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
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Tamaño</th>
                    <th>Materia</th>
                    <th>Descargar</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados_paginados as $materiales) { ?>
                    <tr>
                        <td><?= $materiales['nombre_archivo']; ?></td>
                        <td><?= ucfirst($materiales['tipo_lectura']); ?></td>
                        <td><?= $materiales['fecha']; ?></td>
                        <td><?= formatSizeUnits($materiales['tamano']); ?></td>
                        <td>
                            <?php
                            // Consultar la base de datos para obtener el nombre de la materia
                            $query_materia = "SELECT nombre FROM materias WHERE id = ?";
                            $get_materia = $conn->prepare($query_materia);
                            $get_materia->execute([$materiales['materia_id']]);
                            $materia = $get_materia->fetchColumn();
                            echo $materia;
                            ?>
                        </td>
                        <td><a href="<?= '../uploads/' . basename($materiales['archivo']) ?>" download>Descargar</a></td>
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

    <h2>Añadir Materiales</h2>

    <div class="bloques">
        <form action="materiales.php" method="POST" class="formulario" enctype="multipart/form-data">
            <?php if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error . "<br/>"; ?>

            <label for="nombre_archivo" class="formulario__label">Nombre del Archivo:</label>
            <input class="formulario__input" type="text" name="nombre_archivo" id="nombre_archivo" value="<?php echo isset($_POST['nombre_archivo']) ? htmlspecialchars($_POST['nombre_archivo']) : ''; ?>" />

            <label for="tipo" class="formulario__label">Tipo de lectura:</label>
            <select class="formulario__input" name="tipo" id="tipo">
                <option value="" disabled>Seleccione un tipo de lectura</option>
                <option value="obligatoria">Obligatoria</option>
                <option value="complementaria">Complementaria</option>
            </select>

            <label for="archivo" class="formulario__label">Archivo:</label>
            <input class="formulario__input" type="file" name="archivo" id="archivo">

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