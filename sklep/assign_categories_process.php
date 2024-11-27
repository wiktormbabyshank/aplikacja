<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $categories = $_POST['categories'] ?? [];

    $stmt_delete = $conn->prepare("DELETE FROM product_categories WHERE product_id = ?");
    $stmt_delete->bind_param("i", $product_id);
    $stmt_delete->execute();
    $stmt_delete->close();

    $stmt_insert = $conn->prepare("INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)");
    foreach ($categories as $category_id) {
        $category_id = intval($category_id);
        $stmt_insert->bind_param("ii", $product_id, $category_id);
        $stmt_insert->execute();
    }
    $stmt_insert->close();

    header("Location: admin_dashboard.php?message=Kategorie zostały przypisane");
}
?>
