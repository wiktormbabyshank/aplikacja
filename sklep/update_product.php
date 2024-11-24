<?php
session_start();
include 'db_connection.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $productId = isset($_POST['id']) ? intval($_POST['id']) : null;
    $productName = isset($_POST['product_name']) ? $_POST['product_name'] : null;
    $productPrice = isset($_POST['product_price']) ? floatval($_POST['product_price']) : null;
    $productQuantity = isset($_POST['product_quantity']) ? intval($_POST['product_quantity']) : null;
    $productImages = isset($_FILES['product_images']) ? $_FILES['product_images'] : null;

    
    if ($productId === null || $productName === null || $productPrice === null || $productQuantity === null) {
        die("Brak wymaganych danych w formularzu.");
    }

    $conn->begin_transaction(); 

    try {
        
        $sqlProduct = "UPDATE products SET name = ?, price = ?, quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($sqlProduct);
        $stmt->bind_param("sdii", $productName, $productPrice, $productQuantity, $productId);
        $stmt->execute();

        
        if ($productImages && $productImages['error'][0] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/'; 
            foreach ($productImages['tmp_name'] as $key => $tmpName) {
                $imageName = basename($productImages['name'][$key]);
                $targetFile = $uploadDir . $imageName;

                if (move_uploaded_file($tmpName, $targetFile)) {
                    $sqlImage = "INSERT INTO product_images (product_id, image_path) VALUES (?, ?)";
                    $stmtImage = $conn->prepare($sqlImage);
                    $stmtImage->bind_param("is", $productId, $targetFile);
                    $stmtImage->execute();
                    $stmtImage->close();
                } else {
                    throw new Exception("Nie udało się przesłać pliku: $imageName");
                }
            }
        }

        $conn->commit();

        echo "Produkt został zaktualizowany.";
        header("Location: admin_dashboard.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        echo "Wystąpił błąd podczas aktualizacji: " . $e->getMessage();
    }
    $stmt->close();
    $conn->close();
} else {
    echo "Nieprawidłowe żądanie.";
}
?>
