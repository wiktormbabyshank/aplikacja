<?php
$servername = "pma.ct8.pl"; 
$username = "m50583_kacper";       
$password = "79UiZ2Vb4F4Wxiz";  
$dbname = "m50583_uzytkownicy_sklepu";   


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}


$email = "admin@sklep.com";
$plainPassword = "admin123";
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);


$sql = "INSERT INTO admini (email, haslo) VALUES ('$email', '$hashedPassword')";

if ($conn->query($sql) === TRUE) {
    echo "Nowy administrator został dodany.";
} else {
    echo "Błąd: " . $conn->error;
}

$conn->close();
?>