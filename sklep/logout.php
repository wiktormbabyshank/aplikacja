<?php
session_start(); 
include('db_connection.php');
session_unset();
session_destroy();
header("Location: index.html");
exit();
?>
