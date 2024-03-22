<?php
include("config/sesion.php");

if (empty($user_id)) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: tareas.php");
    exit();
}

$task_id = $_GET['id'];

$select_task = $conn->prepare("SELECT * FROM tareas WHERE id = ?");
$select_task->execute([$task_id]);

if ($select_task->rowCount() > 0) {
    $task_details = $select_task->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $errors = [];

        $mensaje = isset($_POST["mensaje"]) ? $_POST["mensaje"] : NULL;
        $archivo_temporal = isset($_FILES['archivo_entregado']['tmp_name']) ? $_FILES['archivo_entregado']['tmp_name'] : NULL;
        $archivo_nombre = isset($_FILES['archivo_entregado']['name']) ? $_FILES['archivo_entregado']['name'] : NULL;

        if (empty($mensaje)) $errors['mensaje'] = "El mensaje es obligatorio.";

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

        if (empty($errors)) {
            // Establecer la ruta de destino para la subida del archivo
            $directorio_destino = $_SERVER['DOCUMENT_ROOT'] . "/academia/uploads/tareas/";

            // Comprobar si el directorio de destino existe, si no, intenta crearlo
            if (!file_exists($directorio_destino)) mkdir($directorio_destino, 0777, true); // Crea el directorio recursivamente con permisos de escritura

            // Generar un nombre único para el archivo
            $archivo_destino = $directorio_destino . uniqid() . "_" . $archivo_nombre;

            if (move_uploaded_file($archivo_temporal, $archivo_destino)) {
                $sql = "INSERT INTO entregas_tareas (tarea_id, user_id, mensaje, archivo_entregado) VALUES (:tarea_id, :user_id, :mensaje, :archivo_entregado)";

                $result = $conn->prepare($sql);

                $result = $result->execute(array(
                    ':tarea_id' => $task_id,
                    ':user_id' => $user_id,
                    ':mensaje' => $mensaje,
                    ':archivo_entregado' => $archivo_destino
                ));

                header("Location: tareas.php");
                exit();
            }
        }
    }
} else {
    header("Location: tareas.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarea</title>
    <link rel="stylesheet" href="build/css/app.css" />
</head>

<body>
    <?php require("components/header.php"); ?>

    <h2>Tarea</h2>

    <div class="bloques">
        <form action="tarea.php?id=<?= $task_id; ?>" method="POST" enctype="multipart/form-data" class="formulario">
            <?php if (!empty($errors)) foreach ($errors as $error) echo "<p class='error'>$error</p>"; ?>

            <!-- Mostrar los detalles de la tarea en los campos del formulario -->
            <label for="nombre" class="formulario__label">Nombre:</label>
            <input disabled class="formulario__input" type="text" name="nombre" id="nombre" required value="<?php echo htmlspecialchars($task_details['nombre']); ?>" />

            <label for="descripcion" class="formulario__label">Descripción:</label>
            <input disabled class="formulario__input" type="text" name="descripcion" id="descripcion" required value="<?php echo htmlspecialchars($task_details['descripcion']); ?>" />

            <?php if (!empty($task_details['archivo'])) : ?>
                <label class="formulario__label">Archivo: <a href="http://localhost/academia/uploads/tareas/<?php echo basename($task_details['archivo']); ?>" download>Descargar archivo</a></label>
            <?php endif; ?>

            <label for="mensaje" class="formulario__label">Mensaje:</label>
            <textarea class="formulario__input" name="mensaje" id="mensaje"><?= isset($_POST['mensaje']) ? $_POST['mensaje'] : ''; ?></textarea>

            <label for="archivo_entregado" class="formulario__label">Archivo:</label>
            <input type="file" name="archivo_entregado" id="archivo_entregado" class="formulario__input">

            <input class="formulario__submit" type="submit" value="Enviar Tarea">
        </form>
    </div>

    <?php require("components/footer.php"); ?>
</body>

</html>