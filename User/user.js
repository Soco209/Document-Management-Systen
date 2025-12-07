// Student Dashboard JavaScript - Connected to Backend API

// Global variables
let studentRequests = [];
let documentTypes = [];
let requirementFiles = {}; // To store files for specific requirements
let currentStudent = null; 

// API Configuration
const API_BASE = '/student_affairs/api';

// Initialize Student Dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeStudentDashboard();
});

async function initializeStudentDashboard() {
    try {
        console.log("🎓 Initializing Student Dashboard...");
        
        // Check authentication
        const userData = localStorage.getItem('userData') || sessionStorage.getItem('userData');
        if (!userData) { // This check is now robust
            console.log("❌ No user data found, redirecting to login");
            window.location.href = '/student_affairs/Login/login.html';
            return;
        }

        currentStudent = JSON.parse(userData);
        console.log("👤 Current student:", currentStudent);

        // Verify user is a student
        if (currentStudent.role !== 'student') {
            console.log("❌ User is not a student, redirecting");
            if (currentStudent.role === 'admin') {
                window.location.href = '/student_affairs/admin/admin.html';
            } else {
                window.location.href = '/student_affairs/Login/login.html';
            }
            return;
        }
        
        // Update welcome message
        document.getElementById('studentWelcome').textContent = `Welcome, ${currentStudent.full_name}`;
        
        // Setup event listeners first to ensure the UI is interactive
        setupEventListeners();

        // Load data from backend
        await loadStudentRequests();
        await loadDocumentTypes();
        
        console.log("✅ Student Dashboard initialized successfully!");

    } catch (error) {
        console.error("❌ Student Dashboard initialization failed:", error);
        if (error.message.includes('Authentication') || error.message.includes('401')) {
            window.location.href = '/student_affairs/Login/login.html';
        }
    }
}

function setupEventListeners() {
    // Navigation
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetSection = this.getAttribute('data-section');
            showSection(targetSection);
        });
    });

    // Logout
    document.getElementById('studentLogoutBtn').addEventListener('click', logout);

    // Status filter
    document.getElementById('studentStatusFilter').addEventListener('change', function() {
        renderRequestsTable(this.value);
    });

    // Form submissions
    document.getElementById('newRequestForm').addEventListener('submit', handleNewRequest);
    document.getElementById('applyDocumentForm').addEventListener('submit', handleApplyDocument);

    // Cancel buttons
    document.getElementById('cancelRequest').addEventListener('click', resetNewRequestForm);
    document.getElementById('cancelApply').addEventListener('click', resetApplyForm);

    // Document type changes
    document.getElementById('requestDocType').addEventListener('change', updateTemplatePreview);
    document.getElementById('applyDocType').addEventListener('change', updateApplyForm);
    
    // Initialize apply form on page load
    updateApplyForm();

    // Event delegation for requirement uploads
    document.getElementById('uploadRequirementsList').addEventListener('click', handleRequirementUploadClick);
    document.getElementById('requirementFileInput').addEventListener('change', handleRequirementFileSelect);

    // Modal controls
    document.getElementById('closeModal').addEventListener('click', hideDetailsModal);
    document.getElementById('cancelConfirm').addEventListener('click', hideConfirmModal);
    document.getElementById('confirmSubmit').addEventListener('click', confirmSubmission);

    // Event delegation for delete buttons
    document.getElementById('studentTableBody').addEventListener('click', function(event) {
        if (event.target.matches('.delete-request-btn')) {
            const requestId = event.target.getAttribute('data-request-id');
            handleDeleteRequest(requestId);
        }
    });
}

