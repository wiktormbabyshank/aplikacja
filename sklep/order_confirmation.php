<?php
session_start();
include('db_connection.php');

if (!isset($_GET['order_group_id']) || empty($_GET['order_group_id'])) {
    die("Brak wymaganego identyfikatora grupy zamówień.");
}

$order_group_id = intval($_GET['order_group_id']);

// Sprawdzenie, czy użytkownik jest zalogowany
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

if ($isLoggedIn) {
    // Pobranie szczegółów zamówienia dla zalogowanego użytkownika
    $query = "
        SELECT 
            og.id AS order_group_id,
            dm.name AS delivery_method,
            dm.cost AS delivery_cost,
            pm.name AS payment_method,
            zu.imie, zu.nazwisko, zu.email, zu.phone, zu.street, zu.house_number, zu.postal_code, zu.city,
            z.product_id, p.name AS product_name, z.amount, z.price, z.status
        FROM order_group og
        INNER JOIN zamowienia_users z ON og.id = z.order_group_id
        INNER JOIN delivery_methods dm ON og.delivery_method_id = dm.id
        INNER JOIN payment_methods pm ON og.payment_method_id = pm.id
        INNER JOIN uzytkownicy zu ON z.user_id = zu.id
        INNER JOIN products p ON z.product_id = p.id
        WHERE og.id = ? AND zu.id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $order_group_id, $_SESSION['id']);
} else {
    // Pobranie szczegółów zamówienia dla niezalogowanego użytkownika
    $query = "
        SELECT 
            og.id AS order_group_id,
            dm.name AS delivery_method,
            dm.cost AS delivery_cost,
            pm.name AS payment_method,
            z.imie, z.nazwisko, z.email, z.phone, z.street, z.house_number, z.postal_code, z.city,
            z.product_id, p.name AS product_name, z.amount, z.price, z.status
        FROM order_group og
        INNER JOIN zamowienia z ON og.id = z.order_group_id
        INNER JOIN delivery_methods dm ON og.delivery_method_id = dm.id
        INNER JOIN payment_methods pm ON og.payment_method_id = pm.id
        INNER JOIN products p ON z.product_id = p.id
        WHERE og.id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $order_group_id);
}

if (!$stmt->execute()) {
    die("Błąd podczas pobierania szczegółów zamówienia: " . $stmt->error);
}

$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Nie znaleziono zamówienia z podanym identyfikatorem.");
}

$orderDetails = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potwierdzenie Zamówienia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
<div class="container mt-5">
    <h1 class="text-center mb-4">Potwierdzenie Zamówienia</h1>

    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Szczegóły Zamówienia</h4>
            <p><strong>Numer grupy zamówień:</strong> <?= htmlspecialchars($orderDetails[0]['order_group_id']) ?></p>
            <p><strong>Metoda dostawy:</strong> <?= htmlspecialchars($orderDetails[0]['delivery_method']) ?></p>
            <p><strong>Koszt dostawy:</strong> <?= htmlspecialchars($orderDetails[0]['delivery_cost']) ?> zł</p>
            <p><strong>Metoda płatności:</strong> <?= htmlspecialchars($orderDetails[0]['payment_method']) ?></p>
            <p><strong>Imię i nazwisko:</strong> <?= htmlspecialchars($orderDetails[0]['imie']) ?> <?= htmlspecialchars($orderDetails[0]['nazwisko']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($orderDetails[0]['email']) ?></p>
            <p><strong>Telefon:</strong> <?= htmlspecialchars($orderDetails[0]['phone']) ?></p>
            <p><strong>Adres:</strong> <?= htmlspecialchars($orderDetails[0]['street']) ?> <?= htmlspecialchars($orderDetails[0]['house_number']) ?>, <?= htmlspecialchars($orderDetails[0]['postal_code']) ?> <?= htmlspecialchars($orderDetails[0]['city']) ?></p>
        </div>
    </div>

    <div class="mt-4">
        <h4>Produkty w zamówieniu</h4>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Produkt</th>
                <th>Ilość</th>
                <th>Cena za sztukę</th>
                <th>Łączna cena</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($orderDetails as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td><?= htmlspecialchars($item['amount']) ?></td>
                    <td><?= htmlspecialchars(number_format($item['price'] / $item['amount'], 2)) ?> zł</td>
                    <td><?= htmlspecialchars(number_format($item['price'], 2)) ?> zł</td>
                    <td><?= htmlspecialchars($item['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="text-center mt-4">
        <a href="dashboard.php" class="btn btn-primary">Wróć do sklepu</a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
