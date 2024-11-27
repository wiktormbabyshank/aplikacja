<?php
session_start();
include('db_connection.php');

if (!isset($_GET['product_id'])) {
    echo "<p>Nieprawidłowy identyfikator produktu.</p>";
    exit;
}

$product_id = intval($_GET['product_id']);

$query_product = "SELECT * FROM products WHERE id = ?";
$stmt_product = $conn->prepare($query_product);
$stmt_product->bind_param("i", $product_id);
$stmt_product->execute();
$result_product = $stmt_product->get_result();

if ($result_product->num_rows === 0) {
    echo "<p>Produkt nie został znaleziony.</p>";
    exit;
}

$product = $result_product->fetch_assoc();

$query_images = "SELECT image_path FROM product_images WHERE product_id = ?";
$stmt_images = $conn->prepare($query_images);
$stmt_images->bind_param("i", $product_id);
$stmt_images->execute();
$result_images = $stmt_images->get_result();
$images = $result_images->fetch_all(MYSQLI_ASSOC);

$query_categories = "
    SELECT categories.name 
    FROM categories 
    INNER JOIN product_categories ON categories.id = product_categories.category_id 
    WHERE product_categories.product_id = ?";
$stmt_categories = $conn->prepare($query_categories);
$stmt_categories->bind_param("i", $product_id);
$stmt_categories->execute();
$result_categories = $stmt_categories->get_result();
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);

$query_parameters = "
    SELECT parameters.name, parameters.unit, product_parameters.value 
    FROM parameters 
    INNER JOIN product_parameters ON parameters.id = product_parameters.parameter_id 
    WHERE product_parameters.product_id = ?";
$stmt_parameters = $conn->prepare($query_parameters);
$stmt_parameters->bind_param("i", $product_id);
$stmt_parameters->execute();
$result_parameters = $stmt_parameters->get_result();
$parameters = $result_parameters->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szczegóły produktu</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand {
            font-weight: bold;
        }
        .product-card img {
            object-fit: cover; 
            width: 100%; 
            border-radius: 10px;
        }
        .product-details {
            margin-top: 20px;
        }
        .product-info h2, .product-info h3, .product-info h4 {
            color: #333;
        }
        .category-list, .parameter-list {
            margin-top: 20px;
        }
        .btn-order {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            width: 100%;
            margin-bottom: 20px;
        }
        .btn-order:hover {
            background-color: #0056b3;
        }
        .btn-back {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            transition: background-color 0.3s;
            margin-top: 10px;
        }
        .btn-back:hover {
            background-color: #0056b3;
        }
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            filter: invert(100%);
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">PoopAndYou</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <?php
                $pagesResult = $conn->query("SELECT title, slug FROM pages");
                while ($page = $pagesResult->fetch_assoc()) {
                    echo "<li class='nav-item'><a class='nav-link' href='page.php?slug=" . htmlspecialchars($page['slug']) . "'>" . htmlspecialchars($page['title']) . "</a></li>";
                }
                ?>
            
        </div>
    </div>
</nav>

<div class="container">
    <div class="product-details card p-4">
        <div class="row">
            <div class="col-md-6">
                <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php foreach ($images as $index => $image): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <img src="<?= htmlspecialchars($image['image_path']); ?>" class="d-block w-100" alt="Product Image">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="product-info">
                    <h2><?= htmlspecialchars($product['name']); ?></h2>
                    <h3>Cena: <?= htmlspecialchars($product['price']); ?> zł</h3>
                    <h4>Dostępna ilość: <?= htmlspecialchars($product['quantity']); ?></h4>
                    <form action="place_order.php" method="get">
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id); ?>">
                        <button type="submit" class="btn-order">Złóż zamówienie</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="category-list">
            <h5>Kategorie:</h5>
            <ul>
                <?php foreach ($categories as $category): ?>
                    <li><?= htmlspecialchars($category['name']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="parameter-list">
            <h5>Parametry:</h5>
            <ul>
                <?php foreach ($parameters as $parameter): ?>
                    <li><?= htmlspecialchars($parameter['name']); ?>: <?= htmlspecialchars($parameter['value']); ?> <?= htmlspecialchars($parameter['unit']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
