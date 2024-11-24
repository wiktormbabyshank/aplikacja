<?php
session_start();
include('db_connection.php'); 

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    $result = $conn->query("SELECT * FROM uzytkownicy WHERE id = $userId");
    $user = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj Użytkownika</title>
    <link rel="stylesheet" href="styles.css">
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

        input[type="text"],
        input[type="email"],
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
        <h1>Edytuj Użytkownika</h1>
        <form action="edit_user_process.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">

            <label for="imie">Imię:</label>
            <input type="text" id="imie" name="imie" value="<?php echo $user['imie']; ?>" required>

            <label for="nazwisko">Nazwisko:</label>
            <input type="text" id="nazwisko" name="nazwisko" value="<?php echo $user['nazwisko']; ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>

            <label for="status">Status:</label>
            <select id="status" name="status">
                <option value="Active" <?php echo ($user['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                <option value="Blocked" <?php echo ($user['status'] == 'Blocked') ? 'selected' : ''; ?>>Blocked</option>
            </select>

            <button type="submit">Aktualizuj</button>
        </form>
    </div>


    <a href="admin_dashboard.php" class="back-button">Powrót do panelu</a>
</body>
</html>
