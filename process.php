<?php
header('Content-Type: application/json');
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$response = ['success' => false, 'message' => ''];

try {
    // Collect form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $sponsorshipType = trim($_POST['sponsorship_type'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $dedication = trim($_POST['dedication'] ?? '');
    $preferredShiur = trim($_POST['preferred-shiur'] ?? '');
    $parshaName = trim($_POST['parsha-name'] ?? '');

    // Basic validation
    if (empty($name) || empty($email) || empty($phone) || empty($sponsorshipType) || empty($amount)) {
        throw new Exception('Missing required fields.');
    }

    // Gmail SMTP config
    $adminEmail = 'rabbikraz1@gmail.com';
    $smtpUsername = 'rabbikraz1@gmail.com';
    $smtpPassword = 'czaqissvzuluedbh';

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUsername;
    $mail->Password = $smtpPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    // Email 1: Thank-You to User
    $mail->setFrom($adminEmail, 'Rabbi Kraz\'s Shiurim');
    $mail->addAddress($email, $name);
    $mail->isHTML(true);
    $mail->Subject = 'Thank You for Sponsoring a Shiur!';

    $thankYouTemplate = file_get_contents('thank_you_template.html');
    $thankYouBody = str_replace(
        ['{{name}}', '{{amount}}', '{{sponsorship_type}}'],
        [$name, $amount, ucfirst($sponsorshipType)],
        $thankYouTemplate
    );
    $mail->Body = $thankYouBody;

    $mail->send();
    $mail->clearAddresses();

    // Email 2: Admin Notification
    $mail->addAddress($adminEmail, 'Admin');
    $mail->isHTML(true);
    $mail->Subject = 'New Sponsorship Form Submission';

    $adminTemplate = file_get_contents('admin_email_template.html');
    $adminBody = str_replace(
        [
            '{{name}}', '{{email}}', '{{phone}}', '{{sponsorship_type}}',
            '{{amount}}', '{{dedication}}', '{{preferred_shiur}}', '{{parsha_name}}'
        ],
        [
            $name, $email, $phone, ucfirst($sponsorshipType),
            $amount, nl2br($dedication), ucfirst($preferredShiur), $parshaName
        ],
        $adminTemplate
    );
    $mail->Body = $adminBody;

    $mail->send();

    $response['success'] = true;
    $response['message'] = 'Emails sent successfully!';

} catch (Exception $e) {
    $response['message'] = 'Failed to send emails: ' . $mail->ErrorInfo;
}

echo json_encode($response);
?>
