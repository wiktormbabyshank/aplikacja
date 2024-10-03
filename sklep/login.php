<?php
session_start(); // Rozpocznij sesję

$servername = "localhost"; // Zmień, jeśli masz inny serwer
$username = "root"; // Zmień, jeśli masz innego użytkownika
$password = ""; // Zmień, jeśli masz inne hasło
$dbname = "uzytkownicy_sklepu"; // Zmień na nazwę swojej bazy danych

// Tworzenie połączenia
$conn = new mysqli($servername, $username, $password, $dbname);

// Sprawdzenie połączenia
if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}

// Sprawdzenie, czy formularz został wysłany
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Zapytanie do bazy danych
    $sql = "SELECT * FROM uzytkownicy WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc(); // Pobierz dane użytkownika

        // Sprawdzenie hasła
        if (password_verify($password, $user['haslo'])) { // Użyj password_verify do weryfikacji hasła
            // Zalogowany pomyślnie
            $_SESSION['loggedin'] = true; // Ustaw flagę zalogowania
            $_SESSION['imie'] = $user['imie']; // Ustaw imię
            $_SESSION['email'] = $user['email']; // Ustaw email
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Błędny email lub hasło";
        }
    } else {
        echo "Błędny email lub hasło";
    }
}

$conn->close();
?>
