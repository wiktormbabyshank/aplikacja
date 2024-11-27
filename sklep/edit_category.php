<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $categoryId = intval($_GET['id']);

    $query = "SELECT * FROM categories WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Kategoria o podanym ID nie istnieje.");
    }

    $category = $result->fetch_assoc();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $categoryId = intval($_POST['id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if (empty($name)) {
        die("Nazwa kategorii nie może być pusta.");
    }

    $query = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $name, $description, $categoryId);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php#categories");
        exit;
    } else {
        die("Błąd podczas aktualizacji kategorii: " . $conn->error);
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
    <title>Edytuj Kategorię</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Edytuj Kategorię</h1>
    <form action="edit_category.php" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($category['id']); ?>">
        <div class="mb-3">
            <label for="name" class="form-label">Nazwa:</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($category['name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Opis:</label>
            <textarea id="description" name="description" class="form-control"><?= htmlspecialchars($category['description']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Zapisz Zmiany</button>
        <a href="admin_dashboard.php#categories" class="btn btn-secondary">Anuluj</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
