<?php include("config/sesion.php"); ?>

<header>
    <div class="logo">Academia</div>
    <input type="checkbox" id="nav_check" hidden>
    <nav>
        <ul>
            <?php if ($user_id != '') : ?>
                <li><a href='update.php?id=<?php echo $user_id; ?>'>Actualizar</a></li>
                <?php
                $select_teacher = $conn->prepare("SELECT * FROM `materias` WHERE instructor = ?");
                $select_teacher->execute([$user_id]);

                if ($select_teacher->rowCount() > 0) {
                    $fetch_teacher = $select_teacher->fetch(PDO::FETCH_ASSOC);

                    echo "<li><a href='asistencia.php'>Asistencia</a></li>";
                }
                ?>
                <li>
                    <a href="materiales.php">Materiales</a>
                </li>
                <li>
                    <a href="tareas.php">Tareas</a>
                </li>
                <li><a href='logout.php'>Cerrar Sesión</a></li>
            <?php else : ?>
                <li><a href='login.php'>Login</a></li>
                <li><a href='registro.php'>Registro</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <label for="nav_check" class="hamburger">
        <div></div>
        <div></div>
        <div></div>
    </label>
</header>