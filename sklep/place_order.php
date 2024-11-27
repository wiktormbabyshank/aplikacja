<?php
session_start();
include('db_connection.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']); 

    $query_product = "SELECT name, price, quantity FROM products WHERE id = ?";
    $stmt_product = $conn->prepare($query_product);

    if (!$stmt_product) {
        die("Błąd w przygotowywaniu zapytania SQL: " . $conn->error);
    }

    $stmt_product->bind_param("i", $product_id);
    $stmt_product->execute();
    $result_product = $stmt_product->get_result();

    if ($result_product->num_rows > 0) {
        $product = $result_product->fetch_assoc();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'], $_POST['payment_method'], $_POST['delivery_method'])) {
            $quantity = intval($_POST['quantity']);
            $payment_method_id = intval($_POST['payment_method']); 
            $delivery_method_id = intval($_POST['delivery_method']);
            $imie = $_POST['imie'] ?? '';
            $nazwisko = $_POST['nazwisko'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $street = $_POST['street'] ?? '';
            $house_number = $_POST['house_number'] ?? '';
            $postal_code = $_POST['postal_code'] ?? '';
            $city = $_POST['city'] ?? '';

            // Pobranie kosztu dostawy
            $query_delivery_cost = "SELECT cost FROM delivery_methods WHERE id = ?";
            $stmt_delivery_cost = $conn->prepare($query_delivery_cost);

            if (!$stmt_delivery_cost) {
                die("Błąd w przygotowywaniu zapytania kosztu dostawy: " . $conn->error);
            }

            $stmt_delivery_cost->bind_param("i", $delivery_method_id);
            $stmt_delivery_cost->execute();
            $result_delivery_cost = $stmt_delivery_cost->get_result();

            if ($result_delivery_cost->num_rows > 0) {
                $delivery = $result_delivery_cost->fetch_assoc();
                $delivery_cost = floatval($delivery['cost']);
            } else {
                die("Nie znaleziono wybranej metody dostawy.");
            }

            if ($quantity > 0 && $quantity <= $product['quantity'] &&
                !empty($imie) && !empty($nazwisko) && !empty($email) && !empty($phone) && 
                !empty($street) && !empty($house_number) && !empty($postal_code) && !empty($city)) {
                
                $product_total = $quantity * $product['price'];
                $total_amount = $product_total + $delivery_cost; // Całkowita kwota zamówienia
                $status = "Pending"; 

                $query_order = "INSERT INTO zamowienia 
                                (imie, nazwisko, email, phone, street, house_number, postal_code, city, amount, delivery_cost, delivery_method_id, status, created_at, payment_method_id) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
                $stmt_order = $conn->prepare($query_order);

                if (!$stmt_order) {
                    die("Błąd w przygotowywaniu zapytania zamówienia: " . $conn->error);
                }

                $stmt_order->bind_param(
                    "sssssssddisii", 
                    $imie, $nazwisko, $email, $phone, $street, $house_number, $postal_code, $city, $product_total, $delivery_cost, $delivery_method_id, $status, $payment_method_id
                );

                if ($stmt_order->execute()) {
                    $new_quantity = $product['quantity'] - $quantity;
                    $query_update_product = "UPDATE products SET quantity = ? WHERE id = ?";
                    $stmt_update_product = $conn->prepare($query_update_product);

                    if (!$stmt_update_product) {
                        die("Błąd w przygotowywaniu zapytania aktualizacji produktu: " . $conn->error);
                    }

                    $stmt_update_product->bind_param("ii", $new_quantity, $product_id);

                    if ($stmt_update_product->execute()) {
                        echo "<!DOCTYPE html>
                        <html lang='pl'>
                        <head>
                            <meta name='viewport' content='width=device-width, initial-scale=1'>
                            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
                            <title>Złóż zamówienie</title>
                        </head>
                        <body>
                        <div class='mt-5'><h1>Zamówienie zostało złożone pomyślnie!</h1>";
                        echo "<a href='dashboard.php' class='btn btn-secondary mt-3'>Powrót do sklepu</a></div>
                        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>
                        </body>
                        </html>";
                        exit;
                    } else {
                        die("Błąd podczas aktualizacji ilości produktu: " . $stmt_update_product->error);
                    }
                } else {
                    die("Błąd podczas składania zamówienia: " . $stmt_order->error);
                }
            } else {
                echo "<p>Wszystkie pola muszą być poprawnie uzupełnione.</p>";
            }
        }

        ?>
        <!DOCTYPE html>
        <html lang="pl">
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
            <title>Złóż zamówienie</title>
        </head>
        <body>
            <div class="container mt-5">
                <h1>Złóż zamówienie</h1>
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <p>Cena za sztukę: <?php echo htmlspecialchars($product['price']); ?> zł</p>
                <p>Dostępna ilość: <?php echo htmlspecialchars($product['quantity']); ?></p>

                <form method="post" class="mt-4">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">

                    <div class="form-group">
                        <label for="quantity">Ilość:</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" min="1" max="<?php echo htmlspecialchars($product['quantity']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="imie">Imię:</label>
                        <input type="text" id="imie" name="imie" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="nazwisko">Nazwisko:</label>
                        <input type="text" id="nazwisko" name="nazwisko" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Telefon:</label>
                        <input type="text" id="phone" name="phone" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="street">Ulica:</label>
                        <input type="text" id="street" name="street" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="house_number">Nr domu:</label>
                        <input type="text" id="house_number" name="house_number" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="postal_code">Kod pocztowy:</label>
                        <input type="text" id="postal_code" name="postal_code" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="city">Miasto:</label>
                        <input type="text" id="city" name="city" class="form-control" required>
                    </div>

                    <div class="form-group mt-3">
                        <label for="payment_method">Wybierz metodę płatności:</label>
                        <select id="payment_method" name="payment_method" class="form-select" required>
                            <?php
                            $result_payment_methods = $conn->query("SELECT id, name FROM payment_methods");
                            while ($method = $result_payment_methods->fetch_assoc()) {
                                echo "<option value='" . htmlspecialchars($method['id']) . "'>" . htmlspecialchars($method['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label for="delivery_method">Wybierz metodę dostawy:</label>
                        <select id="delivery_method" name="delivery_method" class="form-select" required>
                            <?php
                            $result_delivery_methods = $conn->query("SELECT id, name, cost FROM delivery_methods");
                            while ($method = $result_delivery_methods->fetch_assoc()) {
                                echo "<option value='" . htmlspecialchars($method['id']) . "' data-cost='" . htmlspecialchars($method['cost']) . "'>" 
                                    . htmlspecialchars($method['name']) . " (" . htmlspecialchars($method['cost']) . " zł)" 
                                    . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <p id="total-price">Ostateczna cena: 0 zł</p>
                    <button type="submit" class="btn btn-primary mt-3">Złóż zamówienie</button>
                </form>

                <a href="dashboard.php" class="btn btn-secondary mt-3">Powrót do sklepu</a>
            </div>

            <script>
                const pricePerUnit = <?php echo htmlspecialchars($product['price']); ?>;
                const quantityInput = document.getElementById('quantity');
                const deliverySelect = document.getElementById('delivery_method');
                const totalPriceElement = document.getElementById('total-price');

                function updateTotalPrice() {
                    const quantity = parseInt(quantityInput.value) || 0;
                    const deliveryCost = parseFloat(deliverySelect.options[deliverySelect.selectedIndex].dataset.cost) || 0;
                    const totalPrice = (quantity * pricePerUnit + deliveryCost).toFixed(2);
                    totalPriceElement.textContent = `Ostateczna cena: ${totalPrice} zł`;
                }

                quantityInput.addEventListener('input', updateTotalPrice);
                deliverySelect.addEventListener('change', updateTotalPrice);
            </script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
    } else {
        echo "<p>Produkt nie został znaleziony.</p>";
    }
} else {
    echo "<p>Nieprawidłowy identyfikator produktu.</p>";
}
?>