// API Service functions
const apiService = {
    async makeRequest(endpoint, options = {}) {
        try {
            // Correctly retrieve the token from the 'userData' object
            const userDataString = localStorage.getItem('userData') || sessionStorage.getItem('userData');
            const userData = userDataString ? JSON.parse(userDataString) : null;
            const token = userData ? userData.token : null;

            if (!token) throw new Error('Authentication token not found.');

            const response = await fetch(`${API_BASE}${endpoint}`, {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    ...options.headers,
                },
                ...options
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    },

    async getDashboardData() {
        return await this.makeRequest('/dashboard/dashboard.php');
    },

    async getRequests() {
        return await this.makeRequest('/request/request.php');
    },

    async getDocumentTypes() {
        // Use manage.php to get document types with form fields
        return await this.makeRequest('/documents/manage.php');
    },

    async getTemplatePreview(docTypeCode) {
        return await this.makeRequest(`/documents/template_preview.php?type_code=${docTypeCode}`);
    },

    async submitRequest(requestData) {
        return await this.makeRequest('/request/request.php', {
            method: 'POST',
            body: JSON.stringify(requestData)
        });
    },

    async uploadRequirementFiles(requestId, filesObject) {
        const formData = new FormData();
        formData.append('request_id', requestId);

        // Append each file to the form data with requirement name
        Object.entries(filesObject).forEach(([uniqueKey, fileData]) => {
            formData.append('files[]', fileData.file);
            formData.append('requirement_names[]', fileData.requirementName);
        });
        
        console.log('Uploading files for request:', requestId);
        console.log('Files count:', Object.keys(filesObject).length);
        console.log('File details:', Object.entries(filesObject).map(([key, data]) => ({
            key: key,
            requirementName: data.requirementName,
            fileName: data.file.name
        })));

        const userDataString = localStorage.getItem('userData') || sessionStorage.getItem('userData');
        const userData = userDataString ? JSON.parse(userDataString) : null;
        const token = userData ? userData.token : null;

        if (!token) throw new Error('Authentication token not found.');

        // Use fetch directly for FormData, without setting Content-Type header
        const response = await fetch(`${API_BASE}/upload/upload.php`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
            },
            body: formData,
        });
        return await response.json();
    },

    async deleteRequest(requestId) {
        return await this.makeRequest(`/request/request.php?id=${requestId}`, {
            method: 'DELETE'
        });
    }
};

async function loadStudentRequests() {
    try {
        console.log("📋 Loading student requests...");
        const result = await apiService.getRequests();
        
        if (result.success) {
            studentRequests = result.data || [];
            updateStatusCards(studentRequests);
            renderRequestsTable();
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error("❌ Failed to load requests:", error);
        studentRequests = [];
        updateStatusCards([]); // Reset cards on failure
        renderRequestsTable();
    }
}

async function loadDocumentTypes() {
    try {
        console.log("📄 Loading document types...");
        const result = await apiService.getDocumentTypes();
        
        if (result.success) {
            documentTypes = result.data || [];
            populateDocumentDropdowns();
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Failed to load document types:', error);
        // If the API fails, we should not populate with fallback data.
        // An empty dropdown is a clearer indicator of a problem.
        // The error is likely in the backend API (e.g., types.php).
    }
}

function populateDocumentDropdowns() {
    const requestSelect = document.getElementById('requestDocType');
    const applySelect = document.getElementById('applyDocType');

    // Clear existing options
    requestSelect.innerHTML = '<option value="">Select Document Type</option>';
    applySelect.innerHTML = '<option value="">Select Document Type</option>';

    // Add options
    documentTypes.forEach(doc => {
        const option = `<option value="${doc.type_code}">${doc.name}</option>`;
        requestSelect.innerHTML += option;
        applySelect.innerHTML += option;
    });
}

function updateStatusCards(requests) {
    const total = requests.length;
    const pending = requests.filter(r => r.status.toLowerCase() === 'pending').length;
    const processing = requests.filter(r => r.status.toLowerCase() === 'processing').length;
    const completed = requests.filter(r => r.status.toLowerCase() === 'completed').length;

    document.getElementById('studentTotal').textContent = total;
    document.getElementById('studentPending').textContent = pending;
    document.getElementById('studentProcessing').textContent = processing;
    document.getElementById('studentCompleted').textContent = completed;
}

// Navigation
function showSection(sectionId) {
    // Hide all sections
    const sections = document.querySelectorAll('.item.content > section');
    sections.forEach(section => {
        section.hidden = true;
    });
    
    // Show target section
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.hidden = false;
    }
    
    // Update active nav link
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('data-section') === sectionId) {
            link.classList.add('active');
        }
    });
}

