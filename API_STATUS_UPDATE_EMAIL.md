# Email Notification for Status Updates

## Overview
The system now automatically sends email notifications to students when an admin updates the status of their form/request.

## How It Works

### When Admin Updates Status
1. Admin makes a PUT request to `/api/request/request.php?id={request_id}` with the new status and optional admin notes
2. The system updates the request status in the database
3. Automatically fetches the student's email and request details
4. Sends a formatted email notification to the student
5. Logs the email delivery status

### Email Content
The email includes:
- **Subject**: "Update on your '[Document Name]' submission"
- **Status**: The new status (Pending, Processing, Completed, etc.)
- **Admin Comment**: Any notes the admin added (if provided)
- **Branding**: JHCSC DSA Student Council logo and styling
- **Call-to-Action**: Link to check the website

## API Endpoint

### Update Request Status (Admin Only)
**Endpoint**: `PUT /api/request/request.php?id={request_id}`

**Headers**:
```
Content-Type: application/json
Authorization: Bearer {admin_jwt_token}
```

**Request Body**:
```json
{
  "status": "Processing",
  "admin_notes": "Your documents are being reviewed. Please ensure all requirements are complete."
}
```

**Response** (Success):
```json
{
  "success": true,
  "message": "Request status updated successfully."
}
```

**Response** (Error):
```json
{
  "success": false,
  "message": "Request not found or no changes made."
}
```

## Email Configuration

The email system uses NotificationAPI service configured in `/utils/email.php`:
- **Client ID**: omojyjxwdhwdpuehdet46j13oc
- **Template**: jhcsc_dsa_from_update

## Files Modified

### 1. `/api/request/request.php` (Lines 167-203)
- Enhanced the PUT request handler
- Added automatic email notification after successful status update
- Improved error logging and handling
- Email sending doesn't block the response even if it fails

### 2. `/utils/email.php`
- Contains `sendFormStatusUpdateEmail()` function
- Fetches user details from database
- Formats and sends email using NotificationAPI
- Returns boolean indicating success/failure

### 3. `/utils/email_template.php`
- HTML email template with JHCSC branding
- Responsive design
- Professional styling

## Testing

Run the test scripts to verify functionality:

```bash
# Test email function directly
php tmp_rovodev_test_status_update.php

# Test full API flow with email
php tmp_rovodev_test_api_update.php
```

## Status Options

Common status values:
- `Pending` - Initial submission
- `Processing` - Under review
- `Completed` - Request fulfilled
- `Rejected` - Request denied
- `Cancelled` - Cancelled by student

## Error Handling

- If email fails to send, the status update still succeeds
- All email errors are logged to PHP error log
- User receives success message even if email fails (to prevent confusion)

## Logging

The system logs:
- When status update is attempted
- User details found for notification
- Email send success/failure
- Any errors during the process

Check PHP error logs for detailed information:
```bash
tail -f /path/to/php/error.log
```

## Example Usage (Admin Dashboard)

```javascript
// JavaScript example for admin dashboard
async function updateRequestStatus(requestId, newStatus, adminNotes) {
  try {
    const response = await fetch(`/api/request/request.php?id=${requestId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${adminToken}`
      },
      body: JSON.stringify({
        status: newStatus,
        admin_notes: adminNotes
      })
    });
    
    const data = await response.json();
    if (data.success) {
      alert('Status updated and student notified via email!');
    }
  } catch (error) {
    console.error('Error updating status:', error);
  }
}
```

## Benefits

✅ **Automatic Notifications** - No manual email sending required
✅ **Professional Communication** - Branded, formatted emails
✅ **Transparency** - Students always know their request status
✅ **Audit Trail** - All notifications logged
✅ **Non-Blocking** - Email failures don't prevent status updates
✅ **User-Friendly** - Students get clear, actionable information

## Troubleshooting

### Email not received?
1. Check spam/junk folder
2. Verify email address in user profile is correct
3. Check PHP error logs for NotificationAPI errors
4. Verify NotificationAPI credentials are valid

### Status updates but no email?
1. Check error logs for email sending failures
2. Verify `/utils/email.php` file exists and is readable
3. Check database connection
4. Verify user exists and has valid email

## Future Enhancements

Potential improvements:
- SMS notifications
- In-app notifications
- Email notification preferences
- Batch status updates with bulk emails
- Email templates for different status types
