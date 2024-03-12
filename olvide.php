<?php

include("config/sesion.php");

include("config/mails.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array();

    $email = (isset($_POST["email"])) ? htmlspecialchars($_POST["email"]) : NULL;

    if (empty($email)) $errors['email'] = "El E-Mail es obligatorio";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "El E-Mail tiene un formato inválido";

    if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error;
    else {
        $sql = "SELECT * FROM usuarios WHERE email= :email";

        $result = $conn->prepare($sql);

        $result->execute(array(':email' => $email));

        $row = $result->fetch(PDO::FETCH_ASSOC);

        if ($result->rowCount() > 0) {
            $token = bin2hex(random_bytes(32));

            $update_token = $conn->prepare("UPDATE `usuarios` SET token = ? WHERE id = ?");

            $update_token->execute([$token, $row['id']]);

            recuperacion($email, $token);
        }
    }
}
