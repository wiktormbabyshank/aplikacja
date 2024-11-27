<?php

require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include('db_connection.php'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    
    $query = "SELECT id, email FROM uzytkownicy WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        
        $user = $result->fetch_assoc();

        
        $temporary_password = bin2hex(random_bytes(4)); 
        $hashed_password = password_hash($temporary_password, PASSWORD_DEFAULT);

        
        $update_query = "UPDATE uzytkownicy SET haslo = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param('si', $hashed_password, $user['id']);
        $update_stmt->execute();

        
        $mail = new PHPMailer(true);

        try {
            
            $mail->isSMTP();
            $mail->Host = 's1.ct8.pl'; 
            $mail->SMTPAuth = true;
            $mail->Username = 'wiktorm@wiktorm.ct8.pl'; 
            $mail->Password = ')7FE69$LrbWXq^&z)FuI'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            
            $mail->setFrom('wiktorm@wiktorm.ct8.pl', 'Sklep');
            $mail->addAddress($email); 

            
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);
            $mail->Subject = 'Przypomnienie hasła';
            $mail->Body = "Twoje tymczasowe hasło to: <strong>$temporary_password</strong><br>Zaloguj się i zmień hasło na nowe.";

            
            $mail->send();
            echo "<!DOCTYPE html>
<html lang='pl'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>

    <link rel='stylesheet' href='styles.css'>
    
</head><body class='body-main'>
    <div class='container'>
        <h2>Na Twój adres e-mail wysłano przypomnienie hasła.</h2>
    </div>";
            echo "<a href='index.html' class='admin-panel'>Powrót do logowania</a></body></html>";
        } catch (Exception $e) {
            echo "Wystąpił błąd podczas wysyłania wiadomości e-mail. Szczegóły: {$mail->ErrorInfo}";
        }
    } else {
        echo "Podany adres e-mail nie istnieje w naszej bazie danych.";
    }
}
?>
