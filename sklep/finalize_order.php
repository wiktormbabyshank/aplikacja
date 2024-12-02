<?php
session_start();
include('db_connection.php');

$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_method = $_POST['delivery_method'] ?? null;
    $payment_method = $_POST['payment_method'] ?? null;
    $delivery_cost = 0;

    $stmt = $conn->prepare("SELECT cost FROM delivery_methods WHERE id = ?");
    $stmt->bind_param('i', $delivery_method);
    $stmt->execute();
    $stmt->bind_result($delivery_cost);
    $stmt->fetch();
    $stmt->close();

    if (!$delivery_method || !$payment_method) {
        die('Niepoprawne dane dostawy lub płatności.');
    }

    $stmt = $conn->prepare("INSERT INTO order_group (delivery_method_id, payment_method_id, delivery_cost) VALUES (?, ?, ?)");
    $stmt->bind_param('iid', $delivery_method, $payment_method, $delivery_cost);
    if (!$stmt->execute()) {
        die('Błąd podczas tworzenia grupy zamówienia.');
    }
    $order_group_id = $stmt->insert_id;
    $stmt->close();

    $quantities = $_POST['quantities'] ?? [];

    if ($isLoggedIn) {
        $user_id = $_SESSION['id'];

        $stmt = $conn->prepare("
            SELECT k.item_id, p.price, p.name, p.quantity AS stock_quantity, u.imie, u.nazwisko, u.email, u.phone, u.street, u.house_number, u.postal_code, u.city 
            FROM koszyk k
            INNER JOIN products p ON k.item_id = p.id
            INNER JOIN uzytkownicy u ON k.user_id = u.id
            WHERE k.user_id = ?
        ");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        while ($row = $result->fetch_assoc()) {
            $quantity = $quantities[$row['item_id']] ?? 1;

            if ($quantity > $row['stock_quantity']) {
                die("Brak wystarczającej ilości produktu: {$row['name']}");
            }

            $stmt = $conn->prepare("
                INSERT INTO zamowienia_users (product_id, user_id, imie, nazwisko, email, phone, street, house_number, postal_code, city, amount, price, status, order_group_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $status = 2;
            $stmt->bind_param(
                'iissssssssdiii',
                $row['item_id'],
                $user_id,
                $row['imie'],
                $row['nazwisko'],
                $row['email'],
                $row['phone'],
                $row['street'],
                $row['house_number'],
                $row['postal_code'],
                $row['city'],
                $quantity,
                $row['price'],
                $status,
                $order_group_id
            );
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $stmt->bind_param('ii', $quantity, $row['item_id']);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $conn->prepare("DELETE FROM koszyk WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();

        header("Location: order_confirmation.php?order_group_id=$order_group_id");
        exit;

    } else {
        $cart = $_SESSION['cart'] ?? [];
        $name = $_POST['name'] ?? null;
        $surname = $_POST['surname'] ?? null;
        $email = $_POST['email'] ?? null;
        $phone = $_POST['phone'] ?? null;
        $street = $_POST['street'] ?? null;
        $house_number = $_POST['house_number'] ?? null;
        $postal_code = $_POST['postal_code'] ?? null;
        $city = $_POST['city'] ?? null;

        if (!$name || !$surname || !$email || !$street || !$house_number || !$postal_code || !$city) {
            die('Niekompletne dane zamówienia.');
        }

        foreach ($cart as $product_id) {
            $quantity = $quantities[$product_id] ?? 1;

            $stmt = $conn->prepare("SELECT price, quantity FROM products WHERE id = ?");
            $stmt->bind_param('i', $product_id);
            $stmt->execute();
            $stmt->bind_result($price, $stock_quantity);
            $stmt->fetch();
            $stmt->close();

            if ($quantity > $stock_quantity) {
                die("Brak wystarczającej ilości produktu ID: $product_id");
            }

            $status = 2;
            $stmt = $conn->prepare("
                INSERT INTO zamowienia (product_id, imie, nazwisko, email, phone, street, house_number, postal_code, city, amount, price, status, order_group_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                'issssssssdiii',
                $product_id,
                $name,
                $surname,
                $email,
                $phone,
                $street,
                $house_number,
                $postal_code,
                $city,
                $quantity,
                $price,
                $status,
                $order_group_id
            );
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $stmt->bind_param('ii', $quantity, $product_id);
            $stmt->execute();
            $stmt->close();
        }

        unset($_SESSION['cart']);

        header("Location: order_confirmation.php?order_group_id=$order_group_id");
        exit;
    }
} else {
    die('Nieprawidłowe żądanie.');
}
?>
