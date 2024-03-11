<?php

include("config/sesion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array();

    $email = (isset($_POST["email"])) ? htmlspecialchars($_POST["email"]) : NULL;
    $password = (isset($_POST["password"])) ? $_POST["password"] : NULL;

    if (empty($email)) $errors['email'] = "El E-Mail es obligatorio";
    if (empty($password)) $errors['password'] = "La contraseña es obligatoria";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "El E-Mail tiene un formato inválido";

    if (strlen($password) < 6) $errors['password'] = "La contraseña debe contener mínimo 6 caracteres";

    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) $errors['password'] = "La contraseña debe contener al menos una letra mayúscula, una letra minúscula y un número.";

    if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error;
    else {
        $sql = "SELECT * FROM usuarios WHERE email= :email";

        $result = $conn->prepare($sql);

        $result->execute(array(':email' => $email));

        $row = $result->fetch(PDO::FETCH_ASSOC);

        if ($result->rowCount() > 0) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];

                header('Location: index.php');
            }
        }
    }
}
