<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $group_id = $_POST['group_id'] ?? null;
    $order_id = $_POST['order_id'] ?? null;

    if ($action === 'complete' && $group_id) {
        $update_query = "
            UPDATE zamowienia_users 
            SET status = 3, closed_at = NOW() 
            WHERE order_group_id = ?
        ";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('i', $group_id);
        $stmt->execute();
        header('Location: admin_dashboard_logged.php');
        exit;
    }

    if ($action === 'delete_group' && $group_id) {
        $delete_orders_query = "DELETE FROM zamowienia_users WHERE order_group_id = ?";
        $stmt = $conn->prepare($delete_orders_query);
        $stmt->bind_param('i', $group_id);
        $stmt->execute();

        $delete_group_query = "DELETE FROM order_group WHERE id = ?";
        $stmt = $conn->prepare($delete_group_query);
        $stmt->bind_param('i', $group_id);
        $stmt->execute();

        header('Location: admin_dashboard_logged.php');
        exit;
    }

    if ($action === 'delete_order' && $order_id) {
        $delete_order_query = "DELETE FROM zamowienia_users WHERE id = ?";
        $stmt = $conn->prepare($delete_order_query);
        $stmt->bind_param('i', $order_id);
        $stmt->execute();

        header('Location: admin_dashboard_logged.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administracyjny - Zamówienia Zalogowanych</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: bold; }
        .order-group { box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); border-radius: 10px; padding: 15px; background-color: white; margin-bottom: 20px; }
        .order-item { margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9; display: flex; align-items: center; }
        .order-item img { max-width: 80px; max-height: 80px; margin-right: 15px; }
        .order-item:last-child { margin-bottom: 0; }
        .btn-complete { background-color: #28a745; color: white; margin-right: 5px; }
        .btn-complete:hover { background-color: #218838; }
        .btn-delete { background-color: #dc3545; color: white; }
        .btn-delete:hover { background-color: #c82333; }
        .nav-link-btn { display: inline-block; padding: 10px 20px; margin: 5px; background-color: #007bff; color: white; text-align: center; text-decoration: none; font-size: 16px; border-radius: 8px; transition: background-color 0.3s; }
        .nav-link-btn:hover { background-color: #0056b3; }
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
        <h1>Panel Administracyjny - Zamówienia Zalogowanych</h1>
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

    <section id="orders_logged_users">
        <div class="row">
            <div class="col-md-6">
                <h3 class="text-center mb-4">Zamówienia w realizacji</h3>
                <?php displayOrders($conn, 2, true); ?>
            </div>

            <div class="col-md-6">
                <h3 class="text-center mb-4">Zrealizowane zamówienia</h3>
                <?php displayOrders($conn, 3, false); ?>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
function displayOrders($conn, $status, $showCompleteButton) {
    $query = "
        SELECT 
            order_group.id AS group_id, 
            delivery_methods.name AS delivery_name, 
            payment_methods.name AS payment_name, 
            order_group.delivery_cost, 
            uzytkownicy.imie, 
            uzytkownicy.nazwisko,
            zamowienia_users.id AS order_id, 
            zamowienia_users.amount, 
            zamowienia_users.price, 
            zamowienia_users.created_at, 
            zamowienia_users.closed_at, 
            zamowienia_users.status,
            products.name AS product_name,
            (SELECT image_path FROM product_images WHERE product_id = products.id LIMIT 1) AS product_image
        FROM order_group
        LEFT JOIN zamowienia_users ON zamowienia_users.order_group_id = order_group.id
        LEFT JOIN uzytkownicy ON zamowienia_users.user_id = uzytkownicy.id
        LEFT JOIN products ON zamowienia_users.product_id = products.id
        LEFT JOIN delivery_methods ON order_group.delivery_method_id = delivery_methods.id
        LEFT JOIN payment_methods ON order_group.payment_method_id = payment_methods.id
        WHERE zamowienia_users.status = ?
        ORDER BY order_group.id DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $status);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[$row['group_id']]['group_details'] = [
                'delivery_name' => $row['delivery_name'],
                'payment_name' => $row['payment_name'],
                'delivery_cost' => $row['delivery_cost'],
                'user_name' => $row['imie'] . ' ' . $row['nazwisko']
            ];
            $orders[$row['group_id']]['orders'][] = $row;
        }

        foreach ($orders as $group_id => $group) {
            echo "<div class='order-group'>
                    <h4>Grupa Zamówień ID: $group_id</h4>
                    <p>Użytkownik: {$group['group_details']['user_name']}</p>
                    <p>Metoda dostawy: {$group['group_details']['delivery_name']}</p>
                    <p>Metoda płatności: {$group['group_details']['payment_name']}</p>
                    <p>Koszt dostawy: " . number_format($group['group_details']['delivery_cost'], 2) . " zł</p>";
            if ($showCompleteButton) {
                echo "<form method='POST' style='display:inline-block;' onsubmit='return confirm(\"Czy na pewno chcesz zrealizować tę grupę zamówień?\");'>
                        <input type='hidden' name='group_id' value='$group_id'>
                        <input type='hidden' name='action' value='complete'>
                        <button type='submit' class='btn btn-complete'>Zrealizuj</button>
                      </form>";
            }
            echo "<form method='POST' style='display:inline-block;' onsubmit='return confirm(\"Czy na pewno chcesz usunąć tę grupę zamówień?\");'>
                    <input type='hidden' name='group_id' value='$group_id'>
                    <input type='hidden' name='action' value='delete_group'>
                    <button type='submit' class='btn btn-delete'>Usuń grupę</button>
                  </form>";
            foreach ($group['orders'] as $order) {
                $image_path = $order['product_image'] ?: 'default.jpg';
                echo "<div class='order-item'>
                        <img src='" . htmlspecialchars($image_path) . "' alt='Zdjęcie produktu'>
                        <div>
                            <p><strong>Produkt:</strong> {$order['product_name']}</p>
                            <p><strong>Ilość:</strong> {$order['amount']}</p>
                            <p><strong>Cena:</strong> " . number_format($order['price'], 2) . " zł</p>
                            <p><strong>Data utworzenia:</strong> {$order['created_at']}</p>";
                if ($status === 3) {
                    echo "<p><strong>Data zamknięcia:</strong> {$order['closed_at']}</p>";
                }
                echo "</div>
                      <form method='POST' style='display:inline-block;' onsubmit='return confirm(\"Czy na pewno chcesz usunąć to zamówienie?\");'>
                          <input type='hidden' name='order_id' value='{$order['order_id']}'>
                          <input type='hidden' name='action' value='delete_order'>
                          <button type='submit' class='btn btn-delete'>Usuń</button>
                      </form>
                      </div>";
            }
            echo "</div>";
        }
    } else {
        echo "<p class='text-center text-muted'>Brak zamówień w tej sekcji.</p>";
    }
}
?>
