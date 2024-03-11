<?php

$username = "root";
$password = "";
$db = 'mysql:host=localhost;dbname=academia';

try {
    $conn = new PDO($db, $username, $password);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
