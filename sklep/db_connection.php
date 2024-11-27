<?php

$servername = "localhost"; 
$username = "root";  
$password = ""; 
$dbname = "m50583_uzytkownicy_sklepu"; 


$conn = new mysqli($servername, $username, $password, $dbname); 


if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}



?>
