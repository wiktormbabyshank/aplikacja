<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'] ?? '';

    $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?message=Kategoria została dodana");
    } else {
        echo "Błąd: " . $stmt->error;
    }
    $stmt->close();
}
?>
