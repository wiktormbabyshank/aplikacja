<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $id = intval($_POST['id']);

        $stmt = $conn->prepare("DELETE FROM pages WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: admin_dashboard.php#pages");
            exit();
        } else {
            echo "Błąd podczas usuwania strony: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Nie podano ID strony do usunięcia.";
    }
} else {
    echo "Nieprawidłowe żądanie.";
}

$conn->close();
?>
