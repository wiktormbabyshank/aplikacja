<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $parameters = $_POST['parameters'] ?? [];

    $stmt_delete = $conn->prepare("DELETE FROM product_parameters WHERE product_id = ?");
    $stmt_delete->bind_param("i", $product_id);
    $stmt_delete->execute();
    $stmt_delete->close();

    $stmt_insert = $conn->prepare("INSERT INTO product_parameters (product_id, parameter_id, value) VALUES (?, ?, ?)");
    foreach ($parameters as $parameter_id => $value) {
        $value = trim($value);
        if (!empty($value)) {
            $stmt_insert->bind_param("iis", $product_id, $parameter_id, $value);
            $stmt_insert->execute();
        }
    }
    $stmt_insert->close();

    header("Location: admin_dashboard.php?message=Parametry zostały przypisane");
}
?>
