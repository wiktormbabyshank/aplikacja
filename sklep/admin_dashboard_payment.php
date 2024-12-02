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
        <a href="admin_dashboard.php" class="nav-link-btn">Zarządzaj Produktami</a>
        <a href="admin_dashboard_kat.php" class="nav-link-btn">Zarządzaj Kategoriami</a>
        <a href="admin_dashboard_user.php" class="nav-link-btn">Zarządzaj Użytkownikami</a>
        <a href="admin_dashboard_logged.php" class="nav-link-btn">Zarządzaj Zamówieniami Zalogowanych</a>
        <a href="admin_dashboard_unlogged.php" class="nav-link-btn">Zarządzaj Zamówieniami Niezalogowanych</a>
        <a href="admin_dashboard_pages.php" class="nav-link-btn">Zarządzaj Podstronami</a>
        <a href="admin_dashboard_delivery.php" class="nav-link-btn">Zarządzaj Sposobami Dostawy</a>
        <a href="admin_dashboard_payment.php" class="nav-link-btn">Zarządzaj Sposobami Płatności</a>
    </div>

    
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
