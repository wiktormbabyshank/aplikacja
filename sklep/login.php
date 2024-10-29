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


    $sql = "SELECT * FROM uzytkownicy WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc(); 


        if (password_verify($password, $user['haslo'])) { 

            $_SESSION['loggedin'] = true;
            $_SESSION['imie'] = $user['imie'];
            $_SESSION['email'] = $user['email'];
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Błędny email lub hasło";
        }
    } else {
        echo "Błędny email lub hasło";
    }
}

$conn->close();
?>
