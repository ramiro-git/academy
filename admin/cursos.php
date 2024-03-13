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
</head>

<body>
    <form action="cursos.php" method="POST">
        <?php if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error . "<br/>"; ?>

        Título:
        <input type="text" name="title" id="title" required value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" />

        Descripción:
        <input type="text" name="description" id="description" required value="<?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?>" />

        <?php $sql = $conn->prepare("SELECT id, nombre FROM instructores");
        $sql->execute();
        if ($sql->rowCount() > 0) {
            echo "Instructor: <select name='instructor'>";
            while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='" . $row["id"] . "'>" . $row["nombre"] . "</option>";
            }
            echo "</select>";
        } else {
            echo "No hay instructores disponibles.";
        }
        ?>

        Comienza:
        <input type="date" name="begin" id="begin" required value="<?php echo isset($_POST['begin']) ? htmlspecialchars($_POST['begin']) : ''; ?>" />

        Duración (en semanas):
        <input type="number" name="duration" id="duration" required value="<?php echo isset($_POST['duration']) ? htmlspecialchars($_POST['duration']) : ''; ?>" />

        <input type="submit" value="Submit">
    </form>
</body>

</html>