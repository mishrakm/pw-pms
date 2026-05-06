<?php

// Contact Form Mail Handler

// PlusWealth PMS Contact Form Processing


use PHPMailer\PHPMailer\PHPMailer;

use PHPMailer\PHPMailer\SMTP;

use PHPMailer\PHPMailer\Exception;



require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../includes/db_config.php';


header('Content-Type: application/json');


// Check if form was submitted

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}


// Sanitize and validate input

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}


function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}


// Get form data

$name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
$email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
$phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '';
// city and ticket_size removed from form



// Validation errors array

$errors = [];


// Validate required fields

if (empty($name) || strlen($name) < 2) {
    $errors[] = 'Name must be at least 2 characters long';
}


if (empty($email) || !validate_email($email)) {
    $errors[] = 'Please provide a valid email address';
}


if (empty($phone) || strlen($phone) < 7) {
    $errors[] = 'Please provide a valid phone number';
}


// Return validation errors

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $errors
    ]);
    exit;
}


// Save to database

try {
    $conn = get_db_connection();
    
    // Get client information
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO contact_submissions (name, email, phone, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $phone, $ip_address, $user_agent);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to save submission to database");
    }
    
    $submission_id = $conn->insert_id;
    $stmt->close();
    
    error_log("Contact form submission saved with ID: " . $submission_id);
    
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    // Continue with email even if database save fails
}


// Email configuration

$to_emails = [
    'amit.mishra@pluswealth.net',
    'pavit.singh@pluswealth.com'
];


// Build email message body

$email_body = "\nYou have received a new contact form submission from your website.\n\n\nDetails:\n---------\nName: " . $name . "\nEmail: " . $email . "\nPhone: " . $phone . "\n\nPlease respond to this inquiry at your earliest convenience.\n\n---\nThis is an automated message from PlusWealth PMS Website Contact Form.\n";


try {

    // Send email to admin using PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = "smtp.office365.com";
    $mail->Port = 587;
    $mail->SMTPSecure = 'tls';
    $mail->SMTPAuth = true;
    $mail->Username = "no-reply@pluswealth.com";
    $mail->Password = "Y#816125957005os";
    $mail->setFrom("no-reply@pluswealth.com", "PlusWealth PMS Contact Form");
    $mail->addReplyTo($email, $name);
    
    // Add multiple recipients
    foreach ($to_emails as $recipient) {
        $mail->addAddress($recipient);
    }
    
    $mail->Subject = 'New Contact Form Submission from ' . $name;
    $mail->Body = $email_body;
    
    $mail->send();
    
    // Optional: Send confirmation email to user
    $user_mail = new PHPMailer(true);
    $user_mail->isSMTP();
    $user_mail->SMTPDebug = 0;
    $user_mail->Host = "smtp.office365.com";
    $user_mail->Port = 587;
    $user_mail->SMTPSecure = 'tls';
    $user_mail->SMTPAuth = true;
    $user_mail->Username = "noreply@egitpro.com";
    $user_mail->Password = "Jad04108";
    $user_mail->setFrom("noreply@egitpro.com", "PlusWealth Capital Management");
    $user_mail->addAddress($email, $name);
    
    $user_body = "\nDear " . $name . ",\n\nThank you for reaching out to PlusWealth Capital Management LLP.\n\nWe have received your inquiry and will get back to you within 24 business hours.\n\nDetails of your submission:\n---------------------------\nName: " . $name . "\nEmail: " . $email . "\nPhone: " . $phone . "\n\nIf you have any questions in the meantime, feel free to visit our website or call us directly.\n\nBest regards,\nPlusWealth Capital Management LLP\nSEBI Registration No: INZ000163752\nPortfolio Manager Registration No: INP000009144\n\n---\nThis is an automated message. Please do not reply to this email.\n";
    
    $user_mail->Subject = 'Thank you for contacting PlusWealth PMS';
    $user_mail->Body = $user_body;
    $user_mail->send();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your message has been sent successfully. We will get back to you soon.'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while sending your message. Please try again later or contact us directly.',
        'error' => isset($mail) ? $mail->ErrorInfo : $e->getMessage()
    ]);
}

exit;

?>
