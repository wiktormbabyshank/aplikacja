<?php
session_start();

// Logowanie danych POST dla debugowania
error_log("Dane POST otrzymane: " . print_r($_POST, true));

// Pobranie danych POST
$cartId = $_POST['cart_id'] ?? null;
$quantity = $_POST['quantity'] ?? null;

if (!$cartId || !$quantity) {
    error_log("Błąd: Brak danych cart_id lub quantity.");
    echo json_encode(['success' => false, 'message' => 'Brak danych cart_id lub quantity']);
    exit;
}

// Konwersja ilości na liczbę całkowitą
$quantity = max(1, (int)$quantity);

// Obsługa niezalogowanych użytkowników
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    if (isset($_SESSION['cart'][$cartId])) {
        $_SESSION['cart'][$cartId]['quantity'] = $quantity;
        echo json_encode(['success' => true]);
    } else {
        error_log("Błąd: Produkt o cart_id {$cartId} nie istnieje w koszyku.");
        echo json_encode(['success' => false, 'message' => 'Produkt nie istnieje w koszyku']);
    }
    exit;
}

// Dla zalogowanych użytkowników
include('db_connection.php');
$userId = $_SESSION['id'];

$query = "UPDATE koszyk SET quantity = ? WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('iii', $quantity, $cartId, $userId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    error_log("Błąd aktualizacji w bazie danych.");
    echo json_encode(['success' => false, 'message' => 'Błąd aktualizacji bazy danych']);
}
