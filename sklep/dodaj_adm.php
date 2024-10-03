<?php
$servername = "localhost"; // Zmień, jeśli masz inny serwer
$username = "root"; // Zmień, jeśli masz inny użytkownik
$password = ""; // Zmień, jeśli masz inne hasło
$dbname = "uzytkownicy_sklepu"; // Zmień na nazwę swojej bazy danych

// Tworzenie połączenia
$conn = new mysqli($servername, $username, $password, $dbname);

// Sprawdzenie połączenia
if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}

// Ustawienia dla nowego admina
$admin_email = "admin@test.com"; // Podaj email
$admin_password = "admin123"; // Podaj hasło

// Szyfrowanie hasła
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// Przygotowanie zapytania SQL
$sql = "INSERT INTO admini (email, haslo) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $admin_email, $hashed_password);

// Wykonanie zapytania
if ($stmt->execute()) {
    echo "Administrator dodany pomyślnie.";
} else {
    echo "Błąd dodawania administratora: " . $stmt->error;
}

// Zamknięcie zapytania i połączenia
$stmt->close();
$conn->close();
?>
