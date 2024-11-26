<?php
session_start();
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

<body class="body-editp">
    <div class="container4">
        <h1>Edytuj Produkt</h1>
        <form action="update_product.php" method="post" enctype="multipart/form-data">
            <input type="hidden" class="input-editp" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
            <input type="text" class="input-editp" name="product_name" placeholder="Nazwa produktu" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            <input type="number" class="input-editp" name="product_price" placeholder="Cena produktu" value="<?php echo htmlspecialchars($product['price']); ?>" required step="0.01">
            <input type="number" class="input-editp" name="product_quantity" placeholder="Ilość" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
            <input type="file" class="input-editp" name="product_images[]" multiple>
            <button type="submit" class="button-editp">Zaktualizuj produkt</button>
        </form>
    </div>

    <a href="admin_dashboard.php" class="back-button">Powrót do panelu</a>
</body>
</html>
