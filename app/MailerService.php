<?php

final class MailerService
{
    public static function sendContactMail(string $fromEmail, string $fromName, string $message): void
    {
        $cfg = require __DIR__ . '/config/mail.local.php';

        // include PHPMailer (varianta veche, fără composer)
        require_once __DIR__ . '/../mail/class.phpmailer.php';
        require_once __DIR__ . '/../mail/class.smtp.php';

        $mail = new PHPMailer(true);

        // SMTP
        $mail->isSMTP();
        $mail->Host = $cfg['smtp']['host'];
        $mail->Port = (int)$cfg['smtp']['port'];
        $mail->SMTPAuth = true;
        $mail->Username = $cfg['smtp']['username'];
        $mail->Password = $cfg['smtp']['password'];

        $secure = $cfg['smtp']['secure'] ?? '';
        if ($secure === 'tls') $mail->SMTPSecure = 'tls';
        if ($secure === 'ssl') $mail->SMTPSecure = 'ssl';

        // From / To
        $mail->setFrom($cfg['from']['email'], $cfg['from']['name']);
        $mail->addAddress($cfg['to']['email'], $cfg['to']['name']);

        // Reply-to = user
        if ($fromEmail !== '') {
            $mail->addReplyTo($fromEmail, $fromName ?: $fromEmail);
        }

        $mail->Subject = 'Contact - Hypermarket Activities';
        $mail->isHTML(true);

        $safeMsg = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
        $safeFrom = htmlspecialchars($fromEmail, ENT_QUOTES, 'UTF-8');
        $safeName = htmlspecialchars($fromName, ENT_QUOTES, 'UTF-8');

        $mail->Body = "<h3>Mesaj nou din formularul de contact</h3>
                       <p><b>Nume:</b> {$safeName}</p>
                       <p><b>Email:</b> {$safeFrom}</p>
                       <p><b>Mesaj:</b><br>{$safeMsg}</p>";

        $mail->AltBody = "Nume: {$fromName}\nEmail: {$fromEmail}\n\nMesaj:\n{$message}";

        $mail->send();
    }
}
