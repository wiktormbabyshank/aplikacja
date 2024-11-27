<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Przypisz parametry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h1 class="text-center mb-4">Przypisz parametry do produktu</h1>
    <form action="assign_parameters_process.php" method="post" class="bg-white p-4 rounded shadow-sm">
        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($_GET['product_id']); ?>">
        <?php
        include('db_connection.php');
        $parameters = $conn->query("SELECT * FROM parameters");
        while ($parameter = $parameters->fetch_assoc()) {
            echo "<div class='mb-3'>";
            echo "<label for='param_{$parameter['id']}' class='form-label'>" . htmlspecialchars($parameter['name']) . " (" . htmlspecialchars($parameter['unit']) . ")</label>";
            echo "<input type='text' id='param_{$parameter['id']}' name='parameters[{$parameter['id']}]' class='form-control'>";
            echo "</div>";
        }
        ?>
        <button type="submit" class="btn btn-primary w-100">Zapisz parametry</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>