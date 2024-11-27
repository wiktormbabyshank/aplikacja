<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['id'])) {
        $id = intval($_POST['id']);

        $query_check_orders = "SELECT COUNT(*) AS count FROM zamowienia WHERE payment_method_id = ?";
        $stmt_check_orders = $conn->prepare($query_check_orders);

        if (!$stmt_check_orders) {
            die("Błąd w przygotowywaniu zapytania SQL: " . $conn->error);
        }

        $stmt_check_orders->bind_param("i", $id);
        $stmt_check_orders->execute();
        $result_check_orders = $stmt_check_orders->get_result();
        $order_count = $result_check_orders->fetch_assoc()['count'];

        if ($order_count > 0) {
            echo "Nie można usunąć tej metody płatności, ponieważ jest używana w zamówieniach.";
        } else {
            $query_delete_payment = "DELETE FROM payment_methods WHERE id = ?";
            $stmt_delete_payment = $conn->prepare($query_delete_payment);

            if (!$stmt_delete_payment) {
                die("Błąd w przygotowywaniu zapytania SQL: " . $conn->error);
            }

            $stmt_delete_payment->bind_param("i", $id);

            if ($stmt_delete_payment->execute()) {
                header("Location: admin_dashboard.php#payments");
                exit();
            } else {
                echo "Błąd podczas usuwania metody płatności: " . $stmt_delete_payment->error;
            }

            $stmt_delete_payment->close();
        }

        $stmt_check_orders->close();
    } else {
        echo "Nie podano ID metody płatności do usunięcia.";
    }
} else {
    echo "Nieprawidłowe żądanie.";
}

$conn->close();
?>
