<?php
session_start();

include('db_connection.php');


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];


    $sql = "SELECT * FROM uzytkownicy WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc(); 


        if (password_verify($password, $user['haslo'])) { 

            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $user['id'];
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
