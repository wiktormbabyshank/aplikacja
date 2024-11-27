<?php
session_start();
include('db_connection.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM pages WHERE id = $id");
    $page = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $title = $_POST['title'];
    $content = $_POST['content'];
    $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($_POST['title'])));

    $stmt = $conn->prepare("UPDATE pages SET title = ?, content = ?, slug = ? WHERE id = ?");
    $stmt->bind_param("sssi", $title, $content, $slug, $id);
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
    <title>Edytuj Podstronę</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Edytuj Podstronę</h1>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $page['id']; ?>">
        <div class="mb-3">
            <label for="title" class="form-label">Tytuł</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($page['title']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Treść</label>
            <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($page['content']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
        <a href="admin_dashboard.php#pages" class="btn btn-secondary">Anuluj</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
