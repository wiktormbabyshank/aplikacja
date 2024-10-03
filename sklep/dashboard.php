<?php
session_start();

// Sprawdź, czy użytkownik jest zalogowany
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

// Wyświetlenie powitania
echo "<h1>Witaj, " . htmlspecialchars($_SESSION['imie']) . "!</h1>";
echo "<p>Zalogowany jako: " . htmlspecialchars($_SESSION['email']) . "</p>";
?>

<!-- Przycisk wylogowania -->
<form action="logout.php" method="post">
    <button type="submit">Wyloguj się</button>
</form>
