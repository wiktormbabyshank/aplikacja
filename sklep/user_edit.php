<?php
session_start();
include('db_connection.php');

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.html");
    exit;
}

$user_id = $_SESSION['id'];

// Pobranie danych użytkownika
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

// Obsługa formularza edycji danych
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Pobieranie danych z formularza
    $imie = htmlspecialchars($_POST['imie']);
    $nazwisko = htmlspecialchars($_POST['nazwisko']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $city = htmlspecialchars($_POST['city']);
    $street = htmlspecialchars($_POST['street']);
    $house_number = htmlspecialchars($_POST['house_number']);
    $postal_code = htmlspecialchars($_POST['postal_code']);

    // Aktualizacja danych w bazie
    $update_query = "
        UPDATE uzytkownicy
        SET imie = ?, nazwisko = ?, email = ?, phone = ?, city = ?, street = ?, house_number = ?, postal_code = ?
        WHERE id = ?
    ";
    $update_stmt = $conn->prepare($update_query);

    if (!$update_stmt) {
        die("Błąd przygotowania zapytania: " . $conn->error);
    }

    $update_stmt->bind_param(
        "ssssssssi",
        $imie,
        $nazwisko,
        $email,
        $phone,
        $city,
        $street,
        $house_number,
        $postal_code,
        $user_id
    );

    if ($update_stmt->execute()) {
        echo "<div class='alert alert-success'>Dane zostały zaktualizowane pomyślnie!</div>";
        // Odświeżenie danych użytkownika po aktualizacji
        header("Refresh:0");
    } else {
        echo "<div class='alert alert-danger'>Błąd podczas aktualizacji danych: " . $update_stmt->error . "</div>";
    }
}

// Pobranie zamówień użytkownika
$orders_query = "
    SELECT 
        og.id AS group_id, 
        dm.name AS delivery_method_name, 
        pm.name AS payment_method_name, 
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
    LEFT JOIN delivery_methods dm ON og.delivery_method_id = dm.id
    LEFT JOIN payment_methods pm ON og.payment_method_id = pm.id
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
    $group_details = [
        'delivery_method_name' => $row['delivery_method_name'],
        'payment_method_name' => $row['payment_method_name'],
        'delivery_cost' => $row['delivery_cost'],
    ];
    if ($row['status'] === 'w realizacji') {
        $pending_orders[$group_id]['group_details'] = $group_details;
        $pending_orders[$group_id]['orders'][] = $row;
    } elseif ($row['status'] === 'zrealizowane') {
        $completed_orders[$group_id]['group_details'] = $group_details;
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
    <div class="card p-4 mb-5">
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
                    <input type="text" id="house_number" name="house_number" class="form-control" value="<?= htmlspecialchars($user['house_number']); ?>">
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
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Grupa Zamówień ID: <?= $group_id; ?></h5>
                    <p><strong>Metoda dostawy:</strong> <?= htmlspecialchars($group['group_details']['delivery_method_name']); ?></p>
                    <p><strong>Metoda płatności:</strong> <?= htmlspecialchars($group['group_details']['payment_method_name']); ?></p>
                    <p><strong>Koszt dostawy:</strong> <?= number_format($group['group_details']['delivery_cost'], 2); ?> zł</p>
                    <?php foreach ($group['orders'] as $order): ?>
                        <p><strong>Produkt:</strong> <?= htmlspecialchars($order['product_name']); ?>, 
                           <strong>Ilość:</strong> <?= $order['amount']; ?>, 
                           <strong>Cena:</strong> <?= number_format($order['price'], 2); ?> zł</p>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="col-md-6">
        <h3 class="text-center mb-4">Zrealizowane zamówienia</h3>
        <?php foreach ($completed_orders as $group_id => $group): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Grupa Zamówień ID: <?= $group_id; ?></h5>
                    <p><strong>Metoda dostawy:</strong> <?= htmlspecialchars($group['group_details']['delivery_method_name']); ?></p>
                    <p><strong>Metoda płatności:</strong> <?= htmlspecialchars($group['group_details']['payment_method_name']); ?></p>
                    <p><strong>Koszt dostawy:</strong> <?= number_format($group['group_details']['delivery_cost'], 2); ?> zł</p>
                    <?php foreach ($group['orders'] as $order): ?>
                        <p><strong>Produkt:</strong> <?= htmlspecialchars($order['product_name']); ?>, 
                           <strong>Ilość:</strong> <?= $order['amount']; ?>, 
                           <strong>Cena:</strong> <?= number_format($order['price'], 2); ?> zł</p>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
