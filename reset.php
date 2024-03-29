<?php

include("config/sesion.php");

include("config/mails.php");

if (isset($_GET['token'])) {
  $token = $_GET['token'];

  $select_user = $conn->prepare("SELECT * FROM `usuarios` WHERE token = ?");
  $select_user->execute([$token]);

  $row = $select_user->fetch(PDO::FETCH_ASSOC);

  if ($select_user->rowCount() > 0) {
    $_SESSION['user_id'] = $row['id'];
  }
} else header('Location: index.php');

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
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $sql = "UPDATE usuarios SET password = :password, token = '' WHERE id = :user_id";

    $result = $conn->prepare($sql);

    $result = $result->execute(array(
      ':password' => $hashedPassword,
      ':user_id' => $user_id
    ));

    header("Location: index.php");
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Recuperar Cuenta</title>
  <link rel="stylesheet" href="build/css/app.css" />
  <script src="build/js/app.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
</head>

<body>
  <?php require("components/header.php"); ?>

  <div class="bloques">
    <form action="reset.php" method="POST" class="formulario">
      <?php if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error . "<br/>"; ?>

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

      <button class="formulario__submit" type="submit">Confirmar</button>
    </form>
  </div>

  <?php require("components/footer.php"); ?>
</body>

</html>