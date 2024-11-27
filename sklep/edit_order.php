<?php
session_start();
include 'db_connection.php'; 

if (isset($_GET['id'])) {
    $orderId = intval($_GET['id']); 
    $result = $conn->query("SELECT * FROM zamowienia WHERE id = $orderId");

    if ($result->num_rows === 0) {
        die("Zamówienie nie zostało znalezione.");
    }
    $order = $result->fetch_assoc();
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container-edit-order {
            margin-top: 50px;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-control {
            margin-bottom: 15px;
        }
        .btn-update {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
        }
        .btn-update:hover {
            background-color: #0056b3;
        }
        .back-button {
            display: block;
            text-align: center;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>

<body>
    <div class="container container-edit-order">
        <h1>Edytuj Zamówienie</h1>
        <form action="edit_order_process.php" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($order['id']); ?>">

            <div class="mb-3">
                <label for="user" class="form-label">Użytkownik:</label>
                <input type="text" id="user" class="form-control" value="<?php echo isset($order['imie']) ? htmlspecialchars($order['imie']) : 'Nie znaleziono użytkownika'; ?>" disabled>
            </div>

            <div class="mb-3">
                <label for="amount" class="form-label">Kwota:</label>
                <input type="number" id="amount" class="form-control" name="amount" value="<?php echo htmlspecialchars($order['amount']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status:</label>
                <select id="status" name="status" class="form-select">
                    <option value="Pending" <?php echo ($order['status'] == 'Pending') ? 'selected' : ''; ?>>W trakcie realizacji</option>
                    <option value="Completed" <?php echo ($order['status'] == 'Completed') ? 'selected' : ''; ?>>Zrealizowane</option>
                </select>
            </div>

            <button type="submit" class="btn-update">Aktualizuj Zamówienie</button>
        </form>

        <a href="admin_dashboard.php" class="back-button">Powrót do panelu</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
