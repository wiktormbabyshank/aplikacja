<?php
session_start();
include 'db_connection.php'; 

if (!isset($_SESSION['admin_email'])) {
    header("Location: index.html");
    exit();
}


if (isset($_GET['id'])) {
    $orderId = intval($_GET['id']); 
    $result = $conn->query("SELECT * FROM zamowienia WHERE id = $orderId");

 
    if ($result->num_rows === 0) {
        die("Zamówienie nie zostało znalezione.");
    }
    $order = $result->fetch_assoc();
} else {
    die("Nieprawidłowe żądanie.");
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj Zamówienie</title>
    <link rel="stylesheet" href="styles.css">
    <style>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .container1 {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: bold;
        }

        input[type="number"],
        select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
            box-sizing: border-box;
        }

        button[type="submit"] {
            padding: 10px;
            background-color: #28a745; 
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #218838; 
        }

       
        .back-button {
            position: fixed; 
            bottom: 20px; 
            right: 20px; 
            background-color: #007BFF; 
            color: white; 
            border: none; 
            padding: 10px 20px;
            border-radius: 5px; 
            cursor: pointer;
            text-decoration: none; 
            font-size: 16px;
            transition: background-color 0.3s; 
        }

        .back-button:hover {
            background-color: #0056b3; 
        }
    </style>
</head>
<body>
    <div class="container1">
        <h1>Edytuj Zamówienie</h1>
        <form action="edit_order_process.php" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($order['id']); ?>">

            <label for="user">Użytkownik:</label>
            <input type="text" id="user" value="<?php echo htmlspecialchars($order['user_id']); ?>" disabled>

            <label for="amount">Kwota:</label>
            <input type="number" id="amount" name="amount" value="<?php echo htmlspecialchars($order['amount']); ?>" required>

            <label for="status">Status:</label>
            <select id="status" name="status">
                <option value="Pending" <?php echo ($order['status'] == 'Pending') ? 'selected' : ''; ?>>W trakcie realizacji</option>
                <option value="Completed" <?php echo ($order['status'] == 'Completed') ? 'selected' : ''; ?>>Zrealizowane</option>
            </select>

            <button type="submit">Aktualizuj Zamówienie</button>
        </form>
    </div>

    <!-- Back button -->
    <a href="admin_dashboard.php" class="back-button">Powrót do panelu</a>
</body>
</html>
