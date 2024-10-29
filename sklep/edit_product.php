<?php
session_start();
if (!isset($_SESSION['admin_email'])) {
    header("Location: index.html");
    exit();
}


include('db_connection.php');


if (isset($_GET['id'])) {
    $productId = $_GET['id'];
    $result = $conn->query("SELECT * FROM products WHERE id = $productId");
    $product = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj Produkt</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 600px;
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

        input[type="text"],
        input[type="number"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
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
            font-size: 16px;
            transition: background-color 0.3s; 
        }

        .back-button:hover {
            background-color: #0056b3; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edytuj Produkt</h1>
        <form action="update_product.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
            <input type="text" name="product_name" placeholder="Nazwa produktu" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            <input type="number" name="product_price" placeholder="Cena produktu" value="<?php echo htmlspecialchars($product['price']); ?>" required step="0.01">
            <input type="number" name="product_quantity" placeholder="Ilość" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
            <input type="file" name="product_images[]" multiple>
            <button type="submit">Zaktualizuj produkt</button>
        </form>
    </div>

    <a href="admin_dashboard.php" class="back-button">Powrót do panelu</a>
</body>
</html>
