<?php
session_start();
include('db_connection.php');


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['product_name'], $_POST['product_price'], $_POST['product_quantity'])) {
        $name = trim($_POST['product_name']);
        $price = (float)$_POST['product_price'];
        $quantity = (int)$_POST['product_quantity'];

        if (empty($name) || $price <= 0 || $quantity <= 0) {
            echo "Brak wymaganych danych!";
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO products (name, price, quantity) VALUES (?, ?, ?)");
        if (!$stmt) {
            die("Błąd przygotowania zapytania: " . $conn->error);
        }
        
        $stmt->bind_param("sdi", $name, $price, $quantity);
        $stmt->execute();
        $productId = $stmt->insert_id;
        $stmt->close();

  
        if (isset($_FILES['product_images']) && $_FILES['product_images']['error'][0] == UPLOAD_ERR_OK) {
            foreach ($_FILES['product_images']['tmp_name'] as $key => $tmpName) {
                $imageName = basename($_FILES['product_images']['name'][$key]);
                $targetPath = "uploads/" . uniqid() . "_" . $imageName;

                if (move_uploaded_file($tmpName, $targetPath)) {

                    $imageStmt = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
                    if ($imageStmt) {
                        $imageStmt->bind_param("is", $productId, $targetPath);
                        $imageStmt->execute();
                        $imageStmt->close();
                    } else {
                        echo "Błąd przygotowania zapytania dla obrazu: " . $conn->error;
                    }
                } else {
                    echo "Błąd podczas przesyłania pliku: $imageName";
                }
            }
        }

     
        header("Location: admin_dashboard.php");
        exit();
    } 
}
?>
 

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj Produkt</title>
    <link rel="stylesheet" href="styles.css">
  <style>
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
        <h1>Dodaj Nowy Produkt</h1>
        <form action="add_product.php" method="post" enctype="multipart/form-data">
            <input type="text" name="product_name" placeholder="Nazwa produktu" required>
            <input type="number" name="product_price" placeholder="Cena produktu" required step="0.01">
            <input type="number" name="product_quantity" placeholder="Ilość" required>
            <input type="file" name="product_images[]" multiple required>
            <button type="submit">Dodaj nowy produkt</button>
        </form>
        <a href="admin_dashboard.php" class="back-button">Powrót do panelu</a>
    </div>
</body>
</html>
