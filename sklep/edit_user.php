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
    
</head>
<body class="body_editu"> 
    <div class="container3">
        <h1 class="h1_edit">Edytuj Użytkownika</h1>
        <form action="edit_user_process.php" method="POST" class="form_edit">
            <input type="hidden" class="input-editu" name="id" value="<?php echo $user['id']; ?>">

            <label for="imie" class="label_editu">Imię:</label>
            <input type="text" class="input-editu" id="imie" name="imie" value="<?php echo $user['imie']; ?>" required>

            <label for="nazwisko" class="label_editu">Nazwisko:</label>
            <input type="text" class="input-editu" id="nazwisko" name="nazwisko" value="<?php echo $user['nazwisko']; ?>" required>

            <label for="email" class="label_editu">Email:</label>
            <input type="email" class="input-editu" id="email" name="email" value="<?php echo $user['email']; ?>" required>

            <label for="status" class="label_editu">Status:</label>
            <select id="status" class="status" name="status">
                <option value="Active" <?php echo ($user['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                <option value="Blocked" <?php echo ($user['status'] == 'Blocked') ? 'selected' : ''; ?>>Blocked</option>
            </select>

            <button type="submit" class="button_editu">Aktualizuj</button>
        </form>
    </div>


    <a href="admin_dashboard.php" class="back-button">Powrót do panelu</a>
</body>
</html>
