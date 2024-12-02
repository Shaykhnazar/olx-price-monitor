<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private $smtpHost;
    private $smtpPort;
    private $smtpUser;
    private $smtpPass;
    private $fromEmail;
    private $fromName;

    public function __construct($mailConfig)
    {
        $this->smtpHost = $mailConfig['smtp_host'];
        $this->smtpPort = $mailConfig['smtp_port'];
        $this->smtpUser = $mailConfig['smtp_user'];
        $this->smtpPass = $mailConfig['smtp_pass'];
        $this->fromEmail = $mailConfig['from_email'];
        $this->fromName = $mailConfig['from_name'];
    }

    public function send($toEmail, $subject, $htmlMessage, $plainTextMessage = '')
    {
        // Load Composer's autoloader
        require_once __DIR__ . '/../vendor/autoload.php';

        // Create an instance of PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();                                      // Use SMTP
            $mail->Host       = $this->smtpHost;                  // Set the SMTP server
            $mail->SMTPAuth   = true;                             // Enable SMTP authentication
            $mail->Username   = $this->smtpUser;                  // SMTP username
            $mail->Password   = $this->smtpPass;                  // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // Enable TLS encryption
            $mail->Port       = $this->smtpPort;                  // TCP port to connect to

            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($toEmail);                          // Add a recipient

            // Content
            $mail->isHTML();                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $htmlMessage;                        // Set HTML body

            if (!empty($plainTextMessage)) {
                $mail->AltBody = $plainTextMessage;               // Set alternative plain text body
            }

            // Send the email
            $mail->send();

            return true;
        } catch (Exception $e) {
            // Handle exceptions and errors
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
