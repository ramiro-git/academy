<?php

include("db.php");

session_start();

if (!isset($_SESSION['user_id'])) {
    $user_id = '';
} else {
    $user_id = $_SESSION['user_id'];
};
