<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $cost = floatval($_POST['cost']);
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO delivery_methods (name, cost, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $name, $cost, $description);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php#delivery_methods");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj Metodę Dostawy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Dodaj Nową Metodę Dostawy</h1>
    <form method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Nazwa</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="cost" class="form-label">Koszt</label>
            <input type="number" step="0.01" class="form-control" id="cost" name="cost" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Opis</label>
            <textarea class="form-control" id="description" name="description" rows="5"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Dodaj</button>
        <a href="admin_dashboard.php#delivery_methods" class="btn btn-secondary">Anuluj</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
