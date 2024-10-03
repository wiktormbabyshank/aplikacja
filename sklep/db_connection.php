<?php

$servername = "localhost"; 
$username = "root";       
$password = "";  
$dbname = "uzytkownicy_sklepu";   

$conn = new mysqli($localhost, $root, "", $uzytkownicy_sklepu);

if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
} 
echo "Połączenie z bazą danych zostało nawiązane.";

$conn->close();
?>