<!DOCTYPE html>
<html lang="pl">
<head>
    <meta name="viewport" charset="UTF-8" content="width=device-width, initial-scale=1">
    
    <style type="text/css">
        .produkty {
            display: flex;
            flex-wrap: wrap; 
            justify-content: flex-start; 
            gap: 20px; 
            padding: 20px;
        }
        .zdjeciaprod {
            width: 100px;  
            height: 100px;
            object-fit: cover; 
            margin-bottom: 10px; 
        }
        .stopka1 {
            background-color: #28a745;
            margin: 0px;
            box-shadow: 0 0 20px grey;
            text-align: right;
        }
        .okienko_produktu {
            margin:10px;
            border: 1px solid black;
            box-shadow: 0 0 20px grey;
            padding: 10px;
            border-radius: 10px;
        }
        .okienko_produktu img {
            display: inline-block;
            margin-bottom: 10px;
        }
        button{
            background-color: #28a745;
            border: solid 1px black;
            color: white;
        }
        button:hover{
            background-color: black;
            color: white;
        }
        body{
            box-shadow: 0 0 20px grey;
            margin: 0px;
        }
    </style>
</head>
<body>
    <div class="stopka1">
        <?php
        session_start();
        include('db_connection.php');

        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            header("Location: login.html");
            exit;
        }

        echo "<h1 class='powitanie'>Witaj, " . htmlspecialchars($_SESSION['imie']) . "!</h1>";
        echo "<p class = 'informacja_o_zalogowaniu'>Zalogowany jako: " . htmlspecialchars($_SESSION['email']) . "</p>";
        ?>

        <form action="logout.php" method="post">
            <button type="submit">Wyloguj się</button>
        </form>
    </div>

    <div class="produkty">
        <?php
            include('db_connection.php');

           
            $query = "SELECT products.id, products.name, products.price, products.quantity, product_images.image_path 
                      FROM products 
                      INNER JOIN product_images ON products.id = product_images.product_id";

            $result = $conn->query($query);

            
            $current_product_id = null;

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {

                    
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
                        echo "<button type='submit'>Kup</button>";
                    }

                   
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
