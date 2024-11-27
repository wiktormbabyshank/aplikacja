<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Przypisz kategorie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h1 class="text-center mb-4">Przypisz kategorie do produktu</h1>
    <form action="assign_categories_process.php" method="post" class="bg-white p-4 rounded shadow-sm">
        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($_GET['product_id']); ?>">
        <?php
        include('db_connection.php');
        $categories = $conn->query("SELECT * FROM categories");
        while ($category = $categories->fetch_assoc()) {
            echo "<div class='form-check'>";
            echo "<input type='checkbox' class='form-check-input' id='cat_{$category['id']}' name='categories[]' value='{$category['id']}'>";
            echo "<label for='cat_{$category['id']}' class='form-check-label'>" . htmlspecialchars($category['name']) . "</label>";
            echo "</div>";
        }
        ?>
        <button type="submit" class="btn btn-primary w-100">Zapisz kategorie</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
