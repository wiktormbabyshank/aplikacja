<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.html");
    exit;
}

$user_id = $_SESSION['id'];
$cart_id = $_POST['cart_id'] ?? null;

if (!$cart_id) {
    header("Location: cart.php");
    exit;
}

$query = "DELETE FROM koszyk WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $cart_id, $user_id);

if ($stmt->execute()) {
    header("Location: cart.php");
} else {
    echo "Błąd usuwania przedmiotu z koszyka.";
}

$stmt->close();
$conn->close();
?>
