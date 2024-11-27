<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Użytkownik niezalogowany']);
    exit;
}

$user_id = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);
$item_id = $data['item_id'] ?? null;

if (!$item_id) {
    echo json_encode(['success' => false, 'message' => 'Brak ID przedmiotu']);
    exit;
}

$query = "DELETE FROM ulubione WHERE user_id = ? AND item_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $user_id, $item_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Nie udało się usunąć z ulubionych: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
