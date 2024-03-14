<?php

// Función para verificar si el usuario es administrador
function isAdmin($user_id, $conn)
{
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

function formatSizeUnits($bytes)
{
    // Convertir bytes a MB
    $mb = $bytes / (1024 * 1024);
    // Formatear el resultado con 5 decimales y agregar 'MB'
    return number_format($mb, 5) . ' MB';
}