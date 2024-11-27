<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && ctype_digit($_POST['id'])) {
    $categoryId = intval($_POST['id']);

    $query = "DELETE FROM categories WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $categoryId);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php#categories");
        exit;
    } else {
        die("Błąd podczas usuwania kategorii: " . $conn->error);
    }
} else {
    die("Nieprawidłowe żądanie.");
}
