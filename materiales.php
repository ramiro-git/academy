<?php include("config/sesion.php");

include("config/functions.php");

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
    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] === UPLOAD_ERR_NO_FILE) $errors['archivo'] = "Por favor, seleccione un archivo.";
    else {
        // Obtener la extensión del archivo
        $extension = strtolower(pathinfo($archivo_nombre, PATHINFO_EXTENSION));

        // Verificar si la extensión es válida
        $extensiones_validas = array("pdf", "xls", "xlsx", "doc", "docx");

        if (!in_array($extension, $extensiones_validas)) $errors['archivo'] = "La extensión del archivo no es válida. Por favor, seleccione un archivo PDF, Excel o Word.";
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
    <link rel="stylesheet" href="build/css/app.css" />
</head>

<body>
    <?php require("components/header.php"); ?>

    <?php $select_teacher = $conn->prepare("SELECT * FROM `materias` WHERE instructor = ?");
    $select_teacher->execute([$user_id]);

    if ($select_teacher->rowCount() > 0) { ?>
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
                <input class="formulario__input" type="file" name="archivo" id="archivo" accept=".pdf,.doc,.docx" placeholder="Seleccionar archivo...">

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
    <?php } else {
        // Consultar la base de datos para obtener las materias a las que está inscrito el usuario
        $query_materias_usuario = "SELECT * FROM inscripciones_materias WHERE user_id = ?";
        $get_materias_usuario = $conn->prepare($query_materias_usuario);
        $get_materias_usuario->execute([$user_id]);
        $materias_usuario = $get_materias_usuario->fetchAll();

        // Verificar si el usuario tiene materias inscritas
        if (count($materias_usuario) > 0) {
            echo "<h2>Materiales</h2>";

            // Iterar sobre las materias del usuario y obtener los materiales asociados a cada una
            foreach ($materias_usuario as $materia_usuario) {
                $materia_id = $materia_usuario['materia_id'];
                $query_materiales = "SELECT * FROM materiales WHERE materia_id = ?";
                $get_materiales = $conn->prepare($query_materiales);
                $get_materiales->execute([$materia_id]);
                $materiales = $get_materiales->fetchAll();

                // Verificar si hay materiales disponibles para esta materia
                if (count($materiales) > 0) {
                    echo "<p>Materiales para la materia: " . $materia_id . ":</p>";
                    echo "<ul>";
                    foreach ($materiales as $material) {
                        echo "<li>Nombre: " . $material['nombre_archivo'] . " - Tipo: " . $material['tipo_lectura'] . " - Tamaño: " . formatSizeUnits($material['tamano']) . " - <a href='http://localhost/academia/uploads/materiales/" . basename($material['archivo']) . "' download class='btn btn-primary'>Descargar</a></li>";
                    }
                    echo "</ul>";
                }
            }
        } else echo "<div class='text-center'><h2>No hay materiales aún</h2><img style='max-width: 300px;' src='build/img/download.svg' alt='Descargas' /></div>";
    }

    require("components/footer.php"); ?>
</body>

</html>