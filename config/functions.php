<?php

// Función para verificar si el usuario es administrador
function isAdmin($user_id, $conn)
{
    // Verificar si $user_id está vacío
    if ($user_id === '') {
        // Si $user_id está vacío, establecemos $_SESSION['admin_id'] como vacío
        // Esto indica que no hay un usuario logueado y por lo tanto no puede ser administrador
        $_SESSION['admin_id'] = '';
        return; // Salir de la función ya que no hay nada más que hacer
    }

    // Si $user_id no está vacío, significa que hay un usuario logueado
    // Por lo tanto, procedemos a buscar en la base de datos para determinar si es administrador
    $select_role = $conn->prepare("SELECT * FROM `usuarios` WHERE id = ?");
    $select_role->execute([$user_id]);

    $row = $select_role->fetch(PDO::FETCH_ASSOC);

    // Verificar si se encontró algún usuario con el ID proporcionado
    if ($select_role->rowCount() > 0) {
        // Si se encontró un usuario, verificamos si tiene el rol de administrador
        if ($row['role'] == 1) $_SESSION['admin_id'] = $row['id']; // Si el usuario tiene el rol de administrador, asignamos su ID a $_SESSION['admin_id']
        else $_SESSION['admin_id'] = ''; // Si no es administrador, borrar la sesión de administrador
    }

    // Si no se encontró ningún usuario con el ID proporcionado, $_SESSION['admin_id'] permanecerá vacío
}