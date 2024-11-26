<?php
session_start();
include('db_connection.php');

// Sprawdzamy, czy użytkownik jest zalogowany
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.html");
    exit;
}

// Pobieranie danych użytkownika z bazy
$user_id = $_SESSION['id']; // ID użytkownika zapisane w sesji
$query = "SELECT * FROM uzytkownicy WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Użytkownik nie istnieje.";
    exit;
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta name="viewport" charset="UTF-8" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="stopka2">
        <h2>Edytuj swoje dane</h2>
        <form action="user_edit.php" method="post">
            <label for="imie">Imię:</label>
            <input type="text" id="imie" name="imie" value="<?php echo htmlspecialchars($user['imie']); ?>" required><br><br>

            <label for="nazwisko">Nazwisko:</label>
            <input type="text" id="nazwisko" name="nazwisko" value="<?php echo htmlspecialchars($user['nazwisko']); ?>" required><br><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br><br>

            <label for="adres">Adres:</label>
            <input type="text" id="adres" name="adres" value="<?php echo htmlspecialchars($user['adres']); ?>"><br><br>

            <label for="telefon">Telefon:</label>
            <input type="text" id="telefon" name="telefon" value="<?php echo htmlspecialchars($user['telefon']); ?>"><br><br>

            <button type="submit" name="submit">Zapisz zmiany</button>
        </form>
    </div>

    <?php
    // Przetwarzanie formularza
    if (isset($_POST['submit'])) {
        $imie = $_POST['imie'];
        $nazwisko = $_POST['nazwisko'];
        $email = $_POST['email'];
        $adres = $_POST['adres'];
        $telefon = $_POST['telefon'];

        // Zaktualizowanie danych w bazie
        $update_query = "UPDATE uzytkownicy SET imie = ?, nazwisko = ?, email = ?, adres = ?, telefon = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("sssssi", $imie, $nazwisko, $email, $adres, $telefon, $user_id);

        if ($update_stmt->execute()) {
            echo "<p>Dane zostały zaktualizowane pomyślnie.</p>";
        } else {
            echo "<p>Wystąpił błąd przy aktualizacji danych.</p>";
        }
    }
    ?>
</body>
</html>
