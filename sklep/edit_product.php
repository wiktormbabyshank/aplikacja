<?php
session_start();
include('db_connection.php');

if (isset($_GET['id'])) {
    $productId = $_GET['id'];
    $result = $conn->query("SELECT * FROM products WHERE id = $productId");
    $product = $result->fetch_assoc();

    $imagesResult = $conn->query("SELECT * FROM product_images WHERE product_id = $productId");
    $productImages = $imagesResult->fetch_all(MYSQLI_ASSOC);

    $assignedCategoriesResult = $conn->query("SELECT category_id FROM product_categories WHERE product_id = $productId");
    $assignedCategories = array_column($assignedCategoriesResult->fetch_all(MYSQLI_ASSOC), 'category_id');

    $assignedParametersResult = $conn->query("SELECT parameter_id, value FROM product_parameters WHERE product_id = $productId");
    $assignedParameters = [];
    while ($row = $assignedParametersResult->fetch_assoc()) {
        $assignedParameters[$row['parameter_id']] = $row['value'];
    }

    $categoriesResult = $conn->query("SELECT * FROM categories");
    $parametersResult = $conn->query("SELECT * FROM parameters");
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj Produkt</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container-edit {
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
            background-color: #218838;
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
        .image-preview {
            max-width: 100px;
            margin: 5px;
        }
    </style>
</head>

<body>
    <div class="container container-edit">
        <h1>Edytuj Produkt</h1>
        <form action="update_product.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
            
            <div class="mb-3">
                <label for="product_name" class="form-label">Nazwa produktu</label>
                <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="product_price" class="form-label">Cena produktu (PLN)</label>
                <input type="number" class="form-control" id="product_price" name="product_price" value="<?php echo htmlspecialchars($product['price']); ?>" required step="0.01">
            </div>
            
            <div class="mb-3">
                <label for="product_quantity" class="form-label">Ilość</label>
                <input type="number" class="form-control" id="product_quantity" name="product_quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="product_images" class="form-label">Dodaj nowe obrazy produktu</label>
                <input type="file" class="form-control" id="product_images" name="product_images[]" multiple>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Istniejące obrazy</label>
                <div>
                    <?php foreach ($productImages as $image): ?>
                        <div>
                            <img src="<?php echo htmlspecialchars($image['image_path']); ?>" class="image-preview" alt="Produkt">
                            <label>
                                <input type="checkbox" name="delete_images[]" value="<?php echo htmlspecialchars($image['id']); ?>">
                                Usuń
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Kategorie produktu</label>
                <div>
                    <?php while ($category = $categoriesResult->fetch_assoc()): ?>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="cat_<?php echo $category['id']; ?>" name="categories[]" value="<?php echo $category['id']; ?>" 
                                <?php echo in_array($category['id'], $assignedCategories) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="cat_<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </label>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Parametry produktu</label>
                <div>
                    <?php while ($parameter = $parametersResult->fetch_assoc()): ?>
                        <div class="mb-2">
                            <label for="param_<?php echo $parameter['id']; ?>" class="form-label"><?php echo htmlspecialchars($parameter['name']); ?> (<?php echo htmlspecialchars($parameter['unit']); ?>)</label>
                            <input type="text" class="form-control" id="param_<?php echo $parameter['id']; ?>" name="parameters[<?php echo $parameter['id']; ?>]" 
                                   value="<?php echo htmlspecialchars($assignedParameters[$parameter['id']] ?? ''); ?>">
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <button type="submit" class="btn-update">Zaktualizuj produkt</button>
        </form>

        <a href="admin_dashboard.php" class="back-button">Powrót do panelu</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
