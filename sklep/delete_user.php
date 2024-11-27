<?php
session_start();



include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']); 


    $stmt = $conn->prepare("DELETE FROM uzytkownicy WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {

        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Błąd: " . $stmt->error; 
    }

    $stmt->close();
} else {
    echo "Nieprawidłowe żądanie."; 
}

$conn->close();
?>



