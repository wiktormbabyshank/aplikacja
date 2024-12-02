<?php
session_start();
include('db_connection.php');

if (isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM uzytkownicy WHERE id = $userId");
    $user = $result->fetch_assoc();

    $notesResult = $conn->query("SELECT * FROM user_notes WHERE user_id = $userId ORDER BY created_at DESC");
    $userNotes = $notesResult->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj Użytkownika</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container-edit {
            margin-top: 50px;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-control {
            margin-bottom: 15px;
        }
        .btn-update {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
        }
        .btn-update:hover {
            background-color: #218838;
        }
        .back-button {
            display: block;
            text-align: center;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>

<body>
    <div class="container container-edit">
        <h1>Edytuj Użytkownika</h1>
        <form action="edit_user_process.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">

            <div class="mb-3">
                <label for="imie" class="form-label">Imię:</label>
                <input type="text" class="form-control" id="imie" name="imie" value="<?php echo htmlspecialchars($user['imie']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="nazwisko" class="form-label">Nazwisko:</label>
                <input type="text" class="form-control" id="nazwisko" name="nazwisko" value="<?php echo htmlspecialchars($user['nazwisko']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status:</label>
                <select id="status" class="form-select" name="status">
                    <option value="Active" <?php echo ($user['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                    <option value="Blocked" <?php echo ($user['status'] == 'Blocked') ? 'selected' : ''; ?>>Blocked</option>
                </select>
            </div>

            <button type="submit" class="btn-update">Aktualizuj</button>
        </form>

        <h2 class="mt-4">Notatki</h2>
        <ul class="list-group mb-3">
            <?php foreach ($userNotes as $note): ?>
                <li class="list-group-item">
                    <?php echo htmlspecialchars($note['note']); ?>
                    <span class="text-muted float-end"><?php echo $note['created_at']; ?></span>
                </li>
            <?php endforeach; ?>
        </ul>

        <form action="add_user_note.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
            <div class="mb-3">
                <label for="note" class="form-label">Dodaj nową notatkę:</label>
                <textarea class="form-control" id="note" name="note" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Dodaj Notatkę</button>
        </form>

        <a href="admin_dashboard_user.php" class="back-button">Powrót do panelu</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
