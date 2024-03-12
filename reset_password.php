<?php

include("config/sesion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array();

    $password = (isset($_POST["password"])) ? $_POST["password"] : NULL;
    $password_repeat = (isset($_POST["password_repeat"])) ? $_POST["password_repeat"] : NULL;

    if (empty($password)) $errors['password'] = "La contraseña es obligatoria";
    if (empty($password_repeat)) $errors['password_repeat'] = "La contraseña repetida es obligatoria";

    if (strlen($password) < 6) $errors['password'] = "La contraseña debe contener mínimo 6 caracteres";

    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) $errors['password'] = "La contraseña debe contener al menos una letra mayúscula, una letra minúscula y un número.";

    if ($password != $password_repeat) $errors['password_repeat'] = "Las contraseñas no coinciden";

    if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error;
    else {
        $passwordHasheado = password_hash($password, PASSWORD_BCRYPT);

        $sql = "UPDATE usuarios SET password = :password, token = '' WHERE id = :user_id";

        $result = $conn->prepare($sql);

        $result = $result->execute(array(
            ':password' => $passwordHasheado,
            ':user_id' => $user_id
        ));

        header("Location: index.php");
    }
}