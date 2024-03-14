<?php

include("../config/sesion.php");

include("../config/functions.php");

isAdmin($user_id, $conn);

if (empty($_SESSION['admin_id'])) {
    header("Location: ../index.php"); // Redirigir al usuario al index.php
    exit(); // Detener la ejecución del script después de la redirección
}

// Inicializar variables de búsqueda con valores de la URL o cadenas vacías
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Construir la URL base para la paginación
$base_url = 'instructores.php';
$query_string = '';

// Agregar parámetros de búsqueda a la URL base
if (!empty($search)) $query_string .= '&search=' . urlencode($search);

// Consulta base para seleccionar todos las inscripciones
$query = "SELECT * FROM instructores WHERE 1";

// Aplicar filtros si se han proporcionado
if (!empty($search)) $query .= " AND (nombre LIKE '%$search%' OR apellido LIKE '%$search%' OR email LIKE '%$search%' OR dni LIKE '%$search%' OR especialidad LIKE '%$search%')";

// Ejecutar la consulta para obtener las inscripciones
$get_inscriptions = $conn->prepare($query);
$get_inscriptions->execute();

// Obtener todos los resultados de la consulta
$resultados = $get_inscriptions->fetchAll();

// Definir la paginación
$num_inscripciones_por_pagina = 1; // Número de inscripciones por página
$total_resultados = count($resultados); // Total de resultados
$total_paginas = ceil($total_resultados / $num_inscripciones_por_pagina); // Total de páginas
$pagina_actual = isset($_GET['pagina']) ? min(max(1, $_GET['pagina']), $total_paginas) : 1; // Página actual
$inicio = ($pagina_actual - 1) * $num_inscripciones_por_pagina; // Índice de inicio para la paginación
$resultados_paginados = array_slice($resultados, $inicio, $num_inscripciones_por_pagina); // Recortar resultados para la página actual

