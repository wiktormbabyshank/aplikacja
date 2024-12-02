<?php
session_start();
include('db_connection.php');



// Inicjalizacja zmiennych
$totalCartPrice = 0;
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

if ($isLoggedIn) {
    $user_id = $_SESSION['id'];

    // Pobranie koszyka z bazy danych dla zalogowanego użytkownika
    $query = "
        SELECT 
            koszyk.id AS cart_id,
            products.id AS product_id,
            products.name,
            products.price,
            GROUP_CONCAT(product_images.image_path) AS images
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
} else {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koszyk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .product-card { box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); border-radius: 10px; margin-bottom: 20px; }
        .btn-remove { background-color: #dc3545; color: white; border: none; border-radius: 5px; padding: 10px 20px; }
        .btn-remove:hover { background-color: #c82333; }
    </style>
</head>
<body>
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
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item"><a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> Koszyk</a></li>
                    <li class="nav-item"><a class="nav-link" href="ulubione.php"><i class="fas fa-heart"></i> Ulubione</a></li>
                    <li class="nav-item"><a class="nav-link" href="user_edit.php">Profil</a></li>
                    <li class="nav-item"><a class="btn btn-danger" href="logout.php">Wyloguj się</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="btn btn-primary" href="index.html">Zaloguj się</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <h1 class="text-center mb-4">Twój Koszyk</h1>
    <form action="finalize_order.php" method="POST">
        <div class="row">
            <?php if ($isLoggedIn): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                        $images = explode(',', $row['images']);
                        $main_image = $images[0] ?? 'default.jpg';
                        $price = $row['price'];
                        $totalCartPrice += $price;
                    ?>
                    <div class="col-md-12 mb-3">
                        <div class="card product-card p-3">
                            <div class="row g-0">
                                <div class="col-md-2">
                                    <img src="<?= htmlspecialchars($main_image) ?>" class="img-fluid rounded-start" alt="Product Image">
                                </div>
                                <div class="col-md-6">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                                        <p class="card-text">
                                            Cena: <strong><?= htmlspecialchars($price) ?> zł</strong><br>
                                            Ilość: 
                                            <input type="number" class="form-control quantity-input" 
                                                   name="quantities[<?= $row['product_id'] ?>]"
                                                   data-cart-id="<?= $row['cart_id'] ?>" 
                                                   data-price="<?= $price ?>" 
                                                   value="1" 
                                                   min="1" style="width: 80px; display: inline-block;">
                                            Łączna cena: <strong class="total-price"><?= number_format($price, 2) ?></strong> zł
                                        </p>
                                        <button type="button" class="btn btn-danger btn-remove" data-cart-id="<?= $row['cart_id'] ?>">Usuń</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <?php foreach ($_SESSION['cart'] as $index => $product_id): ?>
                    <?php
                        $productQuery = $conn->prepare("
                            SELECT 
                                products.id AS product_id,
                                products.name,
                                products.price,
                                GROUP_CONCAT(product_images.image_path) AS images
                            FROM products
                            LEFT JOIN product_images ON products.id = product_images.product_id
                            WHERE products.id = ?
                            GROUP BY products.id
                        ");
                        $productQuery->bind_param('i', $product_id);
                        $productQuery->execute();
                        $product = $productQuery->get_result()->fetch_assoc();
                        if (!$product) continue;

                        $price = $product['price'];
                        $totalCartPrice += $price;
                        $main_image = explode(',', $product['images'])[0] ?? 'default.jpg';
                    ?>
                    <div class="col-md-12 mb-3">
                        <div class="card product-card p-3">
                            <div class="row g-0">
                                <div class="col-md-2">
                                    <img src="<?= htmlspecialchars($main_image) ?>" class="img-fluid rounded-start" alt="Product Image">
                                </div>
                                <div class="col-md-6">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                        <p class="card-text">
                                            Cena: <strong><?= htmlspecialchars($price) ?> zł</strong><br>
                                            Ilość: 
                                            <input type="number" class="form-control quantity-input" 
                                                   name="quantities[<?= $product['product_id'] ?>]"
                                                   data-cart-id="<?= $index ?>" 
                                                   data-price="<?= $price ?>" 
                                                   value="1" 
                                                   min="1" style="width: 80px; display: inline-block;">
                                            Łączna cena: <strong class="total-price"><?= number_format($price, 2) ?></strong> zł
                                        </p>
                                        <button type="button" class="btn btn-danger btn-remove" data-cart-id="<?= $index ?>">Usuń</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <h5>Opcje dostawy</h5>
        <select name="delivery_method" class="form-select mb-3" required>
            <option value="" disabled selected>Wybierz opcję dostawy</option>
            <?php
            $deliveryMethods = $conn->query("SELECT id, name, cost FROM delivery_methods");
            while ($delivery = $deliveryMethods->fetch_assoc()) {
                echo "<option value='{$delivery['id']}'>" . htmlspecialchars($delivery['name']) . " ({$delivery['cost']} zł)</option>";
            }
            ?>
        </select>

        <h5>Opcje płatności</h5>
        <select name="payment_method" class="form-select mb-3" required>
            <option value="" disabled selected>Wybierz opcję płatności</option>
            <?php
            $paymentMethods = $conn->query("SELECT id, name FROM payment_methods");
            while ($payment = $paymentMethods->fetch_assoc()) {
                echo "<option value='{$payment['id']}'>" . htmlspecialchars($payment['name']) . "</option>";
            }
            ?>
        </select>

        <?php if (!$isLoggedIn): ?>
            <h4>Dane osobowe</h4>
            <div class="mb-3">
                <label for="name" class="form-label">Imię</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="surname" class="form-label">Nazwisko</label>
                <input type="text" name="surname" id="surname" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Telefon</label>
                <input type="text" name="phone" id="phone" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="street" class="form-label">Ulica</label>
                <input type="text" name="street" id="street" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="house_number" class="form-label">Numer domu</label>
                <input type="text" name="house_number" id="house_number" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="postal_code" class="form-label">Kod pocztowy</label>
                <input type="text" name="postal_code" id="postal_code" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="city" class="form-label">Miasto</label>
                <input type="text" name="city" id="city" class="form-control" required>
            </div>
        <?php endif; ?>

        <h4 class="text-end">Łączna cena koszyka: <strong id="total-cart-price"><?= number_format($totalCartPrice, 2) ?></strong> zł</h4>
        <button type="submit" class="btn btn-success btn-lg w-100">Kup</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('input', function () {
            const price = parseFloat(this.dataset.price);
            const quantity = parseInt(this.value) || 1;
            const totalPriceElement = this.closest('.product-card').querySelector('.total-price');
            totalPriceElement.textContent = (price * quantity).toFixed(2);

            let totalCartPrice = 0;
            document.querySelectorAll('.quantity-input').forEach(input => {
                const itemPrice = parseFloat(input.dataset.price);
                const itemQuantity = parseInt(input.value) || 1;
                totalCartPrice += itemPrice * itemQuantity;
            });
            document.getElementById('total-cart-price').textContent = totalCartPrice.toFixed(2);
        });
    });

    document.querySelectorAll('.btn-remove').forEach(button => {
    button.addEventListener('click', function () {
        const cartId = this.dataset.cartId;

        fetch('remove_from_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ cart_id: cartId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.closest('.product-card').remove();
                // Aktualizuj łączną cenę koszyka
                let totalCartPrice = 0;
                document.querySelectorAll('.quantity-input').forEach(input => {
                    const price = parseFloat(input.dataset.price);
                    const quantity = parseInt(input.value) || 1;
                    totalCartPrice += price * quantity;
                });
                document.getElementById('total-cart-price').textContent = totalCartPrice.toFixed(2);
            } else {
                alert(data.message || 'Nie udało się usunąć produktu.');
            }
        })
        .catch(error => console.error('Błąd Fetch:', error));
    });
});
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
