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
        products.id, 
        products.name, 
        products.price, 
        GROUP_CONCAT(product_images.image_path) AS images
    FROM ulubione
    INNER JOIN products ON ulubione.item_id = products.id
    LEFT JOIN product_images ON products.id = product_images.product_id
    WHERE ulubione.user_id = ?
    GROUP BY products.id
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ulubione</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        .product-card img {
            height: 200px;
            object-fit: cover;
        }
        .product-card {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .navbar-brand {
            font-weight: bold;
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
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <i class="fas fa-shopping-cart"></i> Koszyk
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="ulubione.php">
                        <i class="fas fa-heart"></i> Ulubione
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="user_edit.php">Profil</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-danger logout-btn" href="logout.php">Wyloguj się</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1 class="text-center mb-4">Twoje Ulubione Produkty</h1>
    <div class="row">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $images = explode(',', $row['images']);
                $main_image = $images[0] ?? 'default.jpg';

                echo "<div class='col-md-4 col-lg-3 mb-4'>";
                echo "<div class='card product-card'>";
                echo "<img src='" . htmlspecialchars($main_image) . "' class='card-img-top' alt='Product Image'>";
                echo "<div class='card-body'>";
                echo "<h5 class='card-title'>" . htmlspecialchars($row['name']) . "</h5>";
                echo "<p class='card-text'><strong>" . htmlspecialchars($row['price']) . " zł</strong></p>";

                echo "<div class='d-flex justify-content-between align-items-center'>";

                echo "<form action='product_page.php' method='GET'>";
                echo "<input type='hidden' name='product_id' value='" . htmlspecialchars($row['id']) . "'>";
                echo "<button type='submit' class='btn btn-success'>Kup</button>";
                echo "</form>";

                echo "<button class='btn btn-primary add-to-cart-btn' data-item-id='" . htmlspecialchars($row['id']) . "'>Do koszyka</button>";

                echo "</div>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p class='text-center text-muted'>Nie masz jeszcze ulubionych produktów.</p>";
        }
        ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cartButtons = document.querySelectorAll('.add-to-cart-btn');

    cartButtons.forEach(button => {
        button.addEventListener('click', function () {
            const itemId = this.getAttribute('data-item-id');
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ item_id: itemId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Dodano do koszyka!');
                } else {
                    alert('Błąd: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
