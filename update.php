<?php

include("config/sesion.php");

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
    $titulo_pagina = $fetch_profile['name'];
} else header("Location: index.php");

$errors = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = isset($_POST["name"]) ? $_POST["name"] : NULL;
    $apellido = isset($_POST["surname"]) ? $_POST["surname"] : NULL;
    $genero = (isset($_POST["gender"])) ? $_POST["gender"] : NULL;
    $password = (isset($_POST["password"])) ? $_POST["password"] : NULL;
    $password_repeat = (isset($_POST["password_repeat"])) ? $_POST["password_repeat"] : NULL;
    $twoFactor = isset($_POST['twoFactor']) ? 1 : 0;

    if (empty($nombre)) $errors['name'] = "El  nombre es obligatorio.";
    if (empty($apellido)) $errors['surname'] = "El apellido es obligatorio.";
    if (empty($genero)) $errors['gender'] = "El género es obligatorio";

    if (strlen($nombre) > 255) $errors['name'] = "El nombre es demasiado largo.";
    if (strlen($apellido) > 255) $errors['surname'] = "El apellido es demasiado largo.";

    $generosPermitidos = array("male", "female", "ratherNotSay");

    if (!in_array($genero, $generosPermitidos)) $errors['gender'] = "El género seleccionado no es válido.";

    if (empty($errors)) {
        $sql = "UPDATE usuarios SET name = :name, surname = :surname, gender = :gender, twoFactor = :twoFactor WHERE id = :user_id";

        $result = $conn->prepare($sql);

        $params = array(
            ':name' => $nombre,
            ':surname' => $apellido,
            ':gender' => $genero,
            ':twoFactor' => $twoFactor,
            ':user_id' => $user_id,
        );

        $result->execute($params);

        header("Location: update.php?id=" . $user_id);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuario - <?php echo $titulo_pagina ?></title>
</head>

<body>
    <h1>Bienvenido, <?php echo $titulo_pagina; ?></h1>

    <form action="update.php" method="POST">
        <?php if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error . "<br/>"; ?>

        Nombre:
        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($fetch_profile['name']); ?>" />

        Apellido:
        <input type="text" name="surname" id="surname" value="<?php echo htmlspecialchars($fetch_profile['surname']); ?>" />

        Género:
        <select name="gender" id="gender">
            <option value="male" <?php if ($fetch_profile['gender'] === 'male') echo 'selected'; ?>>Masculino</option>
            <option value="female" <?php if ($fetch_profile['gender'] === 'female') echo 'selected'; ?>>Femenino</option>
            <option value="ratherNotSay" <?php if ($fetch_profile['gender'] === 'ratherNotSay') echo 'selected'; ?>>Prefiero no decirlo</option>
        </select>

        Email:
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($fetch_profile['email']); ?>" />

        Contraseña:
        <input type="password" name="password" id="password" />

        Repetir Contraseña:
        <input type="password" name="password_repeat" id="password_repeat" />

        Doble Factor:
        <input type="checkbox" name="twoFactor" id="twoFactor" <?php if ($fetch_profile['twoFactor'] == 1) echo 'checked'; ?>>

        Tipo de Usuario: <?php echo $fetch_profile['role'] == 1 ? "Administrador" : "Usuario" ?>

        <button type="submit">Actualizar Perfil</button>
    </form>
</body>

</html>