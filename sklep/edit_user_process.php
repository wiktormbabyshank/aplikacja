<?php
session_start();
include 'db_connection.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $userId = isset($_POST['id']) ? intval($_POST['id']) : null;
    $firstName = isset($_POST['imie']) ? $_POST['imie'] : null;
    $lastName = isset($_POST['nazwisko']) ? $_POST['nazwisko'] : null;
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    $status = isset($_POST['status']) ? $_POST['status'] : null;

    if ($userId === null || $firstName === null || $lastName === null || $email === null || $status === null) {
        die("Brak wymaganych danych w formularzu.");
    }

    try {
        $sql = "UPDATE uzytkownicy SET imie = ?, nazwisko = ?, email = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $firstName, $lastName, $email, $status, $userId);

        if ($stmt->execute()) {
            echo "Użytkownik został zaktualizowany.";
            header("Location: admin_dashboard.php"); 
            exit();
        } else {
            throw new Exception("Nie udało się zaktualizować użytkownika.");
        }
    } catch (Exception $e) {
        echo "Wystąpił błąd podczas aktualizacji: " . $e->getMessage();
    }

    $stmt->close();
    $conn->close();

} else {
    echo "Nieprawidłowe żądanie.";
}
?>