// Request Table Rendering
function renderRequestsTable(statusFilter = '') {
    const tbody = document.getElementById('studentTableBody');
    
    const filteredRequests = statusFilter ? 
        studentRequests.filter(request => request.status === statusFilter) : 
        studentRequests;
    
    if (filteredRequests.length === 0) {
        tbody.innerHTML = `
            <tr class="empty-state">
                <td colspan="5" class="no-data">
                    ${statusFilter ? `No ${statusFilter.toLowerCase()} requests found` : 'No requests found'}
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = filteredRequests.map(request => `
        <tr>
            <td><strong>${request.request_id}</strong></td>
            <td>${request.document_name}</td>
            <td>${new Date(request.submission_date).toLocaleDateString()}</td>
            <td><span class="status-badge status-${request.status.toLowerCase().replace(' ', '-')}">${request.status}</span></td>
            <td>
                <button class="btn blue small view-details-btn" data-request-id="${request.request_id}">
                    View Details
                </button>
                ${request.status === 'Pending' ? `
                <button class="btn red small delete-request-btn" data-request-id="${request.request_id}">
                    Delete
                </button>
                ` : ''}
            </td>
        </tr>
    `).join('');
    
    // Add event listeners to the view details buttons
    document.querySelectorAll('.view-details-btn').forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.getAttribute('data-request-id');
            viewRequestDetails(requestId);
        });
    });
}

// Template Preview
async function updateTemplatePreview() {
    const docTypeCode = document.getElementById('requestDocType').value;
    const previewContainer = document.getElementById('templatePreviewContainer');
    const descriptionContainer = document.getElementById('documentDescription');
    const descriptionGroup = document.getElementById('documentDescriptionGroup');

    if (!docTypeCode) {
        previewContainer.innerHTML = '<div class="template-preview-content"><p class="preview-placeholder">Select a document type to see the template preview.</p></div>';
        descriptionGroup.style.display = 'none';
        return;
    }

    const docType = documentTypes.find(doc => doc.type_code === docTypeCode);

    // Display description if available
    if (docType && docType.description) {
        descriptionContainer.innerHTML = `<p>${docType.description}</p>`;
        descriptionGroup.style.display = 'block';
    } else {
        descriptionGroup.style.display = 'none';
    }

    if (docType && docType.template_path) {
        // Ensure proper path separator - add slash if template_path doesn't start with one
        const separator = docType.template_path.startsWith('/') ? '' : '/';
        const templateUrl = `/student_affairs${separator}${docType.template_path}`;
        const isPdf = docType.template_path.toLowerCase().endsWith('.pdf');

        if (isPdf) {
            previewContainer.innerHTML = `
                <div class="template-preview-content">
                    <iframe src="${templateUrl}#toolbar=0" style="width: 100%; height: 500px;" frameborder="0"></iframe>
                </div>
            `;
        } else {
            previewContainer.innerHTML = `
                <div class="template-preview-content">
                    <p class="preview-placeholder">This file type cannot be previewed directly.</p>
                </div>
            `;
        }
    } else {
        previewContainer.innerHTML = `
            <div class="template-preview-content">
                <p class="preview-placeholder">No template available for this document type.</p>
            </div>
        `;
    }
}

// Apply Form Handling
function updateApplyForm() {
    const docTypeCode = document.getElementById('applyDocType').value;
    const formFields = document.getElementById('applicationFormFields');
    const requirementsList = document.getElementById('uploadRequirementsList');
    
    if (!docTypeCode) {
        formFields.innerHTML = '<p style="color: #6c757d; text-align: center; padding: 20px;">Select a document type to see the required fields</p>';
        requirementsList.innerHTML = '';
        return;
    }

    const docType = documentTypes.find(doc => doc.type_code === docTypeCode);
    
    if (!docType) {
        formFields.innerHTML = '<p style="color: #dc3545;">Document type not found.</p>';
        requirementsList.innerHTML = '';
        return;
    }

    // Build dynamic form fields based on admin configuration
    let formHTML = '';
    
    // Check if document type has custom form fields (from manage.php API)
    if (docType.form_fields && docType.form_fields.length > 0) {
        // This is an APPLICATION form with custom fields
        formHTML += '<h4 style="margin-bottom: 15px; color: #2c3e50;">📝 Application Information</h4>';
        formHTML += '<h2 style="margin-bottom: 15px; color: #2c3e50;" font-weight: 300;>Form Submission Guidelines & Required Information</h2>';
        formHTML += '<p style="margin-bottom: 15px; color: #2c3e50;">To complete and submit your application/form successfully, please prepare and provide the exact documents and details listed below. Incomplete submissions will be rejected automatically.</p>';
        // Always add purpose field first for applications
        formHTML += `
            <div class="form-group">
                <label for="applicationPurpose">Purpose of Application <span style="color: red;">*</span></label>
                <textarea id="applicationPurpose" placeholder="Please explain your reason for applying..." required rows="3"></textarea>
            </div>
        `;
        
        docType.form_fields.forEach((field, index) => {
            const fieldId = `custom_field_${index}`;
            const requiredAttr = field.is_required ? 'required' : '';
            const requiredLabel = field.is_required ? '<span style="color: red;">*</span>' : '';
            
            formHTML += `<div class="form-group">`;
            formHTML += `<label for="${fieldId}">${field.field_name} ${requiredLabel}</label>`;
            
            switch (field.field_type) {
                case 'text':
                    formHTML += `<input type="text" id="${fieldId}" name="${field.field_name}" placeholder="Enter ${field.field_name}" ${requiredAttr}>`;
                    break;
                case 'number':
                    formHTML += `<input type="number" id="${fieldId}" name="${field.field_name}" placeholder="Enter ${field.field_name}" ${requiredAttr}>`;
                    break;
                case 'email':
                    formHTML += `<input type="email" id="${fieldId}" name="${field.field_name}" placeholder="Enter ${field.field_name}" ${requiredAttr}>`;
                    break;
                case 'date':
                    formHTML += `<input type="date" id="${fieldId}" name="${field.field_name}" ${requiredAttr}>`;
                    break;
                case 'textarea':
                    formHTML += `<textarea id="${fieldId}" name="${field.field_name}" rows="4" placeholder="Enter ${field.field_name}" ${requiredAttr}></textarea>`;
                    break;
                default:
                    formHTML += `<input type="text" id="${fieldId}" name="${field.field_name}" placeholder="Enter ${field.field_name}" ${requiredAttr}>`;
            }
            
            formHTML += `</div>`;
        });
    } else {
        // This is a simple REQUEST form - just show purpose
        formHTML += '<h4 style="margin-bottom: 15px; color: #2c3e50;">📝 Request Information</h4>';
        formHTML += `
            <div class="form-group">
                <label for="requestPurposeApply">Purpose of Request <span style="color: red;">*</span></label>
                <textarea id="requestPurposeApply" placeholder="Please specify the purpose for this request..." required rows="4"></textarea>
            </div>
        `;
    }
    
    formFields.innerHTML = formHTML;

    // Update requirements based on document type
    const requirements = docType.requirements || [];
    
    if (requirements.length > 0) {
        requirementsList.innerHTML = `
            <h4 style="margin-bottom: 15px; color: #2c3e50;">📤 Required File Uploads</h4>
            <div class="requirements-container">
                ${requirements.map((req, index) => {
                    const reqId = `requirement_${index}`;
                    const fileTypeLabel = getFileTypeLabel(req.file_type || 'any');
                    const acceptTypes = getAcceptTypes(req.file_type || 'any');
                    
                    return `
                        <div class="requirement-item" data-requirement-index="${index}">
                            <div class="requirement-info">
                                <label for="${reqId}">
                                    ${req.requirement_name || req.name}
                                    <span style="color: red;">*</span>
                                </label>
                                <small style="color: #6c757d;">${fileTypeLabel}</small>
                            </div>
                            <div class="requirement-upload">
                                <input type="file" 
                                       id="${reqId}" 
                                       name="${req.requirement_name || req.name}"
                                       accept="${acceptTypes}"
                                       required
                                       onchange="handleRequirementFileChange(this, ${index})"
                                       style="margin-bottom: 5px;">
                                <span class="file-status" id="status_${index}" style="font-size: 0.9em;">No file chosen</span>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    } else {
        requirementsList.innerHTML = '<p style="color: #6c757d; font-style: italic;">No file uploads required for this document type.</p>';
    }
}

function handleRequirementUploadClick(event) {
    // Handle 'Upload' button click
    if (event.target.matches('.upload-req-btn')) {
        const reqId = event.target.getAttribute('data-req-id');
        const fileInput = document.getElementById('requirementFileInput');
        // Store the requirement ID on the file input itself to know which requirement this file is for
        fileInput.setAttribute('data-current-req-id', reqId);
        fileInput.click(); // Programmatically click the hidden file input
    }

    // Handle 'Remove' button click
    if (event.target.matches('.remove-req-btn')) {
        const reqId = event.target.getAttribute('data-req-id');
        delete requirementFiles[reqId]; // Remove the file from our tracking object
        console.log(`File for ${reqId} removed.`);
        // Re-render the form to show the 'Upload' button again
        updateApplyForm();
    }
}

function handleRequirementFileSelect(event) {
    const fileInput = event.target;
    const reqId = fileInput.getAttribute('data-current-req-id');
    const file = fileInput.files[0];

    if (reqId && file) {
        // Check file size (10MB max)
        if (file.size > 5 * 1024 * 1024) { // Align with backend validation (5MB)
            alert(`File ${file.name} is too large. Maximum size is 5MB.`);
            fileInput.value = ''; // Reset file input
            return;
        }

        // Store the file against the requirement ID
        requirementFiles[reqId] = file;
        console.log(`File for ${reqId} selected:`, file.name);

        // Re-render the requirements list to show the "Uploaded" status
        const docTypeCode = document.getElementById('applyDocType').value;
        updateApplyForm(docTypeCode);
    }

    // Reset the file input for the next upload
    fileInput.value = '';
}

function getDocumentRequirements(docTypeCode) {
    const docType = documentTypes.find(doc => doc.type_code === docTypeCode);
    
    if (docType && docType.requirements && docType.requirements.length > 0) {
        // Map the requirements from the API and check against our locally stored files
        return docType.requirements.map(req => ({ 
            ...req, 
            uploaded: !!requirementFiles[req.id] // Check if a file exists for this requirement
        }));
    }
    
    // Return a default or empty array if no specific requirements are found
    return [
        { 
            id: 'default_id', 
            requirement_name: 'Standard Identification', 
            uploaded: !!requirementFiles['default_id'] // Check for the default file as well
        }
    ];
}

// Form Submissions
async function handleNewRequest(event) {
    event.preventDefault();
    
    const docTypeCode = document.getElementById('requestDocType').value;
    const purpose = document.getElementById('requestPurpose').value;
    const notes = document.getElementById('additionalNotes').value;

    if (!docTypeCode || !purpose) {
        alert('Please fill in all required fields.');
        return;
    }

    const docType = documentTypes.find(doc => doc.type_code === docTypeCode);
    
    showConfirmModal(`
        <p><strong>Document Type:</strong> ${docType?.name}</p>
        <p><strong>Purpose:</strong> ${purpose}</p>
        ${notes ? `<p><strong>Additional Notes:</strong> ${notes}</p>` : ''}
        <p style="margin-top: 1rem; color: #6c757d;">Are you sure you want to submit this request?</p>
    `, async () => {
        try {
            const requestData = {
                document_type: docTypeCode,
                purpose: purpose
            };

            const result = await apiService.submitRequest(requestData);
            
            if (result.success) {
                alert('✅ Request submitted successfully! Your Request ID: ' + result.request_id);
                resetNewRequestForm();
                await loadStudentRequests();
                showSection('dashboard');
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Failed to submit request:', error);
            alert('❌ Failed to submit request: ' + error.message);
        }
    });
}

async function handleApplyDocument(event) {
    event.preventDefault();
    
    const docTypeCode = document.getElementById('applyDocType').value;
    
    if (!docTypeCode) {
        alert('Please select a document type.');
        return;
    }
    
    const docType = documentTypes.find(doc => doc.type_code === docTypeCode);
    
    // Determine if this is a simple request or application with custom fields
    let purpose = '';
    
    if (docType.form_fields && docType.form_fields.length > 0) {
        // This is an APPLICATION form with custom fields
        
        // Get the purpose from the application purpose field
        const purposeField = document.getElementById('applicationPurpose');
        purpose = purposeField ? purposeField.value.trim() : '';
        
        if (!purpose) {
            alert('Please fill in the purpose field.');
            return;
        }
        
        // Collect all custom field values
        const formData = {};
        let allFieldsFilled = true;
        
        docType.form_fields.forEach((field, index) => {
            const fieldElement = document.getElementById(`custom_field_${index}`);
            if (fieldElement) {
                const value = fieldElement.value.trim();
                if (field.is_required && !value) {
                    allFieldsFilled = false;
                }
                formData[field.field_name] = value;
            }
        });
        
        if (!allFieldsFilled) {
            alert('Please fill in all required fields.');
            return;
        }
            
    } else {
        // This is a simple REQUEST form - get purpose field
        const purposeField = document.getElementById('requestPurposeApply');
        purpose = purposeField ? purposeField.value.trim() : '';
        
        if (!purpose) {
            alert('Please fill in the purpose field.');
            return;
        }
    }
    
    showConfirmModal(`
        <p><strong>Document Type:</strong> ${docType?.name}</p>
        <p><strong>Purpose:</strong> ${purpose}</p>
        <p style="margin-top: 1rem; color: #6c757d;">Are you sure you want to submit this application?</p>
    `, async () => {
        try {
            // Step 1: Submit the request data to get a request ID
            const requestData = {
                document_type: docTypeCode,
                purpose: purpose
            };

            const requestResult = await apiService.submitRequest(requestData);
            
            if (requestResult.success) {
                const newRequestId = requestResult.id; // Use the new integer ID

                // Step 2: Upload files if any were selected
                const fileCount = Object.keys(requirementFiles).length;
                if (fileCount > 0) {
                    console.log(`Uploading ${fileCount} files...`);
                    try {
                        const uploadResult = await apiService.uploadRequirementFiles(newRequestId, requirementFiles);
                        if (!uploadResult.success) {
                            console.error('File upload failed:', uploadResult.message);
                            alert(`⚠️ Application submitted, but file upload failed: ${uploadResult.message}`);
                        } else {
                            console.log('Files uploaded successfully');
                        }
                    } catch (uploadError) {
                        console.error('File upload error:', uploadError);
                        alert(`⚠️ Application submitted, but file upload encountered an error.`);
                    }
                } else {
                    console.log('No files to upload');
                }
                
                // Step 3: Save form field data to application_form_data table
                if (docType.form_fields && docType.form_fields.length > 0) {
                    console.log('Saving form field data...', {
                        requestId: newRequestId,
                        fieldCount: docType.form_fields.length
                    });
                    try {
                        const saveResult = await saveApplicationFormData(newRequestId, docType.form_fields);
                        console.log('Form data save result:', saveResult);
                    } catch (formDataError) {
                        console.error('Failed to save form data:', formDataError);
                        alert('⚠️ Application submitted, but custom field data was not saved. Error: ' + formDataError.message);
                    }
                }
                
                alert('✅ Application submitted successfully! Your Request ID: ' + requestResult.request_id);
                resetApplyForm();
                await loadStudentRequests();
                showSection('dashboard');
            } else {
                throw new Error(requestResult.message);
            }
        } catch (error) {
            console.error('Failed to submit application:', error);
            alert('❌ Failed to submit application: ' + error.message);
        }
    });
}

// Modal Functions
function showConfirmModal(content, confirmCallback) {
    const modal = document.getElementById('confirmModal');
    const contentDiv = document.getElementById('confirmModalContent');
    
    contentDiv.innerHTML = content;
    modal.classList.add('show');
    
    // Store callback
    modal._confirmCallback = confirmCallback;
}

function hideConfirmModal() {
    document.getElementById('confirmModal').classList.remove('show');
}

async function handleDeleteRequest(requestId) {
    const request = studentRequests.find(req => req.request_id == requestId);
    if (!request) return;

    showConfirmModal(`
        <p>Are you sure you want to delete this request?</p>
        <p><strong>Request ID:</strong> ${request.request_id}</p>
        <p><strong>Document:</strong> ${request.document_name}</p>
    `, async () => {
        try {
            const result = await apiService.deleteRequest(requestId);
            if (result.success) {
                alert('✅ Request deleted successfully!');
                await loadStudentRequests(); // Reload requests to update the UI
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Failed to delete request:', error);
            alert(`❌ Failed to delete request: ${error.message}`);
        }
    });
}

function confirmSubmission() {
    const modal = document.getElementById('confirmModal');
    if (modal._confirmCallback) {
        modal._confirmCallback();
    }
    hideConfirmModal();
}

function viewRequestDetails(requestId) {
    const request = studentRequests.find(req => req.request_id == requestId);
    if (request) {
        const modalContent = document.getElementById('modalContent');
        modalContent.innerHTML = `
            <div class="request-details">
                <h4>Request Information</h4>
                <div class="detail-row">
                    <div class="detail-label">Request ID:</div>
                    <div class="detail-value">${request.request_id}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Document Type:</div>
                    <div class="detail-value">${request.document_name}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Submission Date:</div>
                    <div class="detail-value">${new Date(request.submission_date).toLocaleDateString()}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value">
                        <span class="status-badge status-${request.status.toLowerCase().replace(' ', '-')}">
                            ${request.status}
                        </span>
                    </div>
                </div>
                ${request.purpose ? `
                <div class="detail-row">
                    <div class="detail-label">Purpose:</div>
                    <div class="detail-value">${request.purpose}</div>
                </div>
                ` : ''}
                ${request.admin_notes ? `
                <div class="detail-row">
                    <div class="detail-label">Admin Notes:</div>
                    <div class="detail-value" style="background: #f8f9fa; padding: 0.75rem; border-radius: 4px; border-left: 4px solid #0d6efd;">
                        ${request.admin_notes}
                    </div>
                </div>
                ` : ''}
            </div>
        `;
        showDetailsModal();
    }
}

function showDetailsModal() {
    document.getElementById('detailsModal').classList.add('show');
}

function hideDetailsModal() {
    document.getElementById('detailsModal').classList.remove('show');
}

// Form Reset Functions
function resetNewRequestForm() {
    document.getElementById('newRequestForm').reset();
    document.getElementById('templatePreviewContainer').innerHTML = 
        '<div class="template-preview-content"><p class="preview-placeholder">Select a document type to see the template preview.</p></div>';
}

// Helper functions for file type handling
function getFileTypeLabel(fileType) {
    const labels = {
        'image': 'Accepted: JPG, PNG (Max 5MB)',
        'pdf': 'Accepted: PDF only (Max 10MB)',
        'docx': 'Accepted: Word Document (Max 10MB)',
        'any': 'Accepted: Any file type (Max 10MB)'
    };
    return labels[fileType] || labels['any'];
}

function getAcceptTypes(fileType) {
    const types = {
        'image': 'image/jpeg,image/png,image/jpg',
        'pdf': 'application/pdf',
        'docx': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document,.doc,.docx',
        'any': '*'
    };
    return types[fileType] || '*';
}

// Handle requirement file change
function handleRequirementFileChange(input, index) {
    const statusSpan = document.getElementById(`status_${index}`);
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            alert(`File ${file.name} is too large. Maximum size is 10MB.`);
            input.value = '';
            statusSpan.textContent = 'No file chosen';
            statusSpan.style.color = '#6c757d';
            return;
        }
        
        // Store the file in requirementFiles object with unique key (requirement name + index)
        const reqName = input.name;
        const uniqueKey = `${reqName}_${index}`;
        
        // Store both the file and the original requirement name
        requirementFiles[uniqueKey] = {
            file: file,
            requirementName: reqName
        };
        
        const fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
        statusSpan.textContent = `✓ ${file.name} (${fileSize} MB)`;
        statusSpan.style.color = '#28a745';
        
        console.log(`File stored for requirement: ${reqName} (index: ${index})`, file.name);
    } else {
        statusSpan.textContent = 'No file chosen';
        statusSpan.style.color = '#6c757d';
    }
}

function resetApplyForm() {
    document.getElementById('applyDocumentForm').reset();
    requirementFiles = {}; // Clear stored files
    document.getElementById('applicationFormFields').innerHTML = '<p style="color: #6c757d; text-align: center; padding: 20px;">Select a document type to see the required fields</p>';
    document.getElementById('uploadRequirementsList').innerHTML = '';
}

// Save application form data
async function saveApplicationFormData(requestId, formFields) {
    const formData = {};
    
    formFields.forEach((field, index) => {
        const fieldElement = document.getElementById(`custom_field_${index}`);
        if (fieldElement) {
            formData[field.field_name] = fieldElement.value;
        }
    });
    
    console.log('Preparing to save form data:', {
        requestId,
        formData,
        fieldCount: Object.keys(formData).length
    });
    
    // Send to API to save in application_form_data table
    try {
        const userDataString = localStorage.getItem('userData') || sessionStorage.getItem('userData');
        const userData = userDataString ? JSON.parse(userDataString) : null;
        const token = userData ? userData.token : null;
        
        if (!token) {
            throw new Error('No authentication token found');
        }
        
        console.log('Sending request to save_form_data.php...');
        
        const response = await fetch(`${API_BASE}/request/save_form_data.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                request_id: requestId,
                form_data: formData
            })
        });
        
        console.log('API Response status:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('API Error response:', errorText);
            throw new Error(`API returned ${response.status}: ${errorText}`);
        }
        
        const result = await response.json();
        console.log('API Response:', result);
        
        if (result.success) {
            console.log('✅ Form data saved successfully to application_form_data table');
            return result;
        } else {
            console.error('❌ Failed to save form data:', result.message);
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('❌ Error saving form data:', error);
        throw error; // Re-throw so calling function can handle it
    }
}

