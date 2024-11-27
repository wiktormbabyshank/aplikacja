<?php
include('db_connection.php');

if (isset($_GET['slug'])) {
    $slug = $conn->real_escape_string($_GET['slug']);
    $result = $conn->query("SELECT * FROM pages WHERE slug = '$slug'");
    $page = $result->fetch_assoc();
}

if (!$page) {
    echo "Strona nie istnieje.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page['title']); ?></title>
</head>
<body>
    <h1><?php echo htmlspecialchars($page['title']); ?></h1>
    <div><?php echo nl2br(htmlspecialchars($page['content'])); ?></div>
</body>
</html>
