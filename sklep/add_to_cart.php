<?php
session_start();
include('db_connection.php');

$data = json_decode(file_get_contents('php://input'), true);
$item_id = $data['item_id'] ?? null;
$quantity = $data['quantity'] ?? 1;

if (!$item_id || $quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe dane']);
    exit;
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $found = false;
    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['product_id'] === $item_id) {
            $_SESSION['cart'][$index]['quantity'] += $quantity;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['cart'][] = [
            'product_id' => $item_id,
            'quantity' => $quantity,
        ];
    }

    echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
    exit;
}

$user_id = $_SESSION['id'];

$checkQuery = "SELECT id FROM koszyk WHERE user_id = ? AND item_id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param('ii', $user_id, $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $updateQuery = "UPDATE koszyk SET quantity = quantity + ? WHERE user_id = ? AND item_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('iii', $quantity, $user_id, $item_id);
} else {
    $insertQuery = "INSERT INTO koszyk (user_id, item_id, quantity, added_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param('iii', $user_id, $item_id, $quantity);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Błąd: ' . $stmt->error]);
}
?>
