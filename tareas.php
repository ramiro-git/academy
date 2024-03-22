<?php include("config/sesion.php");

if (empty($user_id)) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id']) && $_GET['id'] != $user_id) {
    header("Location: index.php");
    exit();
}

$select_profile = $conn->prepare("SELECT * FROM `usuarios` WHERE id = ?");
$select_profile->execute([$user_id]);

if ($select_profile->rowCount() > 0) {
    $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
    $page_title = $fetch_profile['name'];
} else header("Location: index.php");

// Procesar el formulario de agregar cursos si se ha enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array(); // Inicializar un array para almacenar errores

    // Recoger los datos del formulario
    $nombre = (isset($_POST["nombre"])) ? $_POST["nombre"] : NULL;
    $descripcion = (isset($_POST["descripcion"])) ? $_POST["descripcion"] : NULL;
    $fecha_entrega = (isset($_POST["fecha_entrega"])) ? $_POST["fecha_entrega"] : NULL;
    $archivo_temporal = isset($_FILES['archivo_entregado']['tmp_name']) ? $_FILES['archivo_entregado']['tmp_name'] : NULL;
    $archivo_nombre = isset($_FILES['archivo_entregado']['name']) ? $_FILES['archivo_entregado']['name'] : NULL;
    $materia_id = (isset($_POST["materia"])) ? $_POST["materia"] : NULL;

    // Validar los campos del formulario
    if (empty($nombre)) $errors['nombre'] = "El nombre es obligatorio.";
    if (empty($descripcion)) $errors['descripcion'] = "La descripcion es obligatoria.";
    if (empty($fecha_entrega)) $errors['fecha_entrega'] = "La fecha de entrega es obligatoria.";
    if (empty($materia_id)) $errors[] = "La materia es obligatoria.";

    if (strlen($nombre) > 255) $errors['nombre'] = "El nombre es demasiado largo.";
    if (strlen($descripcion) > 255) $errors['descripcion'] = "La descripcion es demasiado larga.";

    // Verificar si se ha subido un archivo
    if (!isset($_FILES['archivo_entregado']) || $_FILES['archivo_entregado']['error'] === UPLOAD_ERR_NO_FILE) $errors['archivo_entregado'] = "Por favor, seleccione un archivo.";
    else {
        // Obtener la extensión del archivo
        $extension = strtolower(pathinfo($archivo_nombre, PATHINFO_EXTENSION));

        // Verificar si la extensión es válida
        $extensiones_validas = array("pdf", "xls", "xlsx", "doc", "docx");

        if (!in_array($extension, $extensiones_validas)) {
            $errors[] = "La extensión del archivo no es válida. Por favor, seleccione un archivo PDF, Excel o Word.";
        }
    }

    // Si no hay errores, insertar el nuevo curso en la base de datos
    if (empty($errors)) {
        // Establecer la ruta de destino para la subida del archivo
        $directorio_destino = $_SERVER['DOCUMENT_ROOT'] . "/academia/uploads/tareas/";

        // Comprobar si el directorio de destino existe, si no, intenta crearlo
        if (!file_exists($directorio_destino)) mkdir($directorio_destino, 0777, true); // Crea el directorio recursivamente con permisos de escritura

        // Generar un nombre único para el archivo
        $archivo_destino = $directorio_destino . uniqid() . "_" . $archivo_nombre;

        if (move_uploaded_file($archivo_temporal, $archivo_destino)) {
            $sql = "INSERT INTO tareas (nombre, descripcion, fecha_entrega, archivo, materia_id) VALUES (:nombre, :descripcion, :fecha_entrega, :archivo, :materia_id)";

            $result = $conn->prepare($sql);

            // Ejecutar la consulta preparada con los datos del formulario
            $result = $result->execute(array(
                ':nombre' => $nombre,
                ':descripcion' => $descripcion,
                ':fecha_entrega' => $fecha_entrega,
                ':archivo' => $archivo_destino,
                ':materia_id' => $materia_id,
            ));

            // Redirigir de vuelta a la página de cursos después de agregar el curso
            header("Location: tareas.php");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tareas</title>
    <link rel="stylesheet" href="build/css/app.css" />
</head>

<body>
    <?php require("components/header.php"); ?>

    <?php $select_teacher = $conn->prepare("SELECT * FROM `materias` WHERE instructor = ?");
    $select_teacher->execute([$user_id]);

    if ($select_teacher->rowCount() > 0) { ?>
        <h2>Añadir Tareas</h2>

        <div class="bloques">
            <form action="tareas.php" method="POST" enctype="multipart/form-data" class="formulario" class="formulario">
                <?php if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error . "<br/>"; ?>

                <label for="nombre" class="formulario__label">Nombre:</label>
                <input class="formulario__input" type="text" name="nombre" id="nombre" required value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" />

                <label for="descripcion" class="formulario__label">Descripción:</label>
                <input class="formulario__input" type="text" name="descripcion" id="descripcion" required value="<?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?>" />

                <label for="fecha_entrega" class="formulario__label">Fecha de Entrega:</label>
                <input class="formulario__input" type="date" name="fecha_entrega" id="fecha_entrega" required value="<?php echo isset($_POST['fecha_entrega']) ? htmlspecialchars($_POST['fecha_entrega']) : ''; ?>" />

                <label for="archivo_entregado" class="formulario__label">Archivo:</label>
                <input type="file" name="archivo_entregado" id="archivo_entregado" class="formulario__input">

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
    <?php } else { ?>
        <div class="task-list">
            <?php
            // Consultar la base de datos para obtener las tareas asignadas al usuario actual
            $query_tareas = "SELECT tareas.id, tareas.nombre, tareas.descripcion, tareas.fecha_entrega FROM asignaciones_tareas JOIN tareas ON asignaciones_tareas.tarea_id = tareas.id WHERE asignaciones_tareas.user_id = ?";
            $get_tareas = $conn->prepare($query_tareas);
            $get_tareas->execute([$user_id]);
            $tareas = $get_tareas->fetchAll();

            // Verificar si hay tareas asignadas
            if (count($tareas) == 0) {
                echo "<div class='text-center'><h2>No hay tareas asignadas</h2><img style='max-width: 300px;' src='build/img/tareas.svg' alt='Imagen Tareas' /></div>";
            } else {
                echo "<h2>Tareas asignadas</h2>";
                echo "<ul class='task-ul'>";
                // Iterar sobre los resultados y mostrar cada tarea
                foreach ($tareas as $tarea) {
                    echo "<li class='task-item'>";
                    echo "<div class='task-flex'>";
                    echo "<span>" . $tarea['nombre'] . "</span>";
                    echo "<span>Fecha de Entrega: " . $tarea['fecha_entrega'] . "</span>";
                    echo "</div>";
                    echo "<a href='tarea.php?id=" . $tarea["id"] . "' class='task-link'>Ver tarea</a>";
                    echo "</li>";
                    echo "<hr class='hr' />";
                }
                echo "</ul>";
            }
            ?>
        </div>
    <?php }

    require("components/footer.php"); ?>
</body>

</html>