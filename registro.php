<?php

include("config/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array();

    $nombre = (isset($_POST["name"])) ? $_POST["name"] : NULL;
    $apellido = (isset($_POST["surname"])) ? $_POST["surname"] : NULL;
    $genero = (isset($_POST["gender"])) ? $_POST["gender"] : NULL;
    $email = (isset($_POST["email"])) ? $_POST["email"] : NULL;
    $password = (isset($_POST["password"])) ? $_POST["password"] : NULL;
    $password_repeat = (isset($_POST["password_repeat"])) ? $_POST["password_repeat"] : NULL;

    if (empty($nombre)) $errors['name'] = "El  nombre es obligatorio.";
    if (empty($apellido)) $errors['surname'] = "El apellido es obligatorio.";
    if (empty($genero)) $errors['gender'] = "El género es obligatorio";
    if (empty($email)) $errors['email'] = "El E-Mail es obligatorio";
    if (empty($password)) $errors['password'] = "La contraseña es obligatoria";
    if (empty($password_repeat)) $errors['password_repeat'] = "La contraseña repetida es obligatoria";

    if (strlen($nombre) > 255) $errors['name'] = "El nombre es demasiado largo.";
    if (strlen($apellido) > 255) $errors['surname'] = "El apellido es demasiado largo.";

    $generosPermitidos = array("male", "female", "ratherNotSay");

    if (!in_array($genero, $generosPermitidos)) $errors['gender'] = "El género seleccionado no es válido.";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "El E-Mail tiene un formato inválido";

    if (strlen($password) < 6) $errors['password'] = "La contraseña debe contener mínimo 6 caracteres";

    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) $errors['password'] = "La contraseña debe contener al menos una letra mayúscula, una letra minúscula y un número.";

    if ($password != $password_repeat) $errors['password_repeat'] = "Las contraseñas no coinciden";

    $sql_check_email = "SELECT COUNT(*) AS count FROM usuarios WHERE email = :email";

    $result_email = $conn->prepare($sql_check_email);

    $result_email->execute(array(':email' => $email));

    $result_check_email = $result_email->fetch(PDO::FETCH_ASSOC);

    if ($result_check_email['count'] > 0) $errors['email'] = "El correo electrónico ya está registrado.";

    if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error;
    else {
        $passwordHasheado = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO usuarios (name, surname, gender, email, password) VALUES (:name, :surname, :gender, :email, :password)";

        $result = $conn->prepare($sql);

        $result = $result->execute(array(
            ':name' => $nombre,
            ':surname' => $apellido,
            ':gender' => $genero,
            ':email' => $email,
            ':password' => $passwordHasheado,
        ));

        header("Location: login.html");
    }
}
