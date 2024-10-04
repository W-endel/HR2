<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

// Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = SMTP::DEBUG_OFF;                       // Set to DEBUG_SERVER for development
    $mail->isSMTP();                                          // Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                // Enable SMTP authentication
    $mail->Username   = 'microfinancehr2@gmail.com';         // SMTP username
    $mail->Password   = 'yjla pidq jfdr qbnz';                 // SMTP password (use App Password if 2FA is enabled)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;     // Use STARTTLS for port 587
    $mail->Port       = 587;                                 // TCP port to connect to

    // Recipients
    $mail->setFrom('microfinancehr2@gmail.com', 'Microfinance');
    $mail->addAddress('uretawendel@gmail.com', 'Wendel Ureta'); // Add a recipient
    $mail->addReplyTo('microfinancehr2@gmail.com', 'Microfinance');

    // Content
    $temporaryPassword = 'TempPassword123'; // Generate or define a temporary password
    $mail->isHTML(true);                                 // Set email format to HTML
    $mail->Subject = 'Your Temporary Password';
    $mail->Body    = 'Hello,<br><br>Your temporary password is: <b>' . $temporaryPassword . '</b><br>Please change it after logging in.<br><br>Best regards,<br>Microfinance';
    $mail->AltBody = 'Hello, Your temporary password is: ' . $temporaryPassword . ' Please change it after logging in. Best regards, Microfinance';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
