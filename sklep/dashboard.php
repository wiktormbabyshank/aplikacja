<!DOCTYPE html>
<html lang="pl">
<head>
    <meta name="viewport" charset="UTF-8" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="body-user">
    <div class="stopka2">
        <?php
        session_start();
        include('db_connection.php');

        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            header("Location: index.html");
            exit;
        }

        echo "<h1 class='powitanie'>Witaj, " . htmlspecialchars($_SESSION['imie']) . "!</h1>";
        echo "<p class = 'informacja_o_zalogowaniu'>Zalogowany jako: " . htmlspecialchars($_SESSION['email']) . "</p>";
        ?>

        <!-- Menu wyboru -->
        <nav>
            <ul>
                <li><a href="user_edit.php">Panel klienta</a></li>
                <li><a href="logout.php">Wyloguj się</a></li>
            </ul>
        </nav>
        
        <form action="logout.php" method="post">
            <button type="submit" class="button-user">Wyloguj się</button>
        </form>
    </div>


    <div class="produkty">
        <?php
            // Pobieranie produktów
            include('db_connection.php');
            $query = "SELECT products.id, products.name, products.price, products.quantity, product_images.image_path 
                      FROM products 
                      INNER JOIN product_images ON products.id = product_images.product_id";
            $result = $conn->query($query);

            $current_product_id = null;

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {

                    // Pokaż produkt
                    if ($current_product_id !== $row['id']) {
                        if ($current_product_id !== null) {
                            echo "</div>"; 
                        }
                        $current_product_id = $row['id'];
                        echo "<div class='okienko_produktu'>";
                        echo "<form action='product_page.php'>";
                        echo "<h1>" . htmlspecialchars($row['name']) . "</h1>";
                        echo "<h2>" . htmlspecialchars($row['price']) . " zł</h2>";
                        echo "<h3>Ilość: " . htmlspecialchars($row['quantity']) . "</h3>";
                        echo "<button type='submit' class='button-user'>Kup</button>";
                    }

                    // Wyświetlanie obrazka
                    echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt='Product Image' class='zdjeciaprod'>";
                }

                echo "</div>";
            } else {
                echo "<p>Brak produktów do wyświetlenia.</p>";
            }
        ?>
    </div>
</body>
</html>
