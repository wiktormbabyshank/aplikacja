<?php
session_start(); 
include('db_connection.php');

if (isset($_SESSION['cart'])) {
    unset($_SESSION['cart']);
}

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
}

session_unset();
session_destroy();

header("Location: dashboard.php");
exit();
?>
