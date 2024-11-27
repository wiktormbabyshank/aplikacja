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
    SELECT * FROM zamowienia_users
    WHERE user_id = ?
    ORDER BY 
        CASE 
            WHEN status = 'w realizacji' THEN created_at
            WHEN status = 'zrealizowane' THEN closed_at
        END DESC
";
$orders_stmt = $conn->prepare($orders_query);
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();

$pending_orders = [];
$completed_orders = [];

while ($order = $orders_result->fetch_assoc()) {
    if ($order['status'] === 'w realizacji') {
        $pending_orders[] = $order;
    } elseif ($order['status'] === 'zrealizowane') {
        $completed_orders[] = $order;
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
        .edit-card {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .btn-save {
            background-color: #28a745;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
        }
        .btn-save:hover {
            background-color: #218838;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
        .orders-section {
            margin-top: 50px;
        }
        .order-card {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 15px;
            background-color: white;
        }
        .order-title {
            font-size: 1.1rem;
            font-weight: bold;
        }
        .order-status {
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
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> Koszyk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="user_edit.php">Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-danger" href="logout.php">Wyloguj się</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="text-center mb-4">Edytuj swoje dane</h1>
        <div class="card edit-card p-4">
            <form action="user_edit.php" method="post">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="imie" class="form-label">Imię:</label>
                        <input type="text" id="imie" name="imie" class="form-control" value="<?php echo htmlspecialchars($user['imie']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="nazwisko" class="form-label">Nazwisko:</label>
                        <input type="text" id="nazwisko" name="nazwisko" class="form-control" value="<?php echo htmlspecialchars($user['nazwisko']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Telefon:</label>
                        <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="city" class="form-label">Miasto:</label>
                        <input type="text" id="city" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="street" class="form-label">Ulica:</label>
                        <input type="text" id="street" name="street" class="form-control" value="<?php echo htmlspecialchars($user['street']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="house_number" class="form-label">Numer Domu:</label>
                        <input type="number" id="house_number" name="house_number" class="form-control" value="<?php echo htmlspecialchars($user['house_number']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="postal_code" class="form-label">Kod pocztowy:</label>
                        <input type="text" id="postal_code" name="postal_code" class="form-control" value="<?php echo htmlspecialchars($user['postal_code']); ?>">
                    </div>
                </div>
                <button type="submit" name="submit" class="btn btn-save mt-3">Zapisz zmiany</button>
            </form>
            <a href="dashboard.php" class="btn btn-back mt-3">Powrót do sklepu</a>
        </div>

        <div class="orders-section row mt-5">
            <div class="col-md-6">
                <h3 class="text-center mb-4">Zamówienia w realizacji</h3>
                <?php if (!empty($pending_orders)): ?>
                    <?php foreach ($pending_orders as $order): ?>
                        <div class="order-card mb-3">
                            <div class="order-title">ID Zamówienia: <?= $order['id'] ?></div>
                            <div>Data utworzenia: <?= $order['created_at'] ?></div>
                            <div>Status: <span class="order-status text-warning">W realizacji</span></div>
                            <div>Kwota: <?= number_format($order['amount'], 2) ?> zł</div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">Brak zamówień w realizacji.</p>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <h3 class="text-center mb-4">Zrealizowane zamówienia</h3>
                <?php if (!empty($completed_orders)): ?>
                    <?php foreach ($completed_orders as $order): ?>
                        <div class="order-card mb-3">
                            <div class="order-title">ID Zamówienia: <?= $order['id'] ?></div>
                            <div>Data zamknięcia: <?= $order['closed_at'] ?></div>
                            <div>Status: <span class="order-status text-success">Zrealizowane</span></div>
                            <div>Kwota: <?= number_format($order['amount'], 2) ?> zł</div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">Brak zrealizowanych zamówień.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php
    if (isset($_POST['submit'])) {
        $imie = $_POST['imie'];
        $nazwisko = $_POST['nazwisko'];
        $email = $_POST['email'];
        $miasto = $_POST['city'];
        $telefon = $_POST['phone'];
        $ulica = $_POST['street'];
        $numer_domu = $_POST['house_number'];
        $kod_pocztowy = $_POST['postal_code'];

        $update_query = "UPDATE uzytkownicy SET imie = ?, nazwisko = ?, email = ?, city = ?, phone = ?, street = ?, house_number = ?, postal_code = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssssssisi", $imie, $nazwisko, $email, $miasto, $telefon, $ulica, $numer_domu, $kod_pocztowy, $user_id);
        if ($update_stmt->execute()) {
            echo "<div class='alert alert-success text-center mt-3'>Dane zostały zaktualizowane pomyślnie.</div>";
        } else {
            echo "<div class='alert alert-danger text-center mt-3'>Wystąpił błąd przy aktualizacji danych.</div>";
        }
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
