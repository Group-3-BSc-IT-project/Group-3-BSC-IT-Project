<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class Mailer
{
    public static function send(string $to, string $subject, string $body): bool
    {
        $configFile = __DIR__ . '/../config/mail.php';
        $config = is_file($configFile) ? require $configFile : [];

        $username = getenv('SMTP_USERNAME') ?: ($config['username'] ?? '');
        $password = getenv('SMTP_PASSWORD') ?: ($config['password'] ?? '');
        $from = getenv('SMTP_FROM') ?: ($config['from'] ?? $username);
        $fromName = getenv('SMTP_FROM_NAME') ?: ($config['from_name'] ?? 'MSBMS');

        if ($username === '' || $password === '' || $from === '') {
            error_log('Mail error: configure SMTP_USERNAME, SMTP_PASSWORD, and SMTP_FROM.');
            return false;
        }

        $host = getenv('SMTP_HOST') ?: ($config['host'] ?? 'smtp.gmail.com');
        $port = (int)(getenv('SMTP_PORT') ?: ($config['port'] ?? 587));

        $mail = new PHPMailer(true);

        try {
            $mail->SMTPDebug = 2; // Enables verbose debug output
            $mail->Debugoutput = function($str, $level) {echo "debug: $str\n";};
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $username;
            $mail->Password   = $password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $port;

            $mail->setFrom($from, $fromName);
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