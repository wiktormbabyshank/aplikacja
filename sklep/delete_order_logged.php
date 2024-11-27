<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && ctype_digit($_POST['id'])) {
    $orderId = intval($_POST['id']);

    $query = "DELETE FROM zamowienia_users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $orderId);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php#orders_logged_users");
        exit;
    } else {
        die("Błąd podczas usuwania zamówienia: " . $conn->error);
    }
} else {
    die("Nieprawidłowe żądanie.");
}
?>
