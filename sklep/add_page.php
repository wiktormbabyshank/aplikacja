<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($_POST['title'])));

    $stmt = $conn->prepare("INSERT INTO pages (title, content, slug) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $content, $slug);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php#pages");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj Podstronę</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Dodaj Nową Podstronę</h1>
    <form method="POST">
        <div class="mb-3">
            <label for="title" class="form-label">Tytuł</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Treść</label>
            <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Dodaj Podstronę</button>
        <a href="admin_dashboard.php#pages" class="btn btn-secondary">Anuluj</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
