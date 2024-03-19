<?php include("config/sesion.php"); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
    <link rel="stylesheet" href="build/css/app.css" />
</head>

<body>
    <?php require("components/header.php"); ?>

    <?php if ($user_id != '') : ?>
        <div class="titulo-boton">
            <h2>Materias Activas</h2>
            <button>Organizar materias</button>
        </div>

        <div class="curso-sortable">
            <?php
            // Consulta para obtener las materias del usuario
            $query_materias_usuario = "SELECT * FROM inscripciones_materias WHERE user_id = ?";
            $get_materias_usuario = $conn->prepare($query_materias_usuario);
            $get_materias_usuario->execute([$user_id]);
            $materias_usuario = $get_materias_usuario->fetchAll();

            // Verificar si el usuario tiene materias inscritas
            if (count($materias_usuario) > 0) {
                foreach ($materias_usuario as $materia_usuario) {
                    // Obtener detalles de la materia
                    $materia_id = $materia_usuario['materia_id'];
                    $query_materia = "SELECT * FROM materias WHERE id = ?";
                    $get_materia = $conn->prepare($query_materia);
                    $get_materia->execute([$materia_id]);
                    $materia = $get_materia->fetch();
            ?>
                    <div class="materia">
                        <div class="materia-descripcion">
                            <div class="materia-titulo"><?php echo $materia['nombre']; ?></div>
                        </div>
                        <div class="materia-iconos">
                            <div class="materia-iconos-icono" style="margin-right: 2rem;"><i class="fas fa-book"></i> Contenido</div>
                            <div class="materia-iconos-icono" style="margin-right: 2rem;"><i class="fas fa-comments"></i> Foro</div>
                            <div class="materia-iconos-icono" style="margin-right: 2rem;"><i class="fas fa-envelope"></i> Mensajes</div>
                            <div class="materia-iconos-icono" style="margin-right: 2rem;"><i class="fas fa-clipboard-check"></i> Evaluación</div>
                        </div>
                    </div>

            <?php
                }
            } else {
                echo '<p class="text-center">No hay información</p>';
            }
            ?>
        </div>

    <?php else : ?>
        <p class="text-center">No hay información</p>
    <?php endif; ?>

    <?php require("components/footer.php"); ?>
</body>

</html>