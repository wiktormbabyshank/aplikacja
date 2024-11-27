<?php
session_start();
include('db_connection.php');
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administracyjny</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .admin-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .admin-section {
            margin-bottom: 50px;
        }
        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        .card-body {
            padding: 20px;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .btn-action {
            font-size: 14px;
            border-radius: 5px;
            padding: 10px 15px;
        }
        .btn-edit {
            background-color: #28a745;
            color: white;
        }
        .btn-edit:hover {
            background-color: #218838;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .btn-add {
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            font-size: 16px;
            padding: 10px 20px;
            text-align: center;
            display: block;
            margin: 20px auto 0;
        }
        .btn-add:hover {
            background-color: #0056b3;
        }
        .nav-links {
            text-align: center;
            margin-bottom: 40px;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .nav-link-btn {
            display: inline-block;
            padding: 15px 25px;
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
    <header class="admin-header">
        <h1>Witaj w Panelu Administracyjnym</h1>
        <p>Zarządzaj produktami, użytkownikami, zamówieniami i innymi ustawieniami w jednym miejscu.</p>
    </header>

    <div class="nav-links">
        <a href="#products" class="nav-link-btn">Zarządzaj Produktami</a>
        <a href="#users" class="nav-link-btn">Zarządzaj Użytkownikami</a>
        <a href="#orders" class="nav-link-btn">Zarządzaj Zamówieniami</a>
        <a href="#pages" class="nav-link-btn">Zarządzaj Podstronami</a>
        <a href="#delivery_methods" class="nav-link-btn">Zarządzaj Sposobami Dostawy</a>
        <a href="#payments" class="nav-link-btn">Zarządzaj Sposobami Płatności</a>
    </div>

    <section id="products" class="admin-section">
        <div class="card">
            <div class="card-header">Zarządzaj Produktami</div>
            <div class="card-body">
                <a href="add_product.php" class="btn btn-add">Dodaj Nowy Produkt</a>
                <table class="table table-bordered table-hover mt-3">
                    <thead>
                        <tr>
                            <th>Nazwa</th>
                            <th>Cena</th>
                            <th>Ilość</th>
                            <th>Obrazy</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM products");
                        while ($row = $result->fetch_assoc()) {
                            $productId = $row['id'];
                            $imageResult = $conn->query("SELECT image_path FROM product_images WHERE product_id = $productId");
                            $images = [];
                            while ($imageRow = $imageResult->fetch_assoc()) {
                                $images[] = htmlspecialchars($imageRow['image_path']);
                            }
                            $imageHtml = empty($images) ? 'Brak obrazów' : implode(' ', array_map(fn($path) => "<img src='$path' style='width: 50px; height: auto;'>", $images));
                            echo "<tr>
                                <td>{$row['name']}</td>
                                <td>{$row['price']} PLN</td>
                                <td>{$row['quantity']}</td>
                                <td>$imageHtml</td>
                                <td>
                                    <a href='edit_product.php?id={$row['id']}' class='btn btn-edit btn-action'>Edytuj</a>
                                    <form action='delete_product.php' method='post' style='display: inline;' onsubmit='return confirmDelete();'>
                                        <input type='hidden' name='id' value='{$row['id']}'>
                                        <button type='submit' class='btn btn-delete btn-action'>Usuń</button>
                                    </form>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section id="users" class="admin-section">
        <div class="card">
            <div class="card-header">Zarządzaj Użytkownikami</div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Imię</th>
                            <th>Nazwisko</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM uzytkownicy");
                        while ($user = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>{$user['imie']}</td>
                                <td>{$user['nazwisko']}</td>
                                <td>{$user['email']}</td>
                                <td>{$user['status']}</td>
                                <td>
                                    <a href='edit_user.php?id={$user['id']}' class='btn btn-edit btn-action'>Edytuj</a>
                                    <form action='delete_user.php' method='post' style='display: inline;' onsubmit='return confirmDelete();'>
                                        <input type='hidden' name='id' value='{$user['id']}'>
                                        <button type='submit' class='btn btn-delete btn-action'>Usuń</button>
                                    </form>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section id="orders" class="admin-section">
        <div class="card">
            <div class="card-header">Zarządzaj Zamówieniami</div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID Zamówienia</th>
                            <th>Użytkownik</th>
                            <th>Kwota</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM zamowienia");
                        while ($order = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>{$order['id']}</td>
                                <td>{$order['imie']} {$order['nazwisko']}</td>
                                <td>{$order['amount']} PLN</td>
                                <td>{$order['status']}</td>
                                <td>{$order['created_at']}</td>
                                <td>
                                    <a href='edit_order.php?id={$order['id']}' class='btn btn-edit btn-action'>Edytuj</a>
                                    <form action='delete_order.php' method='post' style='display: inline;' onsubmit='return confirmDelete();'>
                                        <input type='hidden' name='id' value='{$order['id']}'>
                                        <button type='submit' class='btn btn-delete btn-action'>Usuń</button>
                                    </form>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <section id="pages" class="admin-section">
    <div class="card">
        <div class="card-header">Zarządzaj Podstronami Informacyjnymi</div>
        <div class="card-body">
            <a href="add_page.php" class="btn btn-add">Dodaj Nową Podstronę</a>
            <table class="table table-bordered table-hover mt-3">
                <thead>
                    <tr>
                        <th>Tytuł</th>
                        <th>Slug</th>
                        <th>Data Utworzenia</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM pages");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row['title']) . "</td>
                            <td>" . htmlspecialchars($row['slug']) . "</td>
                            <td>" . htmlspecialchars($row['created_at']) . "</td>
                            <td>
                                <a href='edit_page.php?id={$row['id']}' class='btn btn-edit btn-action'>Edytuj</a>
                                <form action='delete_page.php' method='post' style='display: inline;' onsubmit='return confirmDelete();'>
                                    <input type='hidden' name='id' value='{$row['id']}'>
                                    <button type='submit' class='btn btn-delete btn-action'>Usuń</button>
                                </form>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<section id="delivery_methods" class="admin-section">
    <div class="card">
        <div class="card-header">Zarządzaj Sposobami Dostawy</div>
        <div class="card-body">
            <a href="add_delivery_method.php" class="btn btn-add">Dodaj Nową Metodę Dostawy</a>
            <table class="table table-bordered table-hover mt-3">
                <thead>
                    <tr>
                        <th>Nazwa</th>
                        <th>Koszt</th>
                        <th>Opis</th>
                        <th>Data Utworzenia</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM delivery_methods");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row['name']) . "</td>
                            <td>" . htmlspecialchars($row['cost']) . " zł</td>
                            <td>" . htmlspecialchars($row['description']) . "</td>
                            <td>" . htmlspecialchars($row['created_at']) . "</td>
                            <td>
                                <a href='edit_delivery_method.php?id={$row['id']}' class='btn btn-edit btn-action'>Edytuj</a>
                                <form action='delete_delivery_method.php' method='post' style='display: inline;' onsubmit='return confirm(\"Czy na pewno chcesz usunąć tę metodę dostawy?\");'>
                                    <input type='hidden' name='id' value='{$row['id']}'>
                                    <button type='submit' class='btn btn-delete btn-action'>Usuń</button>
                                </form>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<section id="payments" class="admin-section">
    <div class="card">
        <div class="card-header">Zarządzaj Sposobami Płatności</div>
        <div class="card-body">
            <a href="add_payment_method.php" class="btn btn-add">Dodaj Nowy Sposób Płatności</a>
            <table class="table table-bordered table-hover mt-3">
                <thead>
                    <tr>
                        <th>Nazwa</th>
                        <th>Opis</th>
                        <th>Data Utworzenia</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM payment_methods");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row['name']) . "</td>
                            <td>" . htmlspecialchars($row['description']) . "</td>
                            <td>" . htmlspecialchars($row['created_at']) . "</td>
                            <td>
                                <a href='edit_payment_method.php?id={$row['id']}' class='btn btn-edit btn-action'>Edytuj</a>
                                <form action='delete_payment_method.php' method='post' style='display: inline;' onsubmit='return confirmDelete();'>
                                    <input type='hidden' name='id' value='{$row['id']}'>
                                    <button type='submit' class='btn btn-delete btn-action'>Usuń</button>
                                </form>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmDelete() {
        return confirm("Czy na pewno chcesz usunąć ten element?");
    }
</script>

</body>
</html>
