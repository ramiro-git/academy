<?php

include("../config/sesion.php");

include("../config/functions.php");

isAdmin($user_id, $conn);

$select_profile = $conn->prepare("SELECT * FROM `usuarios` WHERE id = ?");
$select_profile->execute([$user_id]);

if ($select_profile->rowCount() > 0) $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>

<body>
    <h1>Dashboard</h1>
    <h2>Bienvenido, <?php echo $fetch_profile['name']; ?></h2>
</body>

</html>