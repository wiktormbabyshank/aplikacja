<?php
session_start();
include('db_connection.php');

$categoriesResult = $conn->query("SELECT * FROM categories");
$parametersResult = $conn->query("SELECT * FROM parameters");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['product_name'], $_POST['product_price'], $_POST['product_quantity'], $_POST['categories'], $_POST['parameters'])) {
        $name = trim($_POST['product_name']);
        $price = (float)$_POST['product_price'];
        $quantity = (int)$_POST['product_quantity'];
        $categories = $_POST['categories'];
        $parameters = $_POST['parameters'];

        if (empty($name) || $price <= 0 || $quantity <= 0 || empty($categories)) {
            echo "Brak wymaganych danych!";
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO products (name, price, quantity) VALUES (?, ?, ?)");
        if (!$stmt) {
            die("Błąd przygotowania zapytania: " . $conn->error);
        }
        
        $stmt->bind_param("sdi", $name, $price, $quantity);
        $stmt->execute();
        $productId = $stmt->insert_id;
        $stmt->close();

        $stmtCategory = $conn->prepare("INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)");
        foreach ($categories as $categoryId) {
            $categoryId = intval($categoryId);
            $stmtCategory->bind_param("ii", $productId, $categoryId);
            $stmtCategory->execute();
        }
        $stmtCategory->close();

        $stmtParameter = $conn->prepare("INSERT INTO product_parameters (product_id, parameter_id, value) VALUES (?, ?, ?)");
        foreach ($parameters as $parameterId => $value) {
            $parameterId = intval($parameterId);
            $value = trim($value);
            if (!empty($value)) {
                $stmtParameter->bind_param("iis", $productId, $parameterId, $value);
                $stmtParameter->execute();
            }
        }
        $stmtParameter->close();

        if (isset($_FILES['product_images']) && $_FILES['product_images']['error'][0] == UPLOAD_ERR_OK) {
            foreach ($_FILES['product_images']['tmp_name'] as $key => $tmpName) {
                $imageName = basename($_FILES['product_images']['name'][$key]);
                $targetPath = "uploads/" . uniqid() . "_" . $imageName;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    $imageStmt = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
                    if ($imageStmt) {
                        $imageStmt->bind_param("is", $productId, $targetPath);
                        $imageStmt->execute();
                        $imageStmt->close();
                    } else {
                        echo "Błąd przygotowania zapytania dla obrazu: " . $conn->error;
                    }
                } else {
                    echo "Błąd podczas przesyłania pliku: $imageName";
                }
            }
        }

        header("Location: admin_dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj Produkt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-brand {
            font-weight: bold;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            max-width: 600px;
            margin: 0 auto;
        }
        .form-header {
            font-weight: bold;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .back-button {
            margin-top: 20px;
            display: inline-block;
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
        }
        .back-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">PoopAndYou</a>
    </div>
</nav>

<div class="container">
    <div class="form-container">
        <h2 class="form-header text-center">Dodaj Nowy Produkt</h2>
        <form action="add_product.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="product_name" class="form-label">Nazwa produktu</label>
                <input type="text" class="form-control" id="product_name" name="product_name" placeholder="Nazwa produktu" required>
            </div>
            <div class="mb-3">
                <label for="product_price" class="form-label">Cena produktu</label>
                <input type="number" class="form-control" id="product_price" name="product_price" placeholder="Cena produktu" required step="0.01">
            </div>
            <div class="mb-3">
                <label for="product_quantity" class="form-label">Ilość</label>
                <input type="number" class="form-control" id="product_quantity" name="product_quantity" placeholder="Ilość" required>
            </div>
            <div class="mb-3">
                <label for="categories" class="form-label">Kategorie produktu</label>
                <div>
                    <?php while ($category = $categoriesResult->fetch_assoc()): ?>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="cat_<?php echo $category['id']; ?>" name="categories[]" value="<?php echo $category['id']; ?>">
                            <label for="cat_<?php echo $category['id']; ?>" class="form-check-label"><?php echo htmlspecialchars($category['name']); ?></label>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <div class="mb-3">
                <label for="parameters" class="form-label">Parametry produktu</label>
                <div>
                    <?php while ($parameter = $parametersResult->fetch_assoc()): ?>
                        <div class="mb-2">
                            <label for="param_<?php echo $parameter['id']; ?>" class="form-label"><?php echo htmlspecialchars($parameter['name']); ?><?php echo $parameter['unit'] ? " ({$parameter['unit']})" : ''; ?></label>
                            <input type="text" class="form-control" id="param_<?php echo $parameter['id']; ?>" name="parameters[<?php echo $parameter['id']; ?>]" placeholder="Wprowadź wartość">
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <div class="mb-3">
                <label for="product_images" class="form-label">Obrazki produktu</label>
                <input type="file" class="form-control" id="product_images" name="product_images[]" multiple required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Dodaj nowy produkt</button>
        </form>
        <a href="admin_dashboard.php" class="back-button">Powrót do panelu</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
