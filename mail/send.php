<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';
require __DIR__ . '/../PHPMailer/src/Exception.php';

function sendEmail($email, $body , $subject = "Welcome to Pawn" )
{
    if (!$email) {
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        // SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'stago.free@gmail.com';
        $mail->Password = 'aslilwyuompbpjqy';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // sender & receiver
        $mail->setFrom('stago.free@gmail.com', 'pawn');
        $mail->addAddress($email, 'User');

        // HTML
        $mail->isHTML(true);
        $mail->Subject = $subject;

        $mail->Body = $body;

        $mail->send();
        return true;

    } catch (Exception $e) {
        echo "Error: {$mail->ErrorInfo}";
        return false;
    }

}
