<?php
/**
 * Email utility using PHPMailer with SMTP
 * Replaces the NotificationAPI implementation
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../api/config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Sends an email using PHPMailer with SMTP
 * 
 * @param string $recipientEmail The recipient's email address
 * @param string $recipientName The recipient's name
 * @param string $subject The email subject
 * @param string $htmlContent The HTML content of the email
 * @return bool True if email was sent successfully, false otherwise
 */
function sendEmail($recipientEmail, $recipientName, $subject, $htmlContent) {
    // Load email configuration
    $config = require __DIR__ . '/../api/config/email_config.php';
    
    $mail = new PHPMailer(true);

    try {
        // Server settings
        if ($config['debug_mode']) {
            $mail->SMTPDebug = 2; // Enable verbose debug output
        }
        
        $mail->isSMTP();
        $mail->Host       = $config['smtp_host'];
        $mail->SMTPAuth   = $config['smtp_auth'];
        $mail->Username   = $config['smtp_username'];
        $mail->Password   = $config['smtp_password'];
        $mail->SMTPSecure = $config['smtp_secure'];
        $mail->Port       = $config['smtp_port'];
        $mail->CharSet    = $config['charset'];

        // Recipients
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($recipientEmail, $recipientName);
        
        // Reply-To (optional)
        if (!empty($config['reply_to_email'])) {
            $mail->addReplyTo($config['reply_to_email'], $config['reply_to_name']);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlContent;
        
        // Plain text alternative (strip HTML tags)
        $mail->AltBody = strip_tags($htmlContent);

        // Send the email
        $mail->send();
        
        error_log("Email sent successfully to: " . $recipientEmail);
        return true;
        
    } catch (Exception $e) {
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        error_log("Exception Message: " . $e->getMessage());
        return false;
    }
}

/**
 * Renders the main HTML email template.
 *
 * @param string $subject The email subject line.
 * @param string $bodyContent The specific HTML content to be placed inside the template.
 * @return string The full HTML of the email.
 */
function renderEmailTemplate($subject, $bodyContent) {
    try {
        $logoPath = __DIR__ . '/../Image/logoJH.png';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        } else {
            $logoSrc = 'https://jhcsc.edu.ph/wp-content/uploads/2024/12/jhcsclogo-transparent-1000x1000.png';
        }

        ob_start();
        // The $subject, $bodyContent, and $logoSrc variables will be available in the included template file.
        include __DIR__ . '/email_template.php';
        return ob_get_clean();
    } catch (Exception $e) {
        error_log('Error in renderEmailTemplate: ' . $e->getMessage());
        // Return a simple fallback template
        if (ob_get_level() > 0) {
            ob_end_clean(); // Clean any output buffer
        }
        return "<html><body><h2>$subject</h2>$bodyContent</body></html>";
    }
}

/**
 * Fetches all admins and sends them a notification.
 *
 * @param string $subject The email subject.
 * @param string $bodyContent The raw HTML content of the message body.
 */
function sendNotificationToAdmins($subject, $bodyContent) {
    $database = new Database();
    $db = $database->getConnection();

    try {
        $stmt = $db->prepare("SELECT email, full_name FROM users WHERE role = 'admin'");
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($admins)) {
            error_log("No admin users found to send notification.");
            return false;
        }

        // Render the full email body using the main template
        $htmlContent = renderEmailTemplate($subject, $bodyContent);

        $all_sent = true;
        foreach ($admins as $admin) {
            if (!sendEmail($admin['email'], $admin['full_name'], $subject, $htmlContent)) {
                error_log("Failed to send notification to admin: " . $admin['email']);
                $all_sent = false;
            }
        }
        return $all_sent;

    } catch (Exception $e) {
        error_log('Database Error in sendNotificationToAdmins: '. $e->getMessage());
        return false;
    }
}

/**
 * Sends a form status update email to a specific user.
 *
 * @param int $userId The ID of the user.
 * @param string $formName The name of the form.
 * @param string $newStatus The new status of the form.
 * @param string $comment The admin's comment.
 */
function sendFormStatusUpdateEmail($userId, $formName, $newStatus, $comment) {
    $database = new Database();
    $db = $database->getConnection();

    try {
        $stmt = $db->prepare("SELECT email, full_name FROM users WHERE id = :userId");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            error_log("User with ID $userId not found for status update email.");
            return false;
        }

        // Construct the specific message for this email
        $subject = sprintf("Update on your '%s' submission", $formName);
        $bodyContent = sprintf(
            "<p>Hello %s,</p><p>The status of your form submission for '%s' has been updated to: <strong>%s</strong>.</p>",
            htmlspecialchars($user['full_name']),
            htmlspecialchars($formName),
            htmlspecialchars($newStatus)
        );

        if (!empty($comment)) {
            $bodyContent .= sprintf("<p><strong>Admin comment:</strong><br>%s</p>", nl2br(htmlspecialchars($comment)));
        }

        // Render the full email using the template
        $htmlContent = renderEmailTemplate($subject, $bodyContent);

        // Send the email
        return sendEmail($user['email'], $user['full_name'], $subject, $htmlContent);

    } catch (Exception $e) {
        error_log('Database or Email Error in sendFormStatusUpdateEmail: '. $e->getMessage());
        return false;
    }
}

/**
 * Sends a user account status update email to a specific user.
 *
 * @param int $userId The ID of the user.
 * @param string $newStatus The new account status (active/inactive).
 * @return bool True if email was sent successfully, false otherwise.
 */
function sendUserStatusUpdateEmail($userId, $newStatus) {
    $database = new Database();
    $db = $database->getConnection();

    try {
        $stmt = $db->prepare("SELECT email, full_name FROM users WHERE id = :userId");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            error_log("User with ID $userId not found for account status update email.");
            return false;
        }

        // Construct the specific message for this email
        $subject = "Account Status Update";
        
        if ($newStatus === 'active') {
            $bodyContent = sprintf(
                "<p>Hello %s,</p><p>Great news! Your account has been <strong>activated</strong> by the administrator.</p><p>You can now access all features of the JHCSC DSA Student Council portal.</p>",
                htmlspecialchars($user['full_name'])
            );
        } else {
            $bodyContent = sprintf(
                "<p>Hello %s,</p><p>Your account status has been updated to: <strong>%s</strong>.</p><p>If you have any questions or concerns about this change, please contact the DSA office.</p>",
                htmlspecialchars($user['full_name']),
                htmlspecialchars($newStatus)
            );
        }

        // Render the full email using the template
        $htmlContent = renderEmailTemplate($subject, $bodyContent);

        // Send the email
        return sendEmail($user['email'], $user['full_name'], $subject, $htmlContent);

    } catch (Exception $e) {
        error_log('Database or Email Error in sendUserStatusUpdateEmail: '. $e->getMessage());
        return false;
    }
}
