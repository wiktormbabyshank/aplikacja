<?php

$servername = "pma.ct8.pl"; 
$username = "m50583_kacper";       
$password = "79UiZ2Vb4F4Wxiz";  
$dbname = "m50583_uzytkownicy_sklepu";  


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];


    if ($password !== $password2) {
        echo "Hasła się nie zgadzają!";
        exit();
    }

 
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

 
    $sql = "INSERT INTO uzytkownicy (imie, nazwisko, email, haslo) VALUES (?, ?, ?, ?)";


    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $imie, $nazwisko, $email, $hashed_password);


    if ($stmt->execute()) {
        echo "Rejestracja zakończona sukcesem!";

        header("Location: index.html");
    } else {
        echo "Błąd: " . $stmt->error;
    }


    $stmt->close();
}

$conn->close();
?>
