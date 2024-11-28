<?php
session_start();
include('db_connection.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['product_id']) || empty($_GET['product_id']) || !ctype_digit($_GET['product_id'])) {
    die("Nieprawidłowy identyfikator produktu.");
}

$product_id = intval($_GET['product_id']);

$query_product = "SELECT id, name, price, quantity FROM products WHERE id = ?";
$stmt_product = $conn->prepare($query_product);
if (!$stmt_product) {
    die("Błąd zapytania SQL: " . $conn->error);
}
$stmt_product->bind_param("i", $product_id);
$stmt_product->execute();
$result_product = $stmt_product->get_result();

if ($result_product->num_rows === 0) {
    die("Produkt o podanym ID nie istnieje.");
}

$product = $result_product->fetch_assoc();

$query_payment_methods = "SELECT id, name FROM payment_methods";
$result_payment_methods = $conn->query($query_payment_methods);
if (!$result_payment_methods) {
    die("Błąd zapytania metod płatności: " . $conn->error);
}
$payment_methods = $result_payment_methods->fetch_all(MYSQLI_ASSOC);

$query_delivery_methods = "SELECT id, name, cost FROM delivery_methods";
$result_delivery_methods = $conn->query($query_delivery_methods);
if (!$result_delivery_methods) {
    die("Błąd zapytania metod dostawy: " . $conn->error);
}
$delivery_methods = $result_delivery_methods->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imie = $_POST['imie'] ?? '';
    $nazwisko = $_POST['nazwisko'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $street = $_POST['street'] ?? '';
    $house_number = $_POST['house_number'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $city = $_POST['city'] ?? '';
    $amount = intval($_POST['amount']);
    $payment_method_id = intval($_POST['payment_method_id']);
    $delivery_method_id = intval($_POST['delivery_method_id']);

    $delivery_cost = null;
    foreach ($delivery_methods as $method) {
        if ($method['id'] == $delivery_method_id) {
            $delivery_cost = floatval($method['cost']);
            break;
        }
    }

    if ($delivery_cost === null) {
        die("Nieprawidłowa metoda dostawy.");
    }

    $product_price = $product['price'];
    $total_price = $product_price * $amount;

    if (empty($imie) || empty($nazwisko) || empty($email) || empty($phone) || 
        empty($street) || empty($house_number) || empty($postal_code) || empty($city) || $amount <= 0) {
        die("Wszystkie pola muszą być poprawnie uzupełnione.");
    }

    $status = "2";

    $query_order = "INSERT INTO zamowienia 
                    (product_id, imie, nazwisko, email, phone, street, house_number, postal_code, city, 
                     amount, price, status, created_at, payment_method_id, delivery_method_id, delivery_cost) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)";

    $stmt_order = $conn->prepare($query_order);
    if (!$stmt_order) {
        die("Błąd zapytania dodawania zamówienia: " . $conn->error);
    }

    $stmt_order->bind_param(
        "issssssssiddsid", 
        $product_id, 
        $imie, 
        $nazwisko, 
        $email, 
        $phone, 
        $street, 
        $house_number, 
        $postal_code, 
        $city, 
        $amount, 
        $total_price, 
        $status, 
        $payment_method_id, 
        $delivery_method_id, 
        $delivery_cost
    );

    if ($stmt_order->execute()) {
        $new_quantity = $product['quantity'] - $amount;
    
        if ($new_quantity < 0) {
            die("Nie można złożyć zamówienia, ponieważ ilość zamawianych produktów przekracza dostępny stan magazynowy.");
        }
    
        $query_update_quantity = "UPDATE products SET quantity = ? WHERE id = ?";
        $stmt_update_quantity = $conn->prepare($query_update_quantity);
        if (!$stmt_update_quantity) {
            die("Błąd zapytania aktualizacji ilości: " . $conn->error);
        }
    
        $stmt_update_quantity->bind_param("ii", $new_quantity, $product_id);
        if (!$stmt_update_quantity->execute()) {
            die("Błąd podczas aktualizacji ilości produktu: " . $stmt_update_quantity->error);
        }
        echo "<!DOCTYPE html>
        <html lang='pl'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            <title>Zamówienie złożone</title>
        </head>
        <body>
        <div class='container mt-5'>
            <h1>Zamówienie zostało złożone pomyślnie!</h1>
            <p>Dziękujemy za zakupy. Twoje zamówienie zostanie przetworzone w najbliższym czasie.</p>
            <a href='dashboard.php' class='btn btn-primary mt-3'>Powrót do sklepu</a>
        </div>
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
        </body>
        </html>";
        exit;
    } else {
        die("Błąd podczas składania zamówienia: " . $stmt_order->error);
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Złóż zamówienie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .container {
            margin-top: 30px;
            max-width: 800px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            font-size: 1rem;
            padding: 10px 20px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            border: none;
            padding: 10px 20px;
        }
        .form-label {
            font-weight: bold;
        }
        #total-price {
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">PoopAndYou</a>
    </div>
</nav>

<div class="container">
    <h1 class="text-center mb-4">Złóż zamówienie</h1>
    <h2><?= htmlspecialchars($product['name']); ?></h2>
    <p>Cena za sztukę: <strong><?= htmlspecialchars($product['price']); ?> zł</strong></p>
    <p>Dostępna ilość: <?= htmlspecialchars($product['quantity']); ?></p>

    <form method="post">
        <div class="mb-3">
            <label for="amount" class="form-label">Ilość:</label>
            <input type="number" id="amount" name="amount" class="form-control" min="1" max="<?= htmlspecialchars($product['quantity']); ?>" required>
        </div>
        <div id="total-price">Cena całkowita: <?= htmlspecialchars($product['price']); ?> zł</div>
        <div class="mb-3">
            <label for="imie" class="form-label">Imię:</label>
            <input type="text" id="imie" name="imie" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="nazwisko" class="form-label">Nazwisko:</label>
            <input type="text" id="nazwisko" name="nazwisko" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Telefon:</label>
            <input type="text" id="phone" name="phone" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="street" class="form-label">Ulica:</label>
            <input type="text" id="street" name="street" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="house_number" class="form-label">Nr domu:</label>
            <input type="text" id="house_number" name="house_number" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="postal_code" class="form-label">Kod pocztowy:</label>
            <input type="text" id="postal_code" name="postal_code" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="city" class="form-label">Miasto:</label>
            <input type="text" id="city" name="city" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="payment_method_id" class="form-label">Metoda płatności:</label>
            <select id="payment_method_id" name="payment_method_id" class="form-select" required>
                <?php foreach ($payment_methods as $method): ?>
                    <option value="<?= htmlspecialchars($method['id']); ?>"><?= htmlspecialchars($method['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="delivery_method_id" class="form-label">Metoda dostawy:</label>
            <select id="delivery_method_id" name="delivery_method_id" class="form-select" required>
                <?php foreach ($delivery_methods as $method): ?>
                    <option value="<?= htmlspecialchars($method['id']); ?>" data-cost="<?= htmlspecialchars($method['cost']); ?>">
                        <?= htmlspecialchars($method['name']); ?> (<?= htmlspecialchars($method['cost']); ?> zł)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Złóż zamówienie</button>
    </form>
</div>

<script>
    const pricePerUnit = <?= htmlspecialchars($product['price']); ?>;
    const quantityInput = document.getElementById('amount');
    const totalPriceElement = document.getElementById('total-price');

    quantityInput.addEventListener('input', () => {
        const quantity = parseInt(quantityInput.value) || 1;
        const totalPrice = (quantity * pricePerUnit).toFixed(2);
        totalPriceElement.textContent = `Cena całkowita: ${totalPrice} zł`;
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
