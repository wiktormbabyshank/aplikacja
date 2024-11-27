<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj kategorię</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h1 class="text-center mb-4">Dodaj kategorię</h1>
    <form action="add_category_process.php" method="post" class="bg-white p-4 rounded shadow-sm">
        <div class="mb-3">
            <label for="name" class="form-label">Nazwa kategorii:</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Opis:</label>
            <textarea id="description" name="description" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary w-100">Dodaj kategorię</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
