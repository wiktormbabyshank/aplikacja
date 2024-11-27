<?php
session_set_cookie_params([
    'lifetime' => 0, 
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']), 
    'httponly' => true, 
    'samesite' => 'Strict' 
]);

session_start();

if (!isset($_SESSION['session_initialized'])) {
    $_SESSION['session_initialized'] = true;

    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        unset($_SESSION['cart']);
    }
}

include('db_connection.php');

$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$user_id = $isLoggedIn ? $_SESSION['id'] : null;

$categoriesResult = $conn->query("SELECT * FROM categories");

$searchQuery = "";
$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : null;

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
}
?>


<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        .navbar-brand {
            font-weight: bold;
        }
        .product-card img {
            height: 200px; 
            object-fit: cover; 
        }
        .product-card {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .logout-btn {
            background-color: red;
            border: none;
        }
        .logout-btn:hover {
            background-color: darkred;
        }
        .card-img-top {
            height: 200px; 
            object-fit: cover; 
            width: 100%; 
            border-radius: 10px; 
        }
        .favorite-btn {
            font-size: 1.5rem;
            color: #ddd; 
            border: none;
            background: none;
            cursor: pointer;
        }
        .favorite-btn .fa-heart {
            font-size: 1.5rem;
        }
        .favorite-btn.favorited .fa-heart {
            color: red; 
        }
        .favorite-btn:hover .fa-heart {
            color: #ff6666; 
        }
        .center-links {
            margin: 0 auto;
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">PoopAndYou</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto center-links">
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
                <?php if ($isLoggedIn): ?>
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
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-primary" href="index.html">Zaloguj się</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <h1 class="text-center">Katalog produktów</h1>
    <form method="GET" action="dashboard.php" class="mb-4">
        <div class="row">
            <div class="col-md-4 mb-3">
                <input type="text" name="search" class="form-control" placeholder="Szukaj produktu..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <select name="category" class="form-select">
                    <option value="">Wybierz kategorię</option>
                    <?php while ($category = $categoriesResult->fetch_assoc()): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo ($categoryFilter == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <button type="submit" class="btn btn-primary w-100">Filtruj</button>
            </div>
        </div>
    </form>

    <div class="row">
        <?php
        $query = "
            SELECT DISTINCT 
                products.id, 
                products.name, 
                products.price, 
                products.quantity, 
                GROUP_CONCAT(product_images.image_path) AS images,
                IF(ulubione.id IS NOT NULL, 1, 0) AS is_favorite
            FROM products
            LEFT JOIN product_images ON products.id = product_images.product_id
            LEFT JOIN ulubione ON products.id = ulubione.item_id AND ulubione.user_id = ?
            LEFT JOIN product_categories ON products.id = product_categories.product_id
            WHERE 1
        ";

        $params = [$user_id];
        $types = "i";

        if (!empty($searchQuery)) {
            $query .= " AND products.name LIKE ?";
            $params[] = "%" . $searchQuery . "%";
            $types .= "s";
        }

        if ($categoryFilter) {
            $query .= " AND product_categories.category_id = ?";
            $params[] = $categoryFilter;
            $types .= "i";
        }

        $query .= " GROUP BY products.id";

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $images = explode(',', $row['images']);
                $main_image = $images[0] ?? 'default.jpg';
                $is_favorite = $row['is_favorite'] ? true : false;

                echo "<div class='col-md-4 col-lg-3 mb-4'>";
                echo "<div class='card product-card'>";
                echo "<img src='" . htmlspecialchars($main_image) . "' class='card-img-top' alt='Product Image'>";
                echo "<div class='card-body'>";
                echo "<h5 class='card-title'>" . htmlspecialchars($row['name']) . "</h5>";
                echo "<p class='card-text'><strong>" . htmlspecialchars($row['price']) . " zł</strong></p>";
                echo "<p class='text-muted'>Ilość: " . htmlspecialchars($row['quantity']) . "</p>";

                echo "<div class='d-flex justify-content-between align-items-center'>";

                echo "<form action='" . ($isLoggedIn ? "product_page.php" : "product_page_unlogged.php") . "' method='GET'>";
                echo "<input type='hidden' name='product_id' value='" . htmlspecialchars($row['id']) . "'>";
                echo "<button type='submit' class='btn btn-success'>Kup</button>";
                echo "</form>";

                echo "<button class='btn btn-primary add-to-cart-btn' data-item-id='" . htmlspecialchars($row['id']) . "'>Do koszyka</button>";

                if ($isLoggedIn) {
                    $favorite_class = $is_favorite ? 'fas fa-heart favorited' : 'far fa-heart';
                    echo "<button class='favorite-btn' data-item-id='" . htmlspecialchars($row['id']) . "'>";
                    echo "<i class='$favorite_class'></i>";
                    echo "</button>";
                }

                echo "</div>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p class='text-center text-muted'>Brak produktów spełniających kryteria.</p>";
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
            const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

            fetch(isLoggedIn ? 'add_to_cart.php' : 'add_to_session_cart.php', {
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

    if (<?php echo $isLoggedIn ? 'true' : 'false'; ?>) {
        const favoriteButtons = document.querySelectorAll('.favorite-btn');

        favoriteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const itemId = this.getAttribute('data-item-id');
                const heartIcon = this.querySelector('.fa-heart');
                const isFavorited = heartIcon.classList.contains('fas');

                fetch(isFavorited ? 'remove_from_favorites.php' : 'add_to_favorites.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ item_id: itemId }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        heartIcon.classList.toggle('far');
                        heartIcon.classList.toggle('fas');
                    } else {
                        alert('Błąd: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
