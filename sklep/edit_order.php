<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $orderId = intval($_GET['id']);

    $query = "SELECT * FROM zamowienia WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Zamówienie o podanym ID nie istnieje.");
    }

    $order = $result->fetch_assoc();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $orderId = intval($_POST['id']);
    $status = trim($_POST['status']);
    $closed_at = trim($_POST['closed_at']);

    $query = "UPDATE zamowienia SET status = ?, closed_at = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $status, $closed_at, $orderId);

    if ($stmt->execute()) {
        header("Location: admin_dashboard_unlogged.php");
        exit;
    } else {
        die("Błąd podczas aktualizacji zamówienia: " . $conn->error);
    }
} else {
    die("Nieprawidłowe żądanie.");
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj Zamówienie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Edytuj Zamówienie</h1>
    <form action="edit_order.php" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($order['id']); ?>">
        <div class="mb-3">
            <label for="status" class="form-label">Status:</label>
            <input type="text" id="status" name="status" class="form-control" value="<?= htmlspecialchars($order['status']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="closed_at" class="form-label">Data Zamknięcia:</label>
            <input type="datetime-local" id="closed_at" name="closed_at" class="form-control" value="<?= htmlspecialchars($order['closed_at']); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Zapisz Zmiany</button>
        <a href="admin_dashboard_unlogged.php" class="btn btn-secondary">Anuluj</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
