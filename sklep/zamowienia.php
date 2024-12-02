<?php
session_start();
include('db_connection.php');

$orderDetails = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_group_id'])) {
    $orderGroupId = intval($_POST['order_group_id']);

    $query = "
        SELECT 
            zamowienia.id AS order_id,
            zamowienia.product_id,
            zamowienia.imie,
            zamowienia.nazwisko,
            zamowienia.email,
            zamowienia.phone,
            zamowienia.street,
            zamowienia.house_number,
            zamowienia.postal_code,
            zamowienia.city,
            zamowienia.amount,
            zamowienia.price,
            zamowienia.status,
            zamowienia.created_at,
            zamowienia.closed_at,
            products.name AS product_name,
            product_images.image_path AS product_image,
            order_group.delivery_method_id,
            order_group.payment_method_id,
            order_group.delivery_cost,
            delivery_methods.name AS delivery_method,
            payment_methods.name AS payment_method
        FROM zamowienia
        LEFT JOIN products ON zamowienia.product_id = products.id
        LEFT JOIN product_images ON products.id = product_images.product_id
        LEFT JOIN order_group ON zamowienia.order_group_id = order_group.id
        LEFT JOIN delivery_methods ON order_group.delivery_method_id = delivery_methods.id
        LEFT JOIN payment_methods ON order_group.payment_method_id = payment_methods.id
        WHERE zamowienia.order_group_id = ?
        GROUP BY zamowienia.id
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $orderGroupId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orderDetails[] = $row;
        }
    } else {
        $error = 'Nie znaleziono zamówień dla podanego ID grupy zamówienia.';
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprawdź Zamówienia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .container {
            max-width: 800px;
            margin-top: 50px;
        }
        .order-details {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 20px;
        }
        .order-item {
            border-bottom: 1px solid #ddd;
            margin-bottom: 10px;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .order-item img {
            max-width: 100px;
            max-height: 100px;
            margin-right: 20px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">PoopAndYou</a>
    </div>
</nav>

<div class="container">
    <h1 class="text-center mb-4">Sprawdź Zamówienia</h1>
    <form method="POST" action="zamowienia.php" class="mb-4">
        <div class="mb-3">
            <label for="order_group_id" class="form-label">ID Grupy Zamówienia:</label>
            <input type="number" id="order_group_id" name="order_group_id" class="form-control" placeholder="Wpisz ID grupy zamówienia" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Sprawdź</button>
    </form>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php elseif (!empty($orderDetails)): ?>
        <div class="order-details">
            <h2>Szczegóły Grupy Zamówienia</h2>
            <p><strong>ID Grupy Zamówienia:</strong> <?= htmlspecialchars($orderGroupId) ?></p>
            <p><strong>Metoda Dostawy:</strong> <?= htmlspecialchars($orderDetails[0]['delivery_method']) ?></p>
            <p><strong>Metoda Płatności:</strong> <?= htmlspecialchars($orderDetails[0]['payment_method']) ?></p>
            <p><strong>Koszt Dostawy:</strong> <?= htmlspecialchars($orderDetails[0]['delivery_cost']) ?> zł</p>
            <h3 class="mt-4">Lista Zamówień:</h3>
            <?php foreach ($orderDetails as $order): ?>
                <div class="order-item">
                    <img src="<?= htmlspecialchars($order['product_image'] ?: 'uploads/default.jpg') ?>" alt="Zdjęcie produktu">
                    <div>
                        <p><strong>Produkt:</strong> <?= htmlspecialchars($order['product_name']) ?></p>
                        <p><strong>Imię:</strong> <?= htmlspecialchars($order['imie']) ?></p>
                        <p><strong>Nazwisko:</strong> <?= htmlspecialchars($order['nazwisko']) ?></p>
                        <p><strong>Adres:</strong> <?= htmlspecialchars($order['street']) ?> <?= htmlspecialchars($order['house_number']) ?>, <?= htmlspecialchars($order['postal_code']) ?> <?= htmlspecialchars($order['city']) ?></p>
                        <p><strong>Ilość:</strong> <?= htmlspecialchars($order['amount']) ?></p>
                        <p><strong>Cena:</strong> <?= htmlspecialchars($order['price']) ?> zł</p>
                        <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
                        <p><strong>Data Utworzenia:</strong> <?= htmlspecialchars($order['created_at']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
