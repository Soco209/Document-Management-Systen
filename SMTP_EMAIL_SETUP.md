# SMTP Email Setup Guide

## 🎉 What Changed?

We've switched from NotificationAPI to **PHPMailer with SMTP** for sending emails. This gives you:
- ✅ No monthly limits (depends on your email provider)
- ✅ Better deliverability
- ✅ Full control over email sending
- ✅ Works with Gmail, Outlook, SendGrid, Mailgun, etc.

---

## 📋 Quick Setup (Gmail Example)

### Step 1: Get Gmail App Password

1. Go to **Google Account Security**: https://myaccount.google.com/security
2. **Enable 2-Step Verification** (if not already enabled)
3. Go to **App Passwords**: https://myaccount.google.com/apppasswords
4. Select **Mail** as the app
5. Select **Windows Computer** (or your device)
6. Click **Generate**
7. Copy the **16-character password** (example: `abcd efgh ijkl mnop`)

### Step 2: Configure Email Settings

Edit the file: `api/config/email_config.php`

```php
return [
    // SMTP Configuration
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_secure' => 'tls',
    'smtp_auth' => true,
    
    // YOUR EMAIL CREDENTIALS
    'smtp_username' => 'your-actual-email@gmail.com',  // ← Change this
    'smtp_password' => 'your-16-char-app-password',    // ← Change this (no spaces)
    
    // Sender Information
    'from_email' => 'your-actual-email@gmail.com',     // ← Change this
    'from_name' => 'JHCSC DSA Student Council',
    
    // Reply-To
    'reply_to_email' => 'your-actual-email@gmail.com', // ← Change this
    'reply_to_name' => 'JHCSC DSA',
    
    'charset' => 'UTF-8',
    'debug_mode' => false,  // Set to true if you need to debug SMTP issues
];
```

### Step 3: Test the Email System

Open in your browser:
```
http://localhost/student_affairs/tmp_rovodev_smtp_test.php
```

This will:
- ✅ Check your configuration
- ✅ Send a test email
- ✅ Send a status update test email

### Step 4: Test Through Admin Panel

1. Log in as admin
2. Navigate to "Request Management"
3. Update any request status
4. Student should receive an email!

---

## 🔧 Configuration for Other Email Services

### Using Outlook/Hotmail

```php
'smtp_host' => 'smtp-mail.outlook.com',
'smtp_port' => 587,
'smtp_secure' => 'tls',
'smtp_username' => 'your-email@outlook.com',
'smtp_password' => 'your-outlook-password',
```

### Using Yahoo Mail

```php
'smtp_host' => 'smtp.mail.yahoo.com',
'smtp_port' => 587,
'smtp_secure' => 'tls',
'smtp_username' => 'your-email@yahoo.com',
'smtp_password' => 'your-yahoo-app-password',
```

### Using SendGrid (Recommended for Production)

```php
'smtp_host' => 'smtp.sendgrid.net',
'smtp_port' => 587,
'smtp_secure' => 'tls',
'smtp_username' => 'apikey',  // Literally the word "apikey"
'smtp_password' => 'your-sendgrid-api-key',
```

### Using Mailgun

```php
'smtp_host' => 'smtp.mailgun.org',
'smtp_port' => 587,
'smtp_secure' => 'tls',
'smtp_username' => 'your-mailgun-smtp-username',
'smtp_password' => 'your-mailgun-smtp-password',
```

---

## 🔍 Troubleshooting

### Issue: "Authentication failed"

**Solution:**
- Double-check your email and password
- For Gmail: Make sure you're using an **App Password**, not your regular password
- Verify 2-Step Verification is enabled

### Issue: "Could not connect to SMTP host"

**Solution:**
- Check your internet connection
- Verify the SMTP host and port are correct
- Try changing port: 587 (TLS) or 465 (SSL)
- Check if your firewall is blocking outgoing SMTP connections

### Issue: "SMTP Error: Could not authenticate"

**Solution for Gmail:**
- Go to https://myaccount.google.com/lesssecureapps
- Enable "Less secure app access" (if using regular password)
- **Better:** Use App Password instead

### Issue: Emails go to spam

**Solution:**
- Ensure your "from" email matches your SMTP username
- Add a reply-to address
- Ensure HTML content is properly formatted
- Consider using a dedicated email service (SendGrid, Mailgun)

### Issue: "Connection timed out"

**Solution:**
- Your hosting/ISP might be blocking SMTP ports
- Try port 465 with SSL instead of 587 with TLS
- Contact your hosting provider
- Consider using an API-based service instead

---

## 📊 Email Sending Limits

### Gmail Free Account
- **Limit:** 500 emails per day
- **Good for:** Development and small deployments

### G Suite / Google Workspace
- **Limit:** 2,000 emails per day
- **Good for:** Small to medium production use

### SendGrid Free
- **Limit:** 100 emails per day (free tier)
- **Paid:** Up to millions per month
- **Good for:** Production environments

### Mailgun
- **Limit:** 5,000 emails per month (free trial)
- **Paid:** Pay-as-you-go or monthly plans
- **Good for:** Production environments

---

## 🎯 Email Features

### What Emails Are Sent?

1. **Request Submitted** (to student)
   - When a student submits a new request
   - Includes request ID and details

2. **Status Updated** (to student) ⭐ **Main Feature**
   - When admin updates request status
   - Includes new status and admin comments
   - Professional template with JHCSC branding

3. **Account Status Changed** (to user)
   - When admin activates/deactivates a user account

4. **Admin Notifications** (to all admins)
   - When important events occur (if implemented)

### Email Template Features

✅ JHCSC branding with logo
✅ Professional design
✅ Mobile-responsive
✅ Link to portal
✅ Contact information
✅ HTML with plain-text fallback

---

## 🔒 Security Best Practices

### DO:
✅ Use App Passwords (not regular passwords)
✅ Keep credentials in a separate config file
✅ Add `email_config.php` to `.gitignore`
✅ Use environment variables in production
✅ Enable 2-Factor Authentication on email account

### DON'T:
❌ Commit credentials to version control
❌ Use your personal email for production
❌ Share App Passwords
❌ Hardcode credentials in code

---

## 📁 Files Created/Modified

### New Files:
- `api/config/email_config.php` - Email configuration
- `utils/email_smtp.php` - PHPMailer email utility
- `tmp_rovodev_smtp_test.php` - Test script
- `SMTP_EMAIL_SETUP.md` - This guide

### Modified Files:
- `api/request/request.php` - Uses new email utility
- `api/users/index.php` - Uses new email utility
- `composer.json` - Added PHPMailer dependency

---

## 🚀 Production Deployment

When deploying to production:

1. **Use a dedicated email service** (SendGrid, Mailgun, AWS SES)
2. **Set up environment variables** for credentials
3. **Enable email logging** to track sent emails
4. **Monitor bounce rates** and deliverability
5. **Set up SPF, DKIM, and DMARC** records for your domain
6. **Use a professional sending domain** (e.g., noreply@yourdomain.com)

---

## ✅ Checklist

- [ ] Enabled 2-Step Verification on email account
- [ ] Generated App Password
- [ ] Updated `api/config/email_config.php` with credentials
- [ ] Ran test script: `tmp_rovodev_smtp_test.php`
- [ ] Test email received successfully
- [ ] Tested status update through admin panel
- [ ] Student received status update email
- [ ] Verified email has correct formatting and branding
- [ ] Cleaned up test scripts (optional)

---

## 🆘 Need Help?

If you're still having issues:

1. **Enable debug mode** in `email_config.php`:
   ```php
   'debug_mode' => true,
   ```

2. **Check PHP error logs** for detailed error messages

3. **Test with a simple email** using the test script

4. **Try a different email provider** (Outlook, Yahoo, etc.)

5. **Contact your hosting provider** about SMTP restrictions

---

## 🎉 Success!

Once everything is working:
- Delete test scripts: `tmp_rovodev_*.php`
- Keep `SMTP_EMAIL_SETUP.md` for reference
- Monitor email delivery
- Consider upgrading to a professional email service for production

**Your email notification system is now fully functional!**
