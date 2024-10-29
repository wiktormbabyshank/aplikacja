<?php
session_start(); 

$servername = "pma.ct8.pl"; 
$username = "m50583_kacper";       
$password = "79UiZ2Vb4F4Wxiz";  
$dbname = "m50583_uzytkownicy_sklepu";  


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

 
    $sql = "SELECT * FROM admini WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin['haslo'])) {

            $_SESSION['admin_email'] = $admin['email'];
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "Błędny email lub hasło";
        }
    } else {
        echo "Błędny email lub hasło";
    }

    $stmt->close();
}

$conn->close();
?>
