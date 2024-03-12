<?php
include("config/sesion.php");

if (isset($_GET['token'])) {
  $token = $_GET['token'];
  $select_user = $conn->prepare("SELECT * FROM `usuarios` WHERE token = ?");
  $select_user->execute([$token]);
  $row = $select_user->fetch(PDO::FETCH_ASSOC);

  if ($select_user->rowCount() > 0) {
    $_SESSION['user_id'] = $row['id'];
  }
}

if (!isset($_SESSION['user_id'])) {
  header('Location: index.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Recuperar Cuenta</title>
</head>

<body>
  <form action="reset_password.php" method="POST">
    Password:
    <input type="password" name="password" required />

    Repetir Contraseña:
    <input type="password" name="password_repeat" id="password_repeat" required />

    <button type="submit">Confirmar</button>
  </form>
</body>

</html>