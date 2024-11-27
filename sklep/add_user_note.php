<?php
session_start();
include('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = intval($_POST['user_id']);
    $note = trim($_POST['note']);

    if (!empty($note)) {
        $stmt = $conn->prepare("INSERT INTO user_notes (user_id, note) VALUES (?, ?)");
        $stmt->bind_param("is", $userId, $note);

        if ($stmt->execute()) {
            header("Location: edit_user.php?id=$userId");
            exit();
        } else {
            echo "Błąd podczas dodawania notatki: " . $conn->error;
        }
    } else {
        echo "Notatka nie może być pusta.";
    }
}
?>
