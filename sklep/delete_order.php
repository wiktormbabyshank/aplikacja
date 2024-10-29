<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $order_id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM zamowienia WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();


    header("Location: admin_dashboard.php");
}

$conn->close();
?>
