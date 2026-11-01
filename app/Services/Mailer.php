<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class Mailer
{
    public static function send(string $to, string $subject, string $body): bool
    {
        $configFile = __DIR__ . '/../config/mail.php';
        if (!is_file($configFile)) {
            error_log('Mail error: app/config/mail.php is missing. Copy mail.example.php and fill it in.');
            return false;
        }
        $cfg = require $configFile;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $cfg['username'];
            $mail->Password   = $cfg['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom($cfg['from'], $cfg['from_name']);
            $mail->addAddress($to);

            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mail error: ' . $mail->ErrorInfo);
            return false;
        }
    }
}