<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Show full debug output
    $mail->Host       = 'smtp.gmail.com';
    $mail->Port       = 587;
    $mail->SMTPSecure = 'tls';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'no-reply@pluswealth.com';
    $mail->Password   = 'qrgdaodurjmhajry';

    $mail->setFrom('no-reply@pluswealth.com', 'PlusWealth PMS');
    $mail->addAddress('amit.mishra@pluswealth.com');

    $mail->Subject = 'Test Email - PlusWealth Gmail SMTP';
    $mail->Body    = "This is a test email to confirm Gmail SMTP is configured correctly.\n\nSent from: no-reply@pluswealth.com\nServer: smtp.gmail.com:587";

    $mail->send();
    echo "✅ Test email sent successfully to amit.mishra@pluswealth.com\n";
} catch (Exception $e) {
    echo "❌ Email failed: " . $mail->ErrorInfo . "\n";
}
