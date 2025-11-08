<?php

require_once __DIR__ . '/../vendor/autoload.php';

function sendEmail($recipientEmail, $recipientName, $subject, $htmlContent) {
    $config = Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', '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');
    $apiInstance = new Brevo\Client\Api\TransactionalEmailsApi(
        new GuzzleHttp\Client(),
        $config
    );

    $sendSmtpEmail = new \Brevo\Client\Model\SendSmtpEmail([
        'to' => [[ 'name' => $recipientName, 'email' => $recipientEmail]],
        'subject' => $subject,
        'htmlContent' => $htmlContent,
        'sender' => ['name' => 'Student Affairs', 'email' => 'no-reply@studentaffairs.com'],
        'replyTo' => ['name' => 'Student Affairs', 'email' => 'no-reply@studentaffairs.com'],
    ]);

    try {
        $apiInstance->sendTransacEmail($sendSmtpEmail);
        error_log("Email sent successfully to: " . $recipientEmail);
        return true;
    } catch (Exception $e) {
        error_log('Brevo API Error: '. $e->getMessage());
        return false;
    }
}
