<?php

include("config/sesion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array();

    $nombre = (isset($_POST["name"])) ? $_POST["name"] : NULL;
    $apellido = (isset($_POST["surname"])) ? $_POST["surname"] : NULL;
    $genero = (isset($_POST["gender"])) ? $_POST["gender"] : NULL;
    $email = (isset($_POST["email"])) ? $_POST["email"] : NULL;
    $password = (isset($_POST["password"])) ? $_POST["password"] : NULL;
    $password_repeat = (isset($_POST["password_repeat"])) ? $_POST["password_repeat"] : NULL;

    if (empty($nombre)) $errors['name'] = "El nombre es obligatorio.";
    if (empty($apellido)) $errors['surname'] = "El apellido es obligatorio.";
    if (empty($genero)) $errors['gender'] = "El género es obligatorio";
    if (empty($email)) $errors['email'] = "El E-Mail es obligatorio";
    if (empty($password)) $errors['password'] = "La contraseña es obligatoria";
    if (empty($password_repeat)) $errors['password_repeat'] = "La contraseña repetida es obligatoria";

    if (strlen($nombre) > 255) $errors['name'] = "El nombre es demasiado largo.";
    if (strlen($apellido) > 255) $errors['surname'] = "El apellido es demasiado largo.";

    $permitedGenders = array("male", "female", "ratherNotSay");

    if (!in_array($genero, $permitedGenders)) $errors['gender'] = "El género seleccionado no es válido.";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "El E-Mail tiene un formato inválido";

    if (strlen($password) < 6) $errors['password'] = "La contraseña debe contener mínimo 6 caracteres";

    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) $errors['password'] = "La contraseña debe contener al menos una letra mayúscula, una letra minúscula y un número.";

    if ($password != $password_repeat) $errors['password_repeat'] = "Las contraseñas no coinciden";

    $sql_check_email = "SELECT COUNT(*) AS count FROM usuarios WHERE email = :email";

    $result_email = $conn->prepare($sql_check_email);

    $result_email->execute(array(':email' => $email));

    $result_check_email = $result_email->fetch(PDO::FETCH_ASSOC);

    if ($result_check_email['count'] > 0) $errors['email'] = "El correo electrónico ya está registrado.";

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO usuarios (name, surname, gender, email, password, role, active) VALUES (:name, :surname, :gender, :email, :password, :role, :active)";

        $result = $conn->prepare($sql);

        $result = $result->execute(array(
            ':name' => $nombre,
            ':surname' => $apellido,
            ':gender' => $genero,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':role' => 0,
            ':active' => 1
        ));

        header("Location: login.php");
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registro</title>
    <link rel="stylesheet" href="build/css/app.css" />
    <script src="build/js/app.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
</head>

<body>
    <h2>Registrar Usuario</h2>

    <div class="bloques">
        <form action="registro.php" method="POST" class="formulario">
            <?php if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error . "<br/>"; ?>

            <label for="name" class="formulario__label">Nombre:</label>
            <input class="formulario__input" type="text" name="name" id="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" />

            <label for="surname" class="formulario__label">Apellido:</label>
            <input class="formulario__input" type="text" name="surname" id="surname" required value="<?php echo isset($_POST['surname']) ? htmlspecialchars($_POST['surname']) : ''; ?>" />

            <label for="gender" class="formulario__label">Género:</label>
            <select name="gender" id="gender" class="formulario__select">
                <option value="male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'male') ? 'selected' : ''; ?>>Masculino</option>
                <option value="female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'female') ? 'selected' : ''; ?>>Femenino</option>
                <option value="ratherNotSay" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'ratherNotSay') ? 'selected' : ''; ?>>Prefiero no decirlo</option>
            </select>

            <label for="email" class="formulario__label">Email:</label>
            <input class="formulario__input" type="email" name="email" id="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />

            <label for="password" class="formulario__label">Contraseña:</label>
            <input class="formulario__input" type="password" name="password" id="password" />
            <div class="password-input-container">
                <span class="password-toggle" onclick="togglePasswordVisibility('password')">
                    <i class="fas fa-eye"></i>
                </span>
            </div>

            <label for="password_repeat" class="formulario__label">Repetir Contraseña:</label>
            <input class="formulario__input" type="password" name="password_repeat" id="password_repeat" />
            <div class="password-input-container">
                <span class="password-toggle" onclick="togglePasswordVisibility('password_repeat')">
                    <i class="fas fa-eye"></i>
                </span>
            </div>

            <button class="formulario__submit" type="submit">Registrame</button>
        </form>

        <div class="acciones">
            <a class="acciones__enlace" href="login.php">¿Ya tienes cuenta? Iniciar Sesión</a>
            <a class="acciones__enlace" href="olvide.php">¿Olvidaste la contraseña? Recuperar Contraseña</a>
        </div>
    </div>
</body>

</html>