// Procesar el formulario de agregar cursos si se ha enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array(); // Inicializar un array para almacenar errores

    // Recoger los datos del formulario
    $nombre = (isset($_POST["nombre"])) ? $_POST["nombre"] : NULL;
    $apellido = (isset($_POST["apellido"])) ? $_POST["apellido"] : NULL;
    $email = (isset($_POST["email"])) ? $_POST["email"] : NULL;
    $dni = (isset($_POST["dni"])) ? $_POST["dni"] : NULL;
    $especialidad = (isset($_POST["especialidad"])) ? $_POST["especialidad"] : NULL;

    // Validar los campos del formulario
    if (empty($nombre)) $errors['nombre'] = "El nombre es obligatorio.";
    if (empty($apellido)) $errors['apellido'] = "El apellido es obligatorio.";
    if (empty($email)) $errors['email'] = "El email es obligatorio.";
    if (empty($dni)) $errors['dni'] = "El dni es obligatorio.";
    if (empty($especialidad)) $errors['especialidad'] = "La especialidad es obligatoria.";

    if (strlen($nombre) > 255) $errors['name'] = "El nombre es demasiado largo.";
    if (strlen($apellido) > 255) $errors['surname'] = "El apellido es demasiado largo.";
    if (strlen($email) > 255) $errors['email'] = "El email es demasiado largo.";
    if (strlen($especialidad) > 255) $errors['especialidad'] = "La especialidad es demasiado larga.";

    // Validar el DNI
    if (!empty($dni) && ($dni < 1 || $dni > 99999999)) $errors['dni'] = "El DNI debe estar en el rango de 1 a 99999999.";

    // Validar el Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "El E-Mail tiene un formato inválido";

    // Validar si el correo electrónico está en la base de datos de usuarios
    $sql = "SELECT COUNT(*) AS count FROM usuarios WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row['count'] == 0) $errors['email'] = "El correo electrónico no está registrado en la base de datos de usuarios.";
    else {
        // Si el correo está registrado, obtener la especialidad si ya está registrado como instructor
        $sql_especialidad = "SELECT especialidad FROM instructores WHERE email = :email";
        $stmt_especialidad = $conn->prepare($sql_especialidad);
        $stmt_especialidad->bindParam(':email', $email);
        $stmt_especialidad->execute();
        $especialidad_existente = $stmt_especialidad->fetch(PDO::FETCH_ASSOC);
        if ($especialidad_existente && $especialidad_existente['especialidad'] == $especialidad) $errors['especialidad'] = "La especialidad ya está registrada para este instructor.";
    }

    // Si no hay errores, insertar el nuevo curso en la base de datos
    if (empty($errors)) {
        $sql = "INSERT INTO instructores (nombre, apellido, email, dni, especialidad) VALUES (:nombre, :apellido, :email, :dni, :especialidad)";

        $result = $conn->prepare($sql);

        // Ejecutar la consulta preparada con los datos del formulario
        $result = $result->execute(array(
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':email' => $email,
            ':dni' => $dni,
            ':especialidad' => $especialidad,
        ));

        // Redirigir de vuelta a la página de cursos después de agregar el curso
        header("Location: instructores.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructores</title>
    <link rel="stylesheet" href="../build/css/app.css" />
</head>

<body>
    <h2>Instructores</h2>

    <div class="bloques">
        <form class="formulario" method="GET" action="<?= $base_url ?>">
            <label for="search" class="formulario__label">Nombre, Apellido, Email, DNI o Especialidad:</label>
            <input class="formulario__input" type="text" name="search" placeholder="Ingrese el nombre, apellido, email, DNI o especialidad del instructor" value="<?= htmlspecialchars($search) ?>">

            <button class="formulario__submit" type="submit">Buscar</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Email</th>
                    <th>DNI</th>
                    <th>Especialidad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados_paginados as $instructor) { ?>
                    <tr>
                        <td><?= $instructor['nombre']; ?></td>
                        <td><?= $instructor['apellido']; ?></td>
                        <td><?= $instructor['email']; ?></td>
                        <td><?= $instructor['dni']; ?></td>
                        <td><?= $instructor['especialidad']; ?></td>
                        <td><a href="updateInstructor?id=<?= $instructor["id"] ?>">Editar</a> <a href="deleteInstructor?id=<?= $instructor['id'] ?>">Eliminar</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php if ($total_paginas > 1) { ?>
            <div class='pagination'>
                <?php if ($pagina_actual > 1) { ?>
                    <a href='<?= "$base_url?pagina=1$query_string" ?>'> Primera </a>
                    <a href='<?= "$base_url?pagina=" . ($pagina_actual - 1) . "$query_string" ?>'> Anterior </a>
                <?php } ?>
                <?php for ($i = 1; $i <= $total_paginas; $i++) { ?>
                    <a <?= ($pagina_actual == $i) ? 'class="active"' : '' ?> href='<?= "$base_url?pagina=$i$query_string" ?>'><?= $i ?></a>
                <?php } ?>
                <?php if ($pagina_actual < $total_paginas) { ?>
                    <a href='<?= "$base_url?pagina=" . ($pagina_actual + 1) . "$query_string" ?>'> Siguiente </a>
                    <a href='<?= "$base_url?pagina=$total_paginas$query_string" ?>'> Última </a>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <h2>Añadir Instructores</h2>

    <div class="bloques">
        <form action="instructores.php" method="POST" class="formulario">
            <?php if (!empty($errors)) foreach ($errors as $error) echo "<br/>" . $error . "<br/>"; ?>

            <label for="nombre" class="formulario__label">Nombre:</label>
            <input class="formulario__input" type="text" name="nombre" id="nombre" required value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" />

            <label for="apellido" class="formulario__label">Apellido:</label>
            <input class="formulario__input" type="text" name="apellido" id="apellido" required value="<?php echo isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : ''; ?>" />

            <label for="email" class="formulario__label">Email:</label>
            <input class="formulario__input" type="email" name="email" id="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />

            <label for="dni" class="formulario__label">DNI:</label>
            <input class="formulario__input" type="number" name="dni" id="dni" required value="<?php echo isset($_POST['dni']) ? htmlspecialchars($_POST['dni']) : ''; ?>" />

            <label for="especialidad" class="formulario__label">Especialidad:</label>
            <input class="formulario__input" type="text" name="especialidad" id="especialidad" required value="<?php echo isset($_POST['especialidad']) ? htmlspecialchars($_POST['especialidad']) : ''; ?>" />

            <input class="formulario__submit" type="submit" value="Añadir">
        </form>
    </div>
</body>

</html>