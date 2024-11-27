<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $id = intval($_POST['id']);

        $query_check_orders = "SELECT COUNT(*) AS count FROM zamowienia WHERE delivery_method_id = ?";
        $stmt_check_orders = $conn->prepare($query_check_orders);
        $stmt_check_orders->bind_param("i", $id);
        $stmt_check_orders->execute();
        $result_check_orders = $stmt_check_orders->get_result();
        $order_count = $result_check_orders->fetch_assoc()['count'];

        if ($order_count > 0) {
            echo "Nie można usunąć tej metody dostawy, ponieważ jest używana w zamówieniach.";
        } else {
            $query_delete = "DELETE FROM delivery_methods WHERE id = ?";
            $stmt_delete = $conn->prepare($query_delete);
            $stmt_delete->bind_param("i", $id);
            if ($stmt_delete->execute()) {
                header("Location: admin_dashboard.php#delivery_methods");
                exit();
            } else {
                echo "Błąd podczas usuwania metody dostawy.";
            }
        }
    } else {
        echo "Nie podano ID metody dostawy.";
    }
}
?>
