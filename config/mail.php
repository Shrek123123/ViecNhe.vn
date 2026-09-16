<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

function sendEmail($to, $name, $subject, $body)
{
    $mail = new PHPMailer(true);
    $mail->SMTPDebug = 0;

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'b9b315001@smtp-brevo.com';
        $mail->Password   = 'xsmtpsib-021c23b3507e36adb8453d72fbd71ccf3793e6a876987dc1989008814d0477a7-JiM62EJd9DZMamel';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->CharSet = 'UTF-8';

        $mail->setFrom('qudominh16@gmail.com', 'Việc Nhẹ.vn');
        $mail->addAddress($to, $name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();

        return true;

    } catch (Exception $e) {
        return false;
    }
}