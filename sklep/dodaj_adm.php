<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "uzytkownicy_sklepu"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}

$admin_email = "admin@test.com"; 
$admin_password = "admin123"; 

$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

$sql = "INSERT INTO admini (email, haslo) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $admin_email, $hashed_password);

if ($stmt->execute()) {
    echo "Administrator dodany pomyślnie.";
} else {
    echo "Błąd dodawania administratora: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
