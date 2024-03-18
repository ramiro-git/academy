<?php

include("config/sesion.php");

include("config/mails.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array();

    $email = (isset($_POST["email"])) ? htmlspecialchars($_POST["email"]) : NULL;

    if (empty($email)) $errors['email'] = "El E-Mail es obligatorio";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "El E-Mail tiene un formato inválido";

    if (empty($errors)) {
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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="build/css/app.css" />
</head>

<body>
    <?php require("components/header.php"); ?>

    <div class="bloques">
        <form action="olvide.php" method="POST" class="formulario">
            <?php if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error . "<br/>"; ?>

            <label for="email" class="formulario__label">Email:</label>
            <input class="formulario__input" type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />

            <button class="formulario__submit" type="submit">Recuperar Contraseña</button>
        </form>

        <div class="acciones">
            <a class="acciones__enlace" href="login.php">¿Ya tienes cuenta? Iniciar Sesión</a>
            <a class="acciones__enlace" href="registro.php">¿No tienes cuenta? Registrate</a>
        </div>
    </div>
</body>

</html>