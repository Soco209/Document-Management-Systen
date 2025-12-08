<?php
/**
 * Email Configuration
 * 
 * Configure your SMTP settings here.
 * For Gmail: You need to use an "App Password" not your regular password.
 * How to generate Gmail App Password:
 * 1. Go to https://myaccount.google.com/security
 * 2. Enable 2-Step Verification if not already enabled
 * 3. Go to https://myaccount.google.com/apppasswords
 * 4. Create a new app password for "Mail"
 * 5. Use that 16-character password below
 */

return [
    // SMTP Configuration
    'smtp_host' => 'smtp.gmail.com',           // Gmail SMTP server (or smtp.sendgrid.net, etc.)
    'smtp_port' => 587,                         // 587 for TLS, 465 for SSL
    'smtp_secure' => 'tls',                     // 'tls' or 'ssl'
    'smtp_auth' => true,                        // Enable SMTP authentication
    
    // Email Account Credentials
    'smtp_username' => 'gaminghr209@gmail.com',  // Your email address
    'smtp_password' => 'dbcbyybapngqtlet',     // Your app password (NOT your regular password!)
    
    // Sender Information
    'from_email' => 'gaminghr@gmail.com',     // From email address
    'from_name' => 'JHCSC DSA Student Council', // From name
    
    // Reply-To (optional)
    'reply_to_email' => 'your-email@gmail.com', // Reply-to email
    'reply_to_name' => 'JHCSC DSA',             // Reply-to name
    
    // Email Settings
    'charset' => 'UTF-8',
    'debug_mode' => false,                      // Set to true for debugging (shows SMTP conversation)
];
