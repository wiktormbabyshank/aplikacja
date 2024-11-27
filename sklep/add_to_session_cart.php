<?php
session_start();

$data = json_decode(file_get_contents('php://input'), true);
$item_id = $data['item_id'] ?? null;

if (!$item_id) {
    echo json_encode(['success' => false, 'message' => 'Brak ID przedmiotu']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!in_array($item_id, $_SESSION['cart'])) {
    $_SESSION['cart'][] = $item_id;
    echo json_encode(['success' => true, 'message' => 'Produkt został dodany do koszyka']);
} else {
    echo json_encode(['success' => false, 'message' => 'Produkt już znajduje się w koszyku']);
}
