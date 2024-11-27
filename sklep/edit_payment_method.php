<?php
session_start();
include('db_connection.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM payment_methods WHERE id = $id");
    $method = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE payment_methods SET name = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $description, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php#payments");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj Metodę Płatności</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Edytuj Sposób Płatności</h1>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $method['id']; ?>">
        <div class="mb-3">
            <label for="name" class="form-label">Nazwa</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($method['name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Opis</label>
            <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($method['description']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
        <a href="admin_dashboard.php#payments" class="btn btn-secondary">Anuluj</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
