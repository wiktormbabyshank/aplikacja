<?php
session_start();
include('db_connection.php');


if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.html");
    exit;
}

$user_id = $_SESSION['id']; 


$query = "
    SELECT 
        koszyk.id AS cart_id,
        products.id AS product_id,
        products.name,
        products.price,
        products.quantity,
        GROUP_CONCAT(product_images.image_path) AS images,
        koszyk.added_at
    FROM koszyk
    INNER JOIN products ON koszyk.item_id = products.id
    LEFT JOIN product_images ON products.id = product_images.product_id
    WHERE koszyk.user_id = ?
    GROUP BY koszyk.id
    ORDER BY koszyk.added_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koszyk</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .product-card {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .btn-remove {
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
        }
        .btn-remove:hover {
            background-color: #c82333;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
        }
        .btn-back:hover {
            background-color: #5a6268;
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
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="cart.php"><i class="fas fa-shopping-cart"></i> Koszyk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user_edit.php">Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-danger" href="logout.php">Wyloguj się</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <div class="container">
        <h1 class="text-center mb-4">Twój Koszyk</h1>

        <div class="mb-4 text-center">
            <a href="dashboard.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Wróć do sklepu
            </a>
        </div>

        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                        $images = explode(',', $row['images']);
                        $main_image = $images[0] ?? 'default.jpg';
                    ?>
                    <div class="col-md-12 mb-3">
                        <div class="card product-card p-3">
                            <div class="row g-0">
                                <div class="col-md-2">
                                    <img src="<?= htmlspecialchars($main_image) ?>" class="img-fluid rounded-start" alt="Product Image">
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                                        <p class="card-text">
                                            Cena: <strong><?= htmlspecialchars($row['price']) ?> zł</strong><br>
                                            Ilość dostępna: <?= htmlspecialchars($row['quantity']) ?><br>
                                            Dodano do koszyka: <?= htmlspecialchars($row['added_at']) ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-center justify-content-center">
                                    <form action="remove_from_cart.php" method="POST">
                                        <input type="hidden" name="cart_id" value="<?= htmlspecialchars($row['cart_id']) ?>">
                                        <button type="submit" class="btn btn-remove">Usuń</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-muted">Twój koszyk jest pusty.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
