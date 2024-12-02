<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.html");
    exit;
}

$user_id = $_SESSION['id'];
$query = "SELECT * FROM uzytkownicy WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Użytkownik nie istnieje.";
    exit;
}

$user = $result->fetch_assoc();

$orders_query = "
    SELECT 
        og.id AS group_id, 
        og.delivery_method_id, 
        og.payment_method_id, 
        og.delivery_cost, 
        zu.id AS order_id, 
        zu.product_id, 
        zu.amount, 
        zu.price, 
        zu.status, 
        zu.created_at, 
        zu.closed_at,
        p.name AS product_name
    FROM order_group og
    LEFT JOIN zamowienia_users zu ON zu.order_group_id = og.id
    LEFT JOIN products p ON zu.product_id = p.id
    WHERE zu.user_id = ?
    ORDER BY zu.status, og.id DESC, zu.created_at DESC
";
$orders_stmt = $conn->prepare($orders_query);
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();

$pending_orders = [];
$completed_orders = [];

while ($row = $orders_result->fetch_assoc()) {
    $group_id = $row['group_id'];
    if ($row['status'] === 'w realizacji') {
        $pending_orders[$group_id]['group_details'] = [
            'delivery_method_id' => $row['delivery_method_id'],
            'payment_method_id' => $row['payment_method_id'],
            'delivery_cost' => $row['delivery_cost'],
        ];
        $pending_orders[$group_id]['orders'][] = $row;
    } elseif ($row['status'] === 'zrealizowane') {
        $completed_orders[$group_id]['group_details'] = [
            'delivery_method_id' => $row['delivery_method_id'],
            'payment_method_id' => $row['payment_method_id'],
            'delivery_cost' => $row['delivery_cost'],
        ];
        $completed_orders[$group_id]['orders'][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj swoje dane</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .edit-card, .order-group {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .order-group {
            margin-bottom: 20px;
            padding: 15px;
            background: white;
        }
        .order-title {
            font-weight: bold;
        }
        .order-status {
            font-weight: bold;
        }
        .order-item {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f9f9f9;
        }
        .order-item:last-child {
            margin-bottom: 0;
        }
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

<div class="container">
    <h1 class="text-center mb-4">Edytuj swoje dane</h1>
    <div class="card edit-card p-4 mb-5">
        <form action="user_edit.php" method="post">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="imie" class="form-label">Imię:</label>
                    <input type="text" id="imie" name="imie" class="form-control" value="<?= htmlspecialchars($user['imie']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="nazwisko" class="form-label">Nazwisko:</label>
                    <input type="text" id="nazwisko" name="nazwisko" class="form-control" value="<?= htmlspecialchars($user['nazwisko']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">Telefon:</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']); ?>">
                </div>
                <div class="col-md-6">
                    <label for="city" class="form-label">Miasto:</label>
                    <input type="text" id="city" name="city" class="form-control" value="<?= htmlspecialchars($user['city']); ?>">
                </div>
                <div class="col-md-6">
                    <label for="street" class="form-label">Ulica:</label>
                    <input type="text" id="street" name="street" class="form-control" value="<?= htmlspecialchars($user['street']); ?>">
                </div>
                <div class="col-md-6">
                    <label for="house_number" class="form-label">Numer Domu:</label>
                    <input type="number" id="house_number" name="house_number" class="form-control" value="<?= htmlspecialchars($user['house_number']); ?>">
                </div>
                <div class="col-md-6">
                    <label for="postal_code" class="form-label">Kod pocztowy:</label>
                    <input type="text" id="postal_code" name="postal_code" class="form-control" value="<?= htmlspecialchars($user['postal_code']); ?>">
                </div>
            </div>
            <button type="submit" name="submit" class="btn btn-success mt-3">Zapisz zmiany</button>
        </form>
    </div>

    <div class="row">
        <div class="col-md-6">
            <h3 class="text-center mb-4">Zamówienia w realizacji</h3>
            <?php foreach ($pending_orders as $group_id => $group): ?>
                <div class="order-group">
                    <h4>Grupa Zamówień ID: <?= $group_id; ?></h4>
                    <p>Metoda dostawy: <?= htmlspecialchars($group['group_details']['delivery_method_id']); ?></p>
                    <p>Koszt dostawy: <?= number_format($group['group_details']['delivery_cost'], 2); ?> zł</p>
                    <?php foreach ($group['orders'] as $order): ?>
                        <div class="order-item">
                            <p><strong>Produkt:</strong> <?= htmlspecialchars($order['product_name']); ?></p>
                            <p><strong>Ilość:</strong> <?= $order['amount']; ?></p>
                            <p><strong>Cena:</strong> <?= number_format($order['price'], 2); ?> zł</p>
                            <p><strong>Status:</strong> <?= htmlspecialchars($order['status']); ?></p>
                            <p><strong>Data utworzenia:</strong> <?= htmlspecialchars($order['created_at']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="col-md-6">
            <h3 class="text-center mb-4">Zrealizowane zamówienia</h3>
            <?php foreach ($completed_orders as $group_id => $group): ?>
                <div class="order-group">
                    <h4>Grupa Zamówień ID: <?= $group_id; ?></h4>
                    <p>Metoda dostawy: <?= htmlspecialchars($group['group_details']['delivery_method_id']); ?></p>
                    <p>Koszt dostawy: <?= number_format($group['group_details']['delivery_cost'], 2); ?> zł</p>
                    <?php foreach ($group['orders'] as $order): ?>
                        <div class="order-item">
                            <p><strong>Produkt:</strong> <?= htmlspecialchars($order['product_name']); ?></p>
                            <p><strong>Ilość:</strong> <?= $order['amount']; ?></p>
                            <p><strong>Cena:</strong> <?= number_format($order['price'], 2); ?> zł</p>
                            <p><strong>Status:</strong> <?= htmlspecialchars($order['status']); ?></p>
                            <p><strong>Data zamknięcia:</strong> <?= htmlspecialchars($order['closed_at']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