// Logout
function logout() {
    localStorage.removeItem('userData');
    // Also clear session storage in case "Remember me" was not checked
    sessionStorage.removeItem('userData');
    
    alert('You have been logged out. Redirecting to login page...');
    window.location.href = '/student_affairs/Login/login.html';
}

// Add CSS for modal and status badges
const style = document.createElement('style');
style.textContent = `
    .modal.show { display: flex !important; }
    .status-badge.status-pending { background-color: #fff3cd; color: #664d03; }
    .status-badge.status-processing { background-color: #cfe2ff; color: #052c65; }
    .status-badge.status-completed { background-color: #d1ecf1; color: #0c5460; }
    .status-badge.status-rejected { background-color: #f8d7da; color: #721c24; }
    .status-badge.status-requires-action { background-color: #ffeaa7; color: #6c5ce7; }
    .requirement-status.uploaded { display: flex; align-items: center; gap: 10px; }
    .file-name-display { 
        font-size: 0.9em; color: #333;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;
        background: #f1f3f5; padding: 4px 8px; border-radius: 4px;
    }
    
    /* Dynamic form field styles */
    .requirements-container {
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-top: 10px;
    }
    
    .requirement-item {
        padding: 15px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }
    
    .requirement-info label {
        font-weight: 600;
        color: #2c3e50;
        display: block;
        margin-bottom: 5px;
    }
    
    .requirement-info small {
        display: block;
        margin-bottom: 10px;
    }
    
    .requirement-upload {
        display: flex;
        flex-direction: column;
    }
    
    .file-status {
        padding: 5px 0;
        color: #6c757d;
    }
    
    #applicationFormFields h4,
    #uploadRequirementsList h4 {
        font-size: 1.1em;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }
`;
document.head.appendChild(style);