<?php
session_start();
include('db_connection.php');
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administracyjny - Zamówienia Niezalogowanych</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .order-group {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 15px;
            background-color: white;
            margin-bottom: 20px;
        }
        .order-item {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .order-item img {
            max-width: 80px;
            max-height: 80px;
            margin-right: 15px;
        }
        .btn-complete {
            background-color: #007bff;
            color: white;
            margin-top: 10px;
        }
        .btn-complete:hover {
            background-color: #0056b3;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
            margin-left: 10px;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .nav-link-btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            background-color: #007bff;
            color: white;
            text-align: center;
            text-decoration: none;
            font-size: 16px;
            border-radius: 8px;
            transition: background-color 0.3s;
        }
        .nav-link-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Panel Administracyjny</a>
        <form action="logout.php" method="post" class="d-inline-block">
            <button type="submit" class="btn btn-danger">Wyloguj się</button>
        </form>
    </div>
</nav>

<div class="container">
    <header class="text-center mb-4">
        <h1>Panel Administracyjny - Zamówienia Niezalogowanych</h1>
    </header>

    <div class="nav-links mb-4">
        <a href="admin_dashboard.php" class="nav-link-btn">Zarządzaj Produktami</a>
        <a href="admin_dashboard_kat.php" class="nav-link-btn">Zarządzaj Kategoriami</a>
        <a href="admin_dashboard_user.php" class="nav-link-btn">Zarządzaj Użytkownikami</a>
        <a href="admin_dashboard_logged.php" class="nav-link-btn">Zarządzaj Zamówieniami Zalogowanych</a>
        <a href="admin_dashboard_unlogged.php" class="nav-link-btn">Zarządzaj Zamówieniami Niezalogowanych</a>
        <a href="admin_dashboard_pages.php" class="nav-link-btn">Zarządzaj Podstronami</a>
        <a href="admin_dashboard_delivery.php" class="nav-link-btn">Zarządzaj Sposobami Dostawy</a>
        <a href="admin_dashboard_payment.php" class="nav-link-btn">Zarządzaj Sposobami Płatności</a>
    </div>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['group_id']) && isset($_POST['action'])) {
            $group_id = intval($_POST['group_id']);
            if ($_POST['action'] === 'complete') {
                $current_date = date('Y-m-d H:i:s');
                $update_query = "UPDATE zamowienia SET status = 3, closed_at = ? WHERE order_group_id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param('si', $current_date, $group_id);
                if ($stmt->execute()) {
                    echo "<div class='alert alert-success text-center'>Grupa zamówień ID $group_id została zrealizowana.</div>";
                } else {
                    echo "<div class='alert alert-danger text-center'>Nie udało się zrealizować grupy zamówień.</div>";
                }
            } elseif ($_POST['action'] === 'delete') {
                $delete_orders_query = "DELETE FROM zamowienia WHERE order_group_id = ?";
                $stmt1 = $conn->prepare($delete_orders_query);
                $stmt1->bind_param('i', $group_id);
                $stmt1->execute();

                $delete_group_query = "DELETE FROM order_group WHERE id = ?";
                $stmt2 = $conn->prepare($delete_group_query);
                $stmt2->bind_param('i', $group_id);
                $stmt2->execute();

                echo "<div class='alert alert-success text-center'>Grupa zamówień ID $group_id została usunięta.</div>";
            }
        }

        if (isset($_POST['order_id']) && $_POST['action'] === 'delete_order') {
            $order_id = intval($_POST['order_id']);
            $delete_order_query = "DELETE FROM zamowienia WHERE id = ?";
            $stmt = $conn->prepare($delete_order_query);
            $stmt->bind_param('i', $order_id);
            if ($stmt->execute()) {
                echo "<div class='alert alert-success text-center'>Zamówienie ID $order_id zostało usunięte.</div>";
            } else {
                echo "<div class='alert alert-danger text-center'>Nie udało się usunąć zamówienia ID $order_id.</div>";
            }
        }
    }

    function displayOrders($orders_result, $is_pending = true) {
        global $conn;
        $orders = [];
        while ($row = $orders_result->fetch_assoc()) {
            $orders[$row['group_id']]['group_details'] = [
                'delivery_name' => $row['delivery_name'],
                'payment_name' => $row['payment_name'],
                'delivery_cost' => $row['delivery_cost'],
                'imie' => $row['imie'],
                'nazwisko' => $row['nazwisko']
            ];
            $orders[$row['group_id']]['orders'][] = $row;
        }
    
        foreach ($orders as $group_id => $group) {
            echo "<div class='order-group'>
                    <h4>Grupa Zamówień ID: $group_id</h4>
                    <p>Klient: {$group['group_details']['imie']} {$group['group_details']['nazwisko']}</p>
                    <p>Metoda dostawy: {$group['group_details']['delivery_name']}</p>
                    <p>Metoda płatności: {$group['group_details']['payment_name']}</p>
                    <p>Koszt dostawy: " . number_format($group['group_details']['delivery_cost'], 2) . " zł</p>";
            if ($is_pending) {
                echo "<form method='POST' style='display: inline-block;' onsubmit='return confirm(\"Czy na pewno chcesz zrealizować tę grupę zamówień?\");'>
                        <input type='hidden' name='group_id' value='$group_id'>
                        <button type='submit' name='action' value='complete' class='btn btn-complete'>Zrealizuj</button>
                    </form>";
            }
            echo "<form method='POST' style='display: inline-block;' onsubmit='return confirm(\"Czy na pewno chcesz usunąć tę grupę zamówień?\");'>
                    <input type='hidden' name='group_id' value='$group_id'>
                    <button type='submit' name='action' value='delete' class='btn btn-delete'>Usuń grupę</button>
                </form>";
            foreach ($group['orders'] as $order) {
                $created_at = $order['created_at'] ?? 'Brak daty utworzenia';
                $image_path = $order['product_image'] ?: 'default.jpg';
                echo "<div class='order-item'>
                        <img src='" . htmlspecialchars($image_path) . "' alt='Zdjęcie produktu'>
                        <div>
                            <p><strong>Produkt:</strong> {$order['product_name']}</p>
                            <p><strong>Ilość:</strong> {$order['amount']}</p>
                            <p><strong>Cena:</strong> " . number_format($order['price'], 2) . " zł</p>
                            <p><strong>Data utworzenia:</strong> {$created_at}</p>
                        </div>
                        <form method='POST' style='display: inline-block;' onsubmit='return confirm(\"Czy na pewno chcesz usunąć to zamówienie?\");'>
                            <input type='hidden' name='order_id' value='{$order['order_id']}'>
                            <button type='submit' name='action' value='delete_order' class='btn btn-delete'>Usuń</button>
                        </form>
                    </div>";
            }
            echo "</div>";
        }
    }

    echo "<div class='row'>
            <div class='col-md-6'>
                <h3 class='text-center mb-4'>Zamówienia w realizacji</h3>";
    $pending_orders_query = "
        SELECT 
            order_group.id AS group_id, 
            order_group.delivery_cost, 
            delivery_methods.name AS delivery_name, 
            payment_methods.name AS payment_name,
            zamowienia.product_id, 
            zamowienia.id AS order_id,
            zamowienia.imie, 
            zamowienia.nazwisko, 
            zamowienia.amount, 
            zamowienia.price, 
            zamowienia.status, 
            zamowienia.created_at, 
            zamowienia.closed_at, 
            products.name AS product_name,
            (SELECT image_path FROM product_images WHERE product_images.product_id = products.id LIMIT 1) AS product_image
        FROM order_group
        LEFT JOIN zamowienia ON zamowienia.order_group_id = order_group.id
        LEFT JOIN products ON zamowienia.product_id = products.id
        LEFT JOIN delivery_methods ON order_group.delivery_method_id = delivery_methods.id
        LEFT JOIN payment_methods ON order_group.payment_method_id = payment_methods.id
        WHERE zamowienia.status = 2 AND zamowienia.imie IS NOT NULL AND zamowienia.nazwisko IS NOT NULL
        ORDER BY order_group.id DESC
    ";

    $pending_orders_result = $conn->query($pending_orders_query);

    if ($pending_orders_result && $pending_orders_result->num_rows > 0) {
        displayOrders($pending_orders_result);
    } else {
        echo "<p class='text-center text-muted'>Brak zamówień w realizacji.</p>";
    }

    echo "</div>
          <div class='col-md-6'>
            <h3 class='text-center mb-4'>Zrealizowane zamówienia</h3>";

    $completed_orders_query = "
        SELECT 
            order_group.id AS group_id, 
            order_group.delivery_cost, 
            delivery_methods.name AS delivery_name, 
            payment_methods.name AS payment_name,
            zamowienia.product_id, 
            zamowienia.id AS order_id,
            zamowienia.imie, 
            zamowienia.nazwisko, 
            zamowienia.amount, 
            zamowienia.price, 
            zamowienia.status, 
            zamowienia.created_at, 
            zamowienia.closed_at, 
            products.name AS product_name,
            (SELECT image_path FROM product_images WHERE product_images.product_id = products.id LIMIT 1) AS product_image
        FROM order_group
        LEFT JOIN zamowienia ON zamowienia.order_group_id = order_group.id
        LEFT JOIN products ON zamowienia.product_id = products.id
        LEFT JOIN delivery_methods ON order_group.delivery_method_id = delivery_methods.id
        LEFT JOIN payment_methods ON order_group.payment_method_id = payment_methods.id
        WHERE zamowienia.status = 3 AND zamowienia.imie IS NOT NULL AND zamowienia.nazwisko IS NOT NULL
        ORDER BY order_group.id DESC
    ";

    $completed_orders_result = $conn->query($completed_orders_query);

    if ($completed_orders_result && $completed_orders_result->num_rows > 0) {
        displayOrders($completed_orders_result, false);
    } else {
        echo "<p class='text-center text-muted'>Brak zrealizowanych zamówień.</p>";
    }

    echo "</div>
          </div>";
    ?>
</div>

<script>
    function confirmCompletion() {
        return confirm("Czy na pewno chcesz zrealizować tę grupę zamówień?");
    }
    function confirmDelete() {
        return confirm("Czy na pewno chcesz usunąć ten element?");
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
