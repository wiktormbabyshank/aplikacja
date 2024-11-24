<?php
session_start();
include 'db_connection.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $orderId = isset($_POST['id']) ? intval($_POST['id']) : null;
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : null;
    $status = isset($_POST['status']) ? trim($_POST['status']) : null;

    if ($orderId === null || $amount === null || !$status) {
        die("Brak wymaganych danych w formularzu.");
    }

    $sql = "UPDATE zamowienia SET amount = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("dsi", $amount, $status, $orderId);

        if ($stmt->execute()) {

            header("Location: admin_dashboard.php?message=Zamówienie zaktualizowane");
            exit();
        } else {
            echo "Wystąpił błąd podczas aktualizacji: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Błąd zapytania: " . $conn->error;
    }

    $conn->close();
} else {
    echo "Nieprawidłowe żądanie.";
}
?>
