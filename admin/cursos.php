<?php
include("../config/sesion.php");

include("../config/functions.php");

isAdmin($user_id, $conn);

if (empty($_SESSION['admin_id'])) {
    header("Location: ../index.php"); // Redirigir al usuario al index.php
    exit(); // Detener la ejecución del script después de la redirección
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
    <div class="bloques">
        <form action="cursos.php" method="POST" class="formulario">
            <?php if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error . "<br/>"; ?>

            <label for="title" class="formulario__label">Título:</label>
            <input class="formulario__input" type="text" name="title" id="title" required value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" />

            <label for="description" class="formulario__label">Descripción:</label>
            <input class="formulario__input" type="text" name="description" id="description" required value="<?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?>" />

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
            <input class="formulario__input" type="date" name="begin" id="begin" required value="<?php echo isset($_POST['begin']) ? htmlspecialchars($_POST['begin']) : ''; ?>" />

            <label for="duration" class="formulario__label">Duración (en semanas):</label>
            <input class="formulario__input" type="number" name="duration" id="duration" required value="<?php echo isset($_POST['duration']) ? htmlspecialchars($_POST['duration']) : ''; ?>" />

            <input class="formulario__submit" type="submit" value="Añadir">
        </form>
    </div>
</body>

</html>