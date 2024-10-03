<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

echo "<h1>Witaj, " . htmlspecialchars($_SESSION['imie']) . "!</h1>";
echo "<p>Zalogowany jako: " . htmlspecialchars($_SESSION['email']) . "</p>";
?>

<form action="logout.php" method="post">
    <button type="submit">Wyloguj się</button>
</form>
