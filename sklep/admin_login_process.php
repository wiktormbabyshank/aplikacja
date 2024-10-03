<?php
session_start(); // Rozpocznij sesję

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

// Sprawdzenie, czy formularz został wysłany
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Przygotowanie zapytania SQL
    $sql = "SELECT * FROM admini WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        // Weryfikacja hasła
        if (password_verify($password, $admin['haslo'])) {
            // Zalogowany pomyślnie
            $_SESSION['admin_email'] = $admin['email']; // Zapisz email w sesji
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "Błędny email lub hasło";
        }
    } else {
        echo "Błędny email lub hasło";
    }

    $stmt->close(); // Zamknięcie zapytania
}

$conn->close(); // Zamknięcie połączenia
?>
