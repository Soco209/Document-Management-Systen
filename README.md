File Functions & Documentation

Detailed documentation of every file in the system, organized by directory structure.

```
student_affairs/
│
├── admin/                          # Admin Dashboard
│   ├── admin.html                  # Admin interface with request management, user management, document type config
│   ├── admin.css                   # Styling: professional layout, data tables, modal dialogs, form builders
│   └── admin.js                    # Functions: initializeAdminDashboard(), loadRequests(), updateRequestStatus(),
│                                     loadUsers(), loadDocumentTypes(), openDocTypeModal(), addFormField(),
│                                     updateFormField() [NEW Jan 2025], deleteFormField(), saveDocTypeChanges()
│                                     Recent Fix: Added form field update functionality
│
├── api/                            # Backend API
│   ├── config.php                  # Legacy CORS headers configuration
│   ├── login.php                   # POST: User authentication, JWT token generation, bcrypt verification
│   │                                 Returns: {token, user{id, full_name, role}}
│   ├── register.php                # POST: User registration, validates data, checks duplicates, hashes passwords
│   │                                 Creates account with 'student' role
│   ├── test_db.php                 # Database connection test - JSON response with status
│   ├── test_jwt.php                # JWT validation test - Token creation and verification
│   ├── .htaccess                   # Apache configuration for routing
│   │
│   ├── config/                     # Configuration Files
│   │   └── database.php            # Database class: getConnection() - Returns PDO connection with UTF-8
│   │                                 Handles MySQL/MariaDB connections with error management
│   │
│   ├── dashboard/                  # Dashboard Statistics
│   │   └── dashboard.php           # GET: Returns role-based statistics
│   │                                 Admin: total/pending/processing/completed counts
│   │                                 Student: pending/completed counts for own requests
│   │
│   ├── documents/                  # Document Management
│   │   ├── types.php               # GET: Fetch all document types with requirements
│   │   │                             POST: Create document type (Admin only)
│   │   │                             PUT: Update document type name and template (Admin only)
│   │   │                             DELETE: Delete document type if not in use (Admin only)
│   │   │
│   │   ├── manage.php              # GET: Get detailed document types with form fields
│   │   │                             POST: Create document type with fields/requirements OR
│   │   │                                   handleAddFormField() - Add new form field OR
│   │   │                                   handleUpdateFormField() - Update existing field
│   │   │                             PUT: Update document type details
│   │   │                             DELETE: Delete document type OR handleDeleteFormField()
│   │   │                             Functions: handleGetDocumentTypes(), handleCreateDocumentType(),
│   │   │                                       handleUpdateDocumentType(), handleAddFormField(),
│   │   │                                       handleUpdateFormField(), handleDeleteFormField(),
│   │   │                                       handleDeleteDocumentType()
│   │   │
│   │   ├── requirements.php        # GET: Retrieve requirements for document type
│   │   │                             POST: Add upload requirement (Admin only)
│   │   │                             DELETE: Remove requirement (Admin only)
│   │   │                             Functions: handleGet(), handlePost(), handleDelete()
│   │   │                             Supports: file type restrictions (image/pdf/docx/any),mandatory flags
│   │   │
│   │   └── get_request_documents.php # GET: Fetch uploaded files and form data for a request
│   │                                   Returns: {uploaded_files[], form_data[]}
│   │
│   ├── middleware/                 # Authentication Middleware
│   │   ├── auth.php                # Authentication helpers:
│   │   │                             getAuthorizationHeader() - Extract Authorization header
│   │   │                             getBearerToken() - Extract JWT token
│   │   │                             isAuthenticated() - Verify authentication
│   │   │                             getCurrentUserId() - Get user ID from token
│   │   │                             getCurrentUser() - Get full user object
│   │   │                             getRole() - Get user role (student/admin)
│   │   │                             isAdmin() - Check admin status
│   │   │
│   │   └── jwt.php                 # JWT utilities (HS256 algorithm):
│   │                                 generate_jwt() - Create JWT token
│   │                                 is_jwt_valid() - Validate JWT signature
│   │                                 base64url_encode() - URL-safe encoding
│   │                                 base64url_decode() - URL-safe decoding
│   │                                 get_jwt_payload() - Extract payload
│   │
│   ├── models/                     # Data Models
│   │   └── User.php                # User model class for data operations
│   │
│   ├── request/                    # Request Management
│   │   ├── request.php             # GET: Retrieve requests (filtered by role)
│   │   │                             POST: Create new request/application
│   │   │                             PUT: Update request status (Admin only) - Sends email notification
│   │   │                             DELETE: Delete pending request
│   │   │                             Generates unique IDs: REQ-YYYYMMDD-XXXXX or APP-YYYYMMDD-XXXXX
│   │   │
│   │   └── save_form_data.php      # POST: Save dynamic form field data
│   │                                 Validates ownership, stores in application_form_data table
│   │                                 Supports: text, textarea, select, radio, checkbox, date fields
│   │
│   ├── upload/                     # File Upload Handlers
│   │   ├── upload.php              # POST: Student file upload
│   │   │                             Validates: file types (PDF, JPEG, PNG, DOCX, DOC), size (10MB max)
│   │   │                             Creates: user-specific directories (uploads/{request_id}/)
│   │   │                             Stores: file metadata in uploaded_files table
│   │   │
│   │   └── template.php            # POST: Admin template upload (Admin only)
│   │                                 Stores in: uploads/templates/
│   │
│   └── users/                      # User Management
│       ├── index.php               # GET: Retrieve all users (Admin only)
│       │                             PUT: Update user info/status (Admin only)
│       │                             Functions: handleGetUsers(), handleUpdateUser(), handleDeleteUser()
│       │
│       └── get_user_details.php    # GET: Fetch detailed user profile and statistics (Admin only)
│
├── Image/                          # Static Assets
│   ├── logoJH.png                  # JHCSC official logo
│   ├── backgroundJH.jpg            # Dashboard background
│   ├── drag-and-drop.png          # Upload UI icon
│   ├── google-docs.png            # Document icon
│   ├── menus.png                  # Menu icon
│   └── trashcan.png               # Delete icon
│
├── Login/                          # Login Interface
│   ├── login.html                  # Login page structure
│   ├── login.css                   # Login page styling
│   └── login.js                    # Functions: Email/password validation, JWT storage (localStorage/sessionStorage),
│                                     Remember me functionality, Role-based redirect (student/admin)
│
├── Register/                       # Registration Interface
│   ├── registration.html           # Registration form
│   ├── registration.css            # Registration styling
│   └── registration.js             # Functions: Multi-field validation, password strength check,
│                                     Duplicate detection, Auto-login after registration
│
├── SRS/                            # Documentation
│   └── SRS-ALITA-3-FINAL-PROGRAMMER.docx  # Software Requirements Specification
│
├── uploads/                        # File Storage (755 permissions)
│   ├── templates/                  # Document templates (PDF/DOCX) - Admin uploads
│   └── {request_id}/               # User-uploaded files organized by request ID
│
├── User/                           # Student Dashboard
│   ├── user-dashboard.html         # Student interface: request submission, file upload, tracking, statistics
│   ├── user.css                    # Styling: responsive grid, card design, status badges, drag-and-drop
│   └── user.js                     # Functions: initializeDashboard(), loadRequests(), loadDocumentTypes(),
│                                     createRequest(), uploadFiles(), renderDynamicForm(), trackRequest(),
│                                     deleteRequest() - Uses Fetch API for backend communication
│
├── utils/                          # Utility Functions
│   ├── email.php                   # NotificationAPI integration:
│   │                                 sendFormStatusUpdateEmail() - Send status update to student
│   │                                 sendNotificationToAdmins() - Notify all admins
│   │                                 sendEmail() - Generic email sender
│   │                                 Features: HTML templates, JHCSC branding, automatic notifications
│   │
│   └── email_template.php          # getEmailTemplate() - Returns formatted HTML email
│                                     Features: Responsive design, JHCSC logo, professional styling
│
├── vendor/                         # Composer Dependencies
│   ├── notificationapi/            # NotificationAPI PHP SDK - Email service
│   ├── squizlabs/                  # PHP CodeSniffer - Code quality standards
│   └── pheromone/                  # Security audit tools
│
├── composer.json                   # PHP dependency management configuration
├── composer.lock                   # Locked dependency versions
├── student_affairs_database.sql    # Database schema and sample data
│                                     Tables: users, document_types, document_form_fields, requests,
│                                            required_documents, uploaded_files, application_form_data
├── API_STATUS_UPDATE_EMAIL.md      # Email notification system documentation
└── README.md                       # This file - Complete system documentation