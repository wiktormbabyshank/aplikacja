<?php
session_start();
include('db_connection.php');

// Inicjalizacja zmiennych
$orderDetails = null;
$error = '';

// Sprawdzanie, czy formularz został wysłany
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = intval($_POST['order_id']); // Bezpieczne rzutowanie na liczbę całkowitą

    // Pobranie danych zamówienia z bazy danych
    $query = "
        SELECT 
            zamowienia.id,
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
            zamowienia.payment_method_id,
            zamowienia.delivery_method_id,
            zamowienia.delivery_cost,
            products.name AS product_name
        FROM zamowienia
        LEFT JOIN products ON zamowienia.product_id = products.id
        WHERE zamowienia.id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $orderDetails = $result->fetch_assoc();
    } else {
        $error = 'Nie znaleziono zamówienia o podanym ID.';
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprawdź Zamówienie</title>
    
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
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">PoopAndYou</a>
    </div>
</nav>

<div class="container">
    <h1 class="text-center mb-4">Sprawdź Zamówienie</h1>
    <form method="POST" action="zamowienia.php" class="mb-4">
        <div class="mb-3">
            <label for="order_id" class="form-label">ID Zamówienia:</label>
            <input type="number" id="order_id" name="order_id" class="form-control" placeholder="Wpisz ID zamówienia" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Sprawdź</button>
    </form>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($orderDetails): ?>
        <div class="order-details">
            <h2>Szczegóły Zamówienia</h2>
            <p><strong>ID Zamówienia:</strong> <?= htmlspecialchars($orderDetails['id']) ?></p>
            <p><strong>Produkt:</strong> <?= htmlspecialchars($orderDetails['product_name']) ?> (ID: <?= htmlspecialchars($orderDetails['product_id']) ?>)</p>
            <p><strong>Imię:</strong> <?= htmlspecialchars($orderDetails['imie']) ?></p>
            <p><strong>Nazwisko:</strong> <?= htmlspecialchars($orderDetails['nazwisko']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($orderDetails['email']) ?></p>
            <p><strong>Telefon:</strong> <?= htmlspecialchars($orderDetails['phone']) ?></p>
            <p><strong>Adres:</strong> <?= htmlspecialchars($orderDetails['street']) ?> <?= htmlspecialchars($orderDetails['house_number']) ?>, <?= htmlspecialchars($orderDetails['postal_code']) ?> <?= htmlspecialchars($orderDetails['city']) ?></p>
            <p><strong>Ilość:</strong> <?= htmlspecialchars($orderDetails['amount']) ?></p>
            <p><strong>Cena:</strong> <?= htmlspecialchars($orderDetails['price']) ?> zł</p>
            <p><strong>Status:</strong> <?= htmlspecialchars($orderDetails['status']) ?></p>
            <p><strong>Data utworzenia:</strong> <?= htmlspecialchars($orderDetails['created_at']) ?></p>
            <?php if ($orderDetails['closed_at']): ?>
                <p><strong>Data zamknięcia:</strong> <?= htmlspecialchars($orderDetails['closed_at']) ?></p>
            <?php endif; ?>
            <p><strong>Metoda płatności:</strong> <?= htmlspecialchars($orderDetails['payment_method_id']) ?></p>
            <p><strong>Metoda dostawy:</strong> <?= htmlspecialchars($orderDetails['delivery_method_id']) ?></p>
            <p><strong>Koszt dostawy:</strong> <?= htmlspecialchars($orderDetails['delivery_cost']) ?> zł</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
