<?php

// Incluir archivo de sesión para manejar sesiones de usuario
include("config/sesion.php");

include("config/mails.php");

// Verificar si la solicitud HTTP es de tipo POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Array para almacenar errores de validación
    $errors = array();

    // Recoger y validar correo electrónico y contraseña del formulario
    $email = (isset($_POST["email"])) ? htmlspecialchars($_POST["email"]) : NULL;
    $password = (isset($_POST["password"])) ? $_POST["password"] : NULL;

    // Validar que el correo electrónico no esté vacío
    if (empty($email)) $errors['email'] = "El E-Mail es obligatorio";

    // Validar formato de correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "El E-Mail tiene un formato inválido";

    // Validar longitud mínima de contraseña
    if (strlen($password) < 6) $errors['password'] = "La contraseña debe contener mínimo 6 caracteres";

    // Validar complejidad de contraseña
    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) $errors['password'] = "La contraseña debe contener al menos una letra mayúscula, una letra minúscula y un número.";

    // Mostrar errores de validación
    if (empty($errors)) {
        // Si no hay errores, proceder con la autenticación
        // Consulta SQL para obtener el usuario por su correo electrónico
        $sql = "SELECT * FROM usuarios WHERE email= :email";

        // Preparar y ejecutar la consulta
        $result = $conn->prepare($sql);

        $result->execute(array(':email' => $email));

        // Obtener la fila de resultado
        $row = $result->fetch(PDO::FETCH_ASSOC);

        // Verificar si se encontró algún usuario con el correo electrónico dado
        if ($result->rowCount() > 0) {
            // Verificar si la contraseña proporcionada coincide con la contraseña almacenada
            if (password_verify($password, $row['password'])) {
                // Iniciar sesión almacenando el ID de usuario en la variable de sesión
                $_SESSION['user_id'] = $row['id'];

                // Redirigir al usuario a la página principal
                header('Location: index.php');
            } else $errors['general'] = "Las credenciales no son válidas";
        } else $errors['general'] = "Las credenciales no son válidas";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link rel="stylesheet" href="build/css/app.css" />
    <script src="build/js/app.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
</head>

<body>
    <?php require("components/header.php"); ?>

    <div class="bloques">
        <form action="login.php" method="POST" class="formulario">
            <?php if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error . "<br/>"; ?>

            <label for="email" class="formulario__label">Email:</label>
            <input class="formulario__input" type="email" name="email" id="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />

            <label for="password" class="formulario__label">Contraseña:</label>
            <input class="formulario__input" type="password" name="password" id="password" />
            <div class="password-input-container">
                <span class="password-toggle" onclick="togglePasswordVisibility('password')">
                    <i class="fas fa-eye"></i>
                </span>
            </div>

            <button type="submit" class="formulario__submit">Iniciar Sesión</button>
        </form>

        <div class="acciones">
            <a class="acciones__enlace" href="login.php">¿No tienes cuenta? Registrate</a>
            <a class="acciones__enlace" href="olvide.php">¿Olvidaste la contraseña? Recuperar Contraseña</a>
        </div>
    </div>

    <?php require("components/footer.php"); ?>
</body>

</html>