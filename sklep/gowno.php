<?php
include('db_connection.php');


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