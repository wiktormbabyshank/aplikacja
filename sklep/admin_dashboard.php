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
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .container1 {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        nav ul {
            list-style-type: none;
            padding: 0;
            text-align: center;
        }

        nav ul li {
            display: inline;
            margin: 0 15px;
        }

        nav ul li a {
            text-decoration: none;
            color: #007BFF;
            font-weight: bold;
        }

        nav ul li a:hover {
            text-decoration: underline;
        }

        button {
            padding: 10px;
            background-color: #28a745; 
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            display: inline-block;
            margin-top: 10px; 
        }

        button:hover {
            background-color: #218838;
        }

        .back-button {
            position: fixed; 
            bottom: 20px;
            right: 20px; 
            background-color: #007BFF; 
            color: white; 
            border: none; 
            padding: 10px 20px;
            border-radius: 5px; 
            cursor: pointer; 
            text-decoration: none; 
            font-size: 16px;
            transition: background-color 0.3s; 
        }

        .back-button:hover {
            background-color: #0056b3; 
        }
    </style>
</head>
<body>
    <div class="container1">
        <h1>Panel Administracyjny</h1>
        <nav>
            <ul>
                <li><a href="#products">Zarządzaj produktami</a></li>
                <li><a href="#orders">Zarządzaj zamówieniami</a></li>
                <li><a href="#users">Zarządzaj użytkownikami</a></li>
            </ul>
        </nav>

        <form action="logout.php" method="post">
            <button type="submit">Wyloguj się</button>
        </form>

     <section id="products">
    <h2>Zarządzaj produktami</h2>


    <form action="add_product.php" method="post" enctype="multipart/form-data">
        <button type="submit">Dodaj nowy produkt</button>
    </form>


    <table>
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
 
            include('db_connection.php');
            $result = $conn->query("SELECT * FROM products");
            while ($row = $result->fetch_assoc()) {
                $productId = $row['id'];
                

                $imageResult = $conn->query("SELECT image_path FROM product_images WHERE product_id = $productId");
                

                $images = [];
                while ($imageRow = $imageResult->fetch_assoc()) {
                    $images[] = htmlspecialchars($imageRow['image_path']);
                }
                

                $imageHtml = '';
                if (!empty($images)) {
                    foreach ($images as $imagePath) {
                        $imageHtml .= "<img src='$imagePath' alt='Product Image' style='width: 100px; height: auto; margin-right: 5px;'/>";
                    }
                } else {
                    $imageHtml = 'Brak obrazów';
                }
                
                echo "<tr>
                    <td>{$row['name']}</td>
                    <td>{$row['price']} PLN</td>
                    <td>{$row['quantity']}</td>
                    <td>$imageHtml</td>
                    <td>
                        <form action='edit_product.php' method='get' style='display:inline;'>
                            <input type='hidden' name='id' value='{$row['id']}'>
                            <button type='submit'>Edytuj</button>
                        </form>
                        <form action='delete_product.php' method='post' style='display:inline;' onsubmit='return confirmDelete();'>
                            <input type='hidden' name='id' value='{$row['id']}'>
                            <button type='submit'>Usuń</button>
                        </form>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</section>


<script>
function confirmDelete() {
    return confirm("Czy na pewno chcesz usunąć ten produkt?");
}
</script>



<script>
function changeImage(button, direction) {
    const slider = button.parentElement;
    const images = slider.getElementsByTagName('img');
    let currentIndex = Array.from(images).findIndex(img => img.style.display === 'block');


    images[currentIndex].style.display = 'none';


    let newIndex = (currentIndex + direction + images.length) % images.length;


    images[newIndex].style.display = 'block';
}
</script>

      <section id="users">
    <h2>Zarządzaj użytkownikami</h2>
    <table>
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
                        <td>" . htmlspecialchars($user['imie']) . "</td>
                        <td>" . htmlspecialchars($user['nazwisko']) . "</td>
                        <td>" . htmlspecialchars($user['email']) . "</td>
                        <td>" . htmlspecialchars($user['status']) . "</td>
                        <td>
                            <form action='edit_user.php' method='get' style='display:inline;'>
                                <input type='hidden' name='id' value='" . $user['id'] . "'>
                                <button type='submit' class='edit-button'>Edytuj</button>
                            </form>
                                <form action='delete_user.php' method='post' style='display:inline;' onsubmit='return confirmDelete();'>
    <input type='hidden' name='id' value='" . $user['id'] . "'>
    <button type='submit' class='delete-button'>Usuń</button>
</form>
                        </td>
                    </tr>";
            }
            ?>
        </tbody>
      <script>
function confirmDelete() {
    return confirm("Czy na pewno chcesz usunąć tego użytkownika?");
}
</script>
    </table>
</section>



        <section id="orders">
    <h2>Zarządzaj zamówieniami</h2>
    <table>
        <thead>
            <tr>
                <th>ID Zamówienia</th>
                <th>Imię i Nazwisko Użytkownika</th>
                <th>Kwota</th>
                <th>Status</th>
                <th>Data Złożenia</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php

            $result = $conn->query("
                SELECT zamowienia.id AS order_id,
                       uzytkownicy.imie,
                       uzytkownicy.nazwisko,
                       zamowienia.amount,
                       zamowienia.status,
                       zamowienia.created_at
                FROM zamowienia
                JOIN uzytkownicy ON zamowienia.user_id = uzytkownicy.id
            ");

            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['order_id']}</td>
                    <td>{$row['imie']} {$row['nazwisko']}</td>
                    <td>{$row['amount']} PLN</td>
                    <td>{$row['status']}</td>
                    <td>{$row['created_at']}</td>
                    <td>
                        <form action='edit_order.php' method='get' style='display:inline;'>
                            <input type='hidden' name='id' value='{$row['order_id']}'>
                            <button type='submit'>Edytuj</button>
                        </form>
                        <form action='delete_order.php' method='post' style='display:inline;' onsubmit='return confirmDelete();'>
    <input type='hidden' name='id' value='{$row['order_id']}'>
    <button type='submit' class='delete-button'>Usuń</button>
</form>

                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</section>


    </div>
    
</body>
</html>