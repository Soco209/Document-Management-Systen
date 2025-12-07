# 🎓 JHCSC Student Affairs Management System

A comprehensive web-based system for managing student document requests and applications at **JH Cerilles State College (JHCSC)**. This system streamlines the process of requesting official documents, tracking submissions, and managing applications through the Department of Student Affairs (DSA).

---

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [Installation](#installation)
- [Configuration](#configuration)
- [API Documentation](#api-documentation)
- [User Roles](#user-roles)
- [Email Notifications](#email-notifications)
- [Security Features](#security-features)
- [Development](#development)
- [Troubleshooting](#troubleshooting)
- [Contributors](#contributors)
- [License](#license)

---

## 🎯 Overview

The **JHCSC Student Affairs Management System** is designed to digitize and automate the document request process for students and administrators. Students can submit requests for various documents (transcripts, certificates, clearances, etc.) and track their status in real-time. Administrators can manage requests, update statuses, and communicate with students through automated email notifications.

### Purpose

- **Eliminate paperwork** - Digital submission and tracking
- **Improve transparency** - Real-time status updates
- **Enhance communication** - Automated email notifications
- **Streamline workflow** - Organized request management
- **Maintain records** - Complete audit trail

---

## ✨ Features

### For Students
- ✅ **User Registration & Authentication** - Secure JWT-based login
- ✅ **Document Request Submission** - Request official documents with custom forms
- ✅ **Dynamic Application Forms** - Fill out applications with various field types
- ✅ **File Upload Management** - Upload required documents (PDFs, images, DOCX)
- ✅ **Request Tracking** - Real-time status updates (Pending, Processing, Completed)
- ✅ **Email Notifications** - Receive updates when request status changes
- ✅ **Dashboard Analytics** - View submission statistics and history
- ✅ **Template Preview** - Preview document templates before requesting

### For Administrators
- ✅ **Admin Dashboard** - Overview of all requests and statistics
- ✅ **Request Management** - View, update, and process student requests
- ✅ **Status Updates** - Change request status with admin notes
- ✅ **Document Type Management** - Create and manage document types
- ✅ **Dynamic Form Builder** - Create custom form fields for applications
- ✅ **User Management** - Manage student and admin accounts
- ✅ **Requirement Configuration** - Set upload requirements with file type restrictions
- ✅ **Automated Notifications** - System sends emails on status updates

### System Features
- 🔐 **JWT Authentication** - Secure token-based authentication
- 📧 **Email Integration** - NotificationAPI for professional emails
- 🗂️ **File Management** - Organized uploads with validation
- 📊 **RESTful API** - Clean API architecture
- 🎨 **Responsive Design** - Works on desktop and mobile
- 🔍 **Search & Filter** - Easy request filtering and search
- 📝 **Audit Trail** - Track all changes and submissions

---

## 🛠️ Technology Stack

### Backend
- **PHP 8.x** - Server-side scripting
- **MySQL/MariaDB** - Database management
- **PDO** - Database abstraction layer
- **JWT (JSON Web Tokens)** - Authentication

### Frontend
- **HTML5** - Structure
- **CSS3** - Styling (custom, no frameworks)
- **JavaScript (ES6+)** - Client-side logic
- **Fetch API** - AJAX requests

### Third-Party Services
- **NotificationAPI** - Email delivery service
- **Composer** - PHP dependency management

### Development Tools
- **XAMPP/WAMP** - Local development environment
- **phpMyAdmin** - Database management
- **Git** - Version control

---

## 📁 Project Structure

```
student_affairs/
│
├── admin/                          # Admin Dashboard
│   ├── admin.html                  # Admin interface
│   ├── admin.css                   # Admin styles
│   └── admin.js                    # Admin functionality
│
├── api/                            # Backend API
│   ├── config.php                  # Legacy config (CORS headers)
│   ├── login.php                   # User authentication
│   ├── register.php                # User registration
│   ├── test_db.php                 # Database connection test
│   ├── test_jwt.php                # JWT validation test
│   ├── .htaccess                   # Apache configuration
│   │
│   ├── config/                     # Configuration
│   │   └── database.php            # Database connection class
│   │
│   ├── dashboard/                  # Dashboard APIs
│   │   └── dashboard.php           # Dashboard statistics
│   │
│   ├── documents/                  # Document Management
│   │   ├── types.php               # Document type CRUD
│   │   ├── requirements.php        # Upload requirements
│   │   ├── manage.php              # Document management
│   │   └── get_request_documents.php  # Fetch request docs
│   │
│   ├── middleware/                 # Authentication Middleware
│   │   ├── auth.php                # Auth verification
│   │   └── jwt.php                 # JWT utilities
│   │
│   ├── models/                     # Data Models
│   │   └── User.php                # User model
│   │
│   ├── request/                    # Request Management
│   │   ├── request.php             # Request CRUD operations
│   │   └── save_form_data.php      # Save dynamic form data
│   │
│   ├── upload/                     # File Upload
│   │   ├── upload.php              # File upload handler
│   │   └── template.php            # Template file upload
│   │
│   └── users/                      # User Management
│       ├── index.php               # User CRUD
│       └── get_user_details.php    # Fetch user details
│
├── Image/                          # Static Images
│   ├── logoJH.png                  # JHCSC logo
│   ├── backgroundJH.jpg            # Background image
│   ├── drag-and-drop.png          # UI icon
│   ├── google-docs.png            # UI icon
│   ├── menus.png                  # UI icon
│   └── trashcan.png               # UI icon
│
├── Login/                          # Login Interface
│   ├── login.html                  # Login page
│   ├── login.css                   # Login styles
│   └── login.js                    # Login functionality
│
├── Register/                       # Registration Interface
│   ├── registration.html           # Registration page
│   ├── registration.css            # Registration styles
│   └── registration.js             # Registration functionality
│
├── SRS/                            # Documentation
│   └── SRS-ALITA-3-FINAL-PROGRAMMER.docx  # Requirements doc
│
├── uploads/                        # Uploaded Files
│   ├── templates/                  # Document templates
│   ├── 2/, 3/, 4/, 5/, 7/, 9/     # User upload folders (by user ID)
│   └── ...
│
├── User/                           # Student Dashboard
│   ├── user-dashboard.html         # Student interface
│   ├── user.css                    # Student styles
│   └── user.js                     # Student functionality
│
├── utils/                          # Utility Functions
│   ├── email.php                   # Email sending functions
│   └── email_template.php          # Email HTML template
│
├── vendor/                         # Composer Dependencies
│   ├── notificationapi/            # Email service SDK
│   ├── squizlabs/                  # PHP CodeSniffer
│   └── pheromone/                  # Security audit tools
│
├── composer.json                   # PHP dependencies
├── composer.lock                   # Locked dependencies
├── student_affairs.sql             # Database schema
├── API_STATUS_UPDATE_EMAIL.md      # Email notification docs
└── README.md                       # This file
```

---

## 🗄️ Database Schema

The system uses a MySQL database named `student_affairs` with the following tables:

### Core Tables

#### `users`
Stores all user accounts (students and admins).
```sql
- id (INT, Primary Key)
- student_id (VARCHAR) - Student ID number
- full_name (VARCHAR)
- email (VARCHAR) - For notifications
- course (VARCHAR)
- password_hash (VARCHAR) - Bcrypt hashed
- role (VARCHAR) - 'student' or 'admin'
- status (VARCHAR) - 'active' or 'inactive'
- created_at (DATETIME)
- updated_at (TIMESTAMP)
```

#### `document_types`
Defines available document types for requests/applications.
```sql
- id (INT, Primary Key)
- name (VARCHAR) - Display name
- type_code (VARCHAR) - Unique identifier
- description (TEXT)
- category (VARCHAR) - 'request' or 'application'
- template_path (VARCHAR) - Path to template file
- requirements (JSON) - Additional requirements
- created_at (DATETIME)
```

#### `requests`
Stores all document requests and applications.
```sql
- id (INT, Primary Key)
- request_id (VARCHAR) - REQ-YYYYMMDD-XXXXX or APP-YYYYMMDD-XXXXX
- student_id (INT, Foreign Key)
- document_type_id (INT, Foreign Key)
- purpose (TEXT)
- status (VARCHAR) - Pending, Processing, Completed, Rejected, Approved
- submission_date (TIMESTAMP)
- admin_notes (TEXT)
- updated_at (TIMESTAMP)
```

#### `required_documents`
Defines file upload requirements for each document type.
```sql
- id (INT, Primary Key)
- document_type_id (INT, Foreign Key)
- requirement_name (VARCHAR)
- requirement_description (TEXT)
- file_type (VARCHAR) - 'image', 'pdf', 'docx', or 'any'
- is_mandatory (BOOLEAN)
- created_at (DATETIME)
```

#### `uploaded_files`
Tracks all uploaded requirement files.
```sql
- id (INT, Primary Key)
- request_id (INT, Foreign Key)
- requirement_name (VARCHAR)
- file_name (VARCHAR)
- file_path (VARCHAR)
- file_size (INT)
- mime_type (VARCHAR)
- created_at (DATETIME)
```

#### `document_form_fields`
Defines dynamic form fields for application forms.
```sql
- id (INT, Primary Key)
- document_type_id (INT, Foreign Key)
- field_name (VARCHAR)
- field_label (VARCHAR)
- field_type (VARCHAR) - text, textarea, select, radio, checkbox, date
- field_options (TEXT) - JSON for select/radio/checkbox
- is_required (BOOLEAN)
- field_order (INT)
- created_at (DATETIME)
```

#### `application_form_data`
Stores submitted data from dynamic forms.
```sql
- id (INT, Primary Key)
- request_id (INT, Foreign Key)
- field_name (VARCHAR)
- file_name (VARCHAR) - For file fields
- field_value (TEXT)
- created_at (DATETIME)
```

---

## 🚀 Installation

### Prerequisites
- **XAMPP/WAMP** (or similar) with:
  - PHP 8.0 or higher
  - MySQL/MariaDB 10.4 or higher
  - Apache server
- **Composer** (for PHP dependencies)
- **Web browser** (Chrome, Firefox, Edge, Safari)

### Step-by-Step Installation

#### 1. Clone or Download the Project
```bash
cd C:\xampp\htdocs\
git clone <repository-url> student_affairs
# OR extract ZIP to C:\xampp\htdocs\student_affairs
```

#### 2. Install Dependencies
```bash
cd student_affairs
composer install
```

#### 3. Create Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create database: `student_affairs`
3. Import: `student_affairs.sql`

#### 4. Configure Database Connection
Edit `api/config/database.php`:
```php
private $host = "localhost";
private $db_name = "student_affairs";
private $username = "root";        // Your MySQL username
private $password = "";            // Your MySQL password
```

#### 5. Configure Email Service
Edit `utils/email.php`:
```php
$notificationapi = new NotificationApi(
    "YOUR_CLIENT_ID",              // Get from NotificationAPI
    "YOUR_CLIENT_SECRET"
);
```

#### 6. Set Up Folders
Ensure these folders exist and are writable:
```bash
mkdir uploads
mkdir uploads/templates
chmod 755 uploads
chmod 755 uploads/templates
```

#### 7. Start Apache & MySQL
- Start XAMPP/WAMP
- Ensure Apache and MySQL are running

#### 8. Access the Application
- **Login**: `http://localhost/student_affairs/Login/login.html`
- **Register**: `http://localhost/student_affairs/Register/registration.html`
- **Admin Dashboard**: `http://localhost/student_affairs/admin/admin.html`

### Default Credentials
The database includes sample users (password: `password123`):
- **Admin**: admin@studentaffairs.edu
- **Student 1**: john.doe@student.edu
- **Student 2**: jane.smith@student.edu

---

## ⚙️ Configuration

### Database Configuration
Located in `api/config/database.php` and `api/config.php`

### Email Configuration
Located in `utils/email.php`
- Uses **NotificationAPI** for email delivery
- Configure client ID and secret
- Customize email templates in `utils/email_template.php`

### CORS Configuration
Located in `api/config.php`
```php
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
```

### File Upload Limits
Configure in `api/upload/upload.php`:
```php
$maxFileSize = 5 * 1024 * 1024; // 5MB
$allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
```

---

## 📡 API Documentation

### Authentication

#### Register User
```
POST /api/register.php
Content-Type: application/json

{
  "student_id": "S2025001",
  "full_name": "John Doe",
  "email": "john@example.com",
  "course": "BS Computer Science",
  "password": "securePassword123"
}
```

#### Login
```
POST /api/login.php
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "securePassword123"
}

Response:
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "full_name": "John Doe",
    "role": "student"
  }
}
```

### Request Management

#### Get All Requests (Student)
```
GET /api/request/request.php
Authorization: Bearer {token}

Response: Array of requests
```

#### Get All Requests (Admin)
```
GET /api/request/request.php?all=true
Authorization: Bearer {admin_token}

Response: Array of all requests
```

#### Create Request
```
POST /api/request/request.php
Authorization: Bearer {token}
Content-Type: application/json

{
  "document_type": "transcript",
  "purpose": "Job application"
}
```

#### Update Request Status (Admin)
```
PUT /api/request/request.php?id={request_id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "status": "Processing",
  "admin_notes": "Documents are being reviewed"
}

Note: Automatically sends email notification to student
```

#### Delete Request
```
DELETE /api/request/request.php?id={request_id}
Authorization: Bearer {token}
```

### Document Types

#### Get All Document Types
```
GET /api/documents/types.php
```

#### Create Document Type (Admin)
```
POST /api/documents/types.php
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Certificate of Enrollment",
  "type_code": "COE",
  "description": "Official certificate of current enrollment",
  "category": "request"
}
```

### File Upload

#### Upload Requirement File
```
POST /api/upload/upload.php
Authorization: Bearer {token}
Content-Type: multipart/form-data

request_id: {request_id}
requirement_name: ID Photo
file: [binary file data]
```

#### Upload Template (Admin)
```
POST /api/upload/template.php
Authorization: Bearer {admin_token}
Content-Type: multipart/form-data

document_type_id: {type_id}
file: [binary file data]
```

### User Management (Admin)

#### Get All Users
```
GET /api/users/index.php
Authorization: Bearer {admin_token}
```

#### Update User
```
PUT /api/users/index.php?id={user_id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "status": "inactive"
}
```

For complete API documentation, see `API_STATUS_UPDATE_EMAIL.md`

---

## 👥 User Roles

### Student Role
**Permissions:**
- Register and login
- Submit document requests
- Fill out application forms
- Upload required documents
- View own requests and status
- Receive email notifications
- Delete pending requests

**Dashboard Features:**
- Request statistics
- Request history
- Template previews
- File upload interface

### Admin Role
**Permissions:**
- Login to admin dashboard
- View all student requests
- Update request statuses
- Add admin notes
- Manage document types
- Create dynamic form fields
- Set upload requirements
- Manage user accounts
- Upload document templates

**Dashboard Features:**
- System-wide statistics
- Request management table
- User management
- Document type configuration
- Form builder

---

## 📧 Email Notifications

The system automatically sends professional email notifications using **NotificationAPI**.

### When Emails Are Sent
1. **New Request Submitted** - Admin receives notification
2. **Status Updated** - Student receives notification with new status
3. **Admin Notes Added** - Included in status update email

### Email Template
Located in `utils/email_template.php`
- JHCSC branding and logo
- Responsive HTML design
- Professional styling
- Clear call-to-action buttons

### Email Functions

#### Send Status Update Email
```php
sendFormStatusUpdateEmail($userId, $formName, $newStatus, $comment);
```

#### Send to All Admins
```php
sendNotificationToAdmins($subject, $bodyContent);
```

#### Custom Email
```php
sendEmail($recipientEmail, $recipientName, $subject, $htmlContent);
```

### Troubleshooting Email
- Check PHP error logs
- Verify NotificationAPI credentials
- Check spam/junk folder
- Ensure valid email addresses in database

See `API_STATUS_UPDATE_EMAIL.md` for detailed email documentation.

---

## 🔒 Security Features

### Authentication & Authorization
- **JWT Tokens** - Secure, stateless authentication
- **Password Hashing** - Bcrypt with cost factor 10
- **Role-Based Access** - Student and admin roles
- **Token Expiration** - Configurable token lifetime

### Input Validation
- **SQL Injection Protection** - PDO prepared statements
- **XSS Prevention** - HTML escaping
- **CSRF Protection** - Token validation
- **File Upload Validation** - MIME type and size checks

### Database Security
- **Prepared Statements** - All queries use PDO prepared statements
- **Error Handling** - Generic error messages to users
- **Connection Security** - Secure PDO configuration

### File Upload Security
- **MIME Type Validation** - Server-side verification
- **File Size Limits** - Configurable maximum size
- **Organized Storage** - User-specific folders
- **Extension Whitelisting** - Only allowed file types

### Security Best Practices
```php
// Example: Prepared statement usage
$stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
$stmt->bindParam(':email', $email);
$stmt->execute();

// Example: Password hashing
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

// Example: JWT validation
$decoded = JWT::decode($token, $secret_key, ['HS256']);
```

---

## 💻 Development

### Adding New Features

#### Add New Document Type
1. Login as admin
2. Navigate to Document Types
3. Click "Add Document Type"
4. Configure fields and requirements
5. Upload template (optional)

#### Add Dynamic Form Fields
1. Go to Document Type management
2. Select document type
3. Add form fields with types:
   - Text input
   - Textarea
   - Select dropdown
   - Radio buttons
   - Checkboxes
   - Date picker

#### Add Upload Requirements
1. Edit document type
2. Add requirement with:
   - Name and description
   - File type restriction (image/pdf/docx/any)
   - Mandatory flag

### Code Style
- **PHP**: Follow PSR-12 standards
- **JavaScript**: ES6+ with async/await
- **CSS**: BEM naming convention
- **Database**: Descriptive table and column names

### Testing
Test files included:
- `api/test_db.php` - Test database connection
- `api/test_jwt.php` - Test JWT functionality

### Debugging
Enable PHP error reporting in development:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

Check logs:
- PHP error log
- Apache error log
- Browser console (F12)

---

## 🐛 Troubleshooting

### Common Issues

#### Cannot Connect to Database
```
Error: Connection error: Access denied
```
**Solution:** Check database credentials in `api/config/database.php`

#### JWT Token Invalid
```
Error: Invalid token
```
**Solution:**
- Token may be expired
- Check `$secret_key` in JWT files
- Clear browser localStorage and login again

#### File Upload Fails
```
Error: File upload failed
```
**Solution:**
- Check folder permissions (755 for uploads/)
- Verify file size limits in php.ini
- Check MIME type restrictions

#### Email Not Sending
```
Email notification failed
```
**Solution:**
- Verify NotificationAPI credentials
- Check error logs for details
- Ensure valid email in user profile
- Check spam folder

#### 404 Not Found on API Calls
```
Error: 404 Not Found
```
**Solution:**
- Check Apache is running
- Verify .htaccess is enabled
- Check file paths in JavaScript
- Enable mod_rewrite in Apache

#### CORS Error
```
Error: CORS policy blocked
```
**Solution:**
- Check CORS headers in `api/config.php`
- Ensure origin matches your localhost
- Clear browser cache

---

## 🤝 Contributors

### Development Team
- **ALITA Team** - Initial development and requirements
- See `SRS/SRS-ALITA-3-FINAL-PROGRAMMER.docx` for full documentation

### Contact
- **Email:** admin@studentaffairs.edu
- **Institution:** JH Cerilles State College
- **Location:** Pagadian Campus, Pagadian, Zamboanga del Sur

---

## 📄 License

© 2025 JH Cerilles State College. All rights reserved.

This project is developed for educational purposes and internal use by JHCSC Department of Student Affairs.

---

## 🎯 Future Enhancements

### Planned Features
- [ ] SMS notifications
- [ ] In-app notifications
- [ ] Mobile application
- [ ] Advanced reporting and analytics
- [ ] Document e-signature
- [ ] Payment integration
- [ ] Multi-language support
- [ ] Chatbot support
- [ ] API rate limiting
- [ ] Two-factor authentication

### Known Issues
- None currently reported

---

## 📞 Support

For technical support or questions:
1. Check this README
2. Review `API_STATUS_UPDATE_EMAIL.md`
3. Check PHP error logs
4. Contact system administrator

---

## 🙏 Acknowledgments

- **JH Cerilles State College** - For supporting this project
- **Department of Student Affairs** - For requirements and feedback
- **NotificationAPI** - Email delivery service
- **Composer** - Dependency management

---

**Last Updated:** January 2025  
**Version:** 1.0.0  
**Status:** Production Ready ✅
