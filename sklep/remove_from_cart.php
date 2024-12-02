<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = $_POST['cart_id'] ?? null;

    error_log("Dane przesłane do remove_from_cart.php: " . print_r($_POST, true));

    if ($cart_id === null) {
        echo json_encode(['success' => false, 'message' => 'Brak danych o produkcie do usunięcia.']);
        exit;
    }

    $isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

    if ($isLoggedIn) {
        $stmt = $conn->prepare("DELETE FROM koszyk WHERE id = ?");
        $stmt->bind_param('i', $cart_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Błąd podczas usuwania produktu z koszyka.']);
        }
        $stmt->close();
    } else {
        if (isset($_SESSION['cart'][$cart_id])) {
            unset($_SESSION['cart'][$cart_id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => "Produkt z kluczem $cart_id nie istnieje w koszyku."]);
        }
    }
}
?>