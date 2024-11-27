<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['id']) ? intval($_POST['id']) : null;
    $productName = isset($_POST['product_name']) ? $_POST['product_name'] : null;
    $productPrice = isset($_POST['product_price']) ? floatval($_POST['product_price']) : null;
    $productQuantity = isset($_POST['product_quantity']) ? intval($_POST['product_quantity']) : null;
    $productImages = isset($_FILES['product_images']) ? $_FILES['product_images'] : null;
    $deleteImages = isset($_POST['delete_images']) ? $_POST['delete_images'] : [];
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    $parameters = isset($_POST['parameters']) ? $_POST['parameters'] : [];

    if ($productId === null || $productName === null || $productPrice === null || $productQuantity === null) {
        die("Brak wymaganych danych w formularzu.");
    }

    $conn->begin_transaction();

    try {
        $sqlProduct = "UPDATE products SET name = ?, price = ?, quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($sqlProduct);
        $stmt->bind_param("sdii", $productName, $productPrice, $productQuantity, $productId);
        $stmt->execute();

        if (!empty($deleteImages)) {
            foreach ($deleteImages as $imageId) {
                $imageId = intval($imageId);

                $result = $conn->query("SELECT image_path FROM product_images WHERE id = $imageId");
                $image = $result->fetch_assoc();

                if ($image && file_exists($image['image_path'])) {
                    unlink($image['image_path']); 
                }

                $conn->query("DELETE FROM product_images WHERE id = $imageId"); 
            }
        }

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

        $conn->query("DELETE FROM product_categories WHERE product_id = $productId"); 
        if (!empty($categories)) {
            $stmtCategory = $conn->prepare("INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)");
            foreach ($categories as $categoryId) {
                $categoryId = intval($categoryId);
                $stmtCategory->bind_param("ii", $productId, $categoryId);
                $stmtCategory->execute();
            }
            $stmtCategory->close();
        }

        $conn->query("DELETE FROM product_parameters WHERE product_id = $productId");
        if (!empty($parameters)) {
            $stmtParameter = $conn->prepare("INSERT INTO product_parameters (product_id, parameter_id, value) VALUES (?, ?, ?)");
            foreach ($parameters as $parameterId => $value) {
                $parameterId = intval($parameterId);
                $value = trim($value);
                if (!empty($value)) {
                    $stmtParameter->bind_param("iis", $productId, $parameterId, $value);
                    $stmtParameter->execute();
                }
            }
            $stmtParameter->close();
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
