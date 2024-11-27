<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);

    $query_product = "SELECT name, price, quantity FROM products WHERE id = ?";
    $stmt_product = $conn->prepare($query_product);
    $stmt_product->bind_param("i", $product_id);
    $stmt_product->execute();
    $result_product = $stmt_product->get_result();

    if ($result_product->num_rows > 0) {
        $product = $result_product->fetch_assoc();

        $query_images = "SELECT image_path FROM product_images WHERE product_id = ?";
        $stmt_images = $conn->prepare($query_images);
        $stmt_images->bind_param("i", $product_id);
        $stmt_images->execute();
        $result_images = $stmt_images->get_result();
        ?>
        <!DOCTYPE html>
        <html lang="pl">
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <title>Szczegóły produktu</title>
            <style>
                body {
                    background-color: #f4f4f4;
                }

                .product-details {
                    margin-top: 30px;
                    background-color: white;
                    border-radius: 10px;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                    padding: 30px;
                    border: 1px solid #ddd;
                    position: relative;
                }

                .product-images img {
                    max-width: 100%;
                    max-height: 400px;
                    object-fit: cover;
                    border-radius: 10px;
                    border: 1px solid #ddd;
                    cursor: pointer;
                    transition: transform 0.3s ease;
                    margin: 10px;  
                }

                .product-images img:hover {
                    transform: scale(1.05); 
                }

                .product-info {
                    margin-top: 20px;
                }

                .product-info h3, .product-info h4 {
                    color: #333;
                }

                .btn-order {
                    background-color: #28a745;
                    color: white;
                    padding: 10px 20px;
                    border-radius: 5px;
                    border: none;
                    cursor: pointer;
                    width: 100%;
                    transition: background-color 0.3s;
                    margin-bottom: 10px;
                }

                .btn-order:hover {
                    background-color: #218838;
                }

                .btn-back {
                    background-color: #007bff;
                    color: white;
                    padding: 10px 20px;
                    border-radius: 5px;
                    border: none;
                    width: 100%;
                    transition: background-color 0.3s;
                    margin-top: 20px;
                }

                .btn-back:hover {
                    background-color: #0056b3;
                }

                .back-to-store-btn {
                    position: absolute;
                    top: 10px;
                    left: 10px;
                    background-color: #007bff;
                    color: white;
                    padding: 10px 20px;
                    border-radius: 5px;
                    border: none;
                    font-size: 1rem;
                    transition: background-color 0.3s;
                }

                .back-to-store-btn:hover {
                    background-color: #0056b3;
                }

                .modal {
                    display: none;
                    position: fixed;
                    z-index: 1;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    overflow: auto;
                    background-color: rgba(0, 0, 0, 0.9);
                }

                .modal-content {
                    margin: auto;
                    display: block;
                    max-width: 80%;
                    max-height: 80%;
                    animation: zoomIn 0.3s ease;
                }

                .close {
                    position: absolute;
                    top: 15px;
                    right: 35px;
                    color: #fff;
                    font-size: 40px;
                    font-weight: bold;
                    cursor: pointer;
                }

                .product-images {
                    display: flex;
                    justify-content: center;  
                    align-items: center;      
                    flex-wrap: wrap;          
                    gap: 20px;                
                }

                .close:hover,
                .close:focus {
                    color: #bbb;
                    text-decoration: none;
                    cursor: pointer;
                }

                @keyframes zoomIn {
                    from {
                        transform: scale(0);
                    }
                    to {
                        transform: scale(1);
                    }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <a href="dashboard.php" class="back-to-store-btn">Powrót do sklepu</a>

                <div class="product-details">
                    <h1 class="text-center">Szczegóły produktu</h1>

                    <div class="product-images">
                        <?php
                        while ($image = $result_images->fetch_assoc()) {
                            echo "<img src='" . htmlspecialchars($image['image_path']) . "' alt='Product Image' class='zdjeciaprod' onclick='openModal(this.src)'>";
                        }
                        ?>
                    </div>

                    <div class="product-info text-center mt-4">
                        <h2 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h2>
                        <h3>Cena: <?php echo htmlspecialchars($product['price']); ?> zł</h3>
                        <h4>Dostępna ilość: <?php echo htmlspecialchars($product['quantity']); ?></h4>

                        <form action="place_order.php" method="post">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
                            <button type="submit" class="btn-order">Złóż zamówienie</button>
                        </form>
                    </div>
                </div>
            </div>

            <div id="myModal" class="modal">
                <span class="close" onclick="closeModal()">&times;</span>
                <img class="modal-content" id="img01">
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

            <script>
                function openModal(imageSrc) {
                    var modal = document.getElementById("myModal");
                    var modalImg = document.getElementById("img01");
                    modal.style.display = "block";
                    modalImg.src = imageSrc;
                }

                function closeModal() {
                    var modal = document.getElementById("myModal");
                    modal.style.display = "none";
                }
            </script>
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
