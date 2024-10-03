<?php
// Dane do połączenia z bazą danych
$servername = "localhost";
$username = "root"; // Domyślnie root dla lokalnego MySQL
$password = "";     // Zostaw puste, jeśli nie masz hasła
$dbname = "uzytkownicy_sklepu";  // Zmień na nazwę swojej bazy danych

// Utwórz połączenie
$conn = new mysqli($servername, $username, $password, $dbname);

// Sprawdź połączenie
if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}

// Sprawdź, czy formularz został przesłany
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    // Sprawdzenie czy hasła się zgadzają
    if ($password !== $password2) {
        echo "Hasła się nie zgadzają!";
        exit();
    }

    // Hashowanie hasła przed zapisem do bazy
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Przygotowanie i wykonanie zapytania SQL do bazy danych
    $sql = "INSERT INTO uzytkownicy (imie, nazwisko, email, haslo) VALUES (?, ?, ?, ?)";

    // Przygotuj zapytanie
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $imie, $nazwisko, $email, $hashed_password);

    // Wykonaj zapytanie
    if ($stmt->execute()) {
        echo "Rejestracja zakończona sukcesem!";
        // Możesz tutaj przekierować na stronę logowania
        header("Location: index.html");
    } else {
        echo "Błąd: " . $stmt->error;
    }

    // Zamknij połączenie
    $stmt->close();
}

$conn->close();
?>
