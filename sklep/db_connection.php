<?php

$servername = "pma.ct8.pl"; 
$username = "m50583_kacper";  
$password = "79UiZ2Vb4F4Wxiz"; 
$dbname = "m50583_uzytkownicy_sklepu"; 


$conn = new mysqli($servername, $username, $password, $dbname); 


if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}



?>
