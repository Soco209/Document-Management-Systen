// Admin Dashboard JavaScript - Connected to Backend API

// Global variables
let requests = [];
let documentTypes = [];
let users = [];
let currentEditingDocType = null;
let fileUploadInitialized = false;

// API Configuration
const API_BASE = '/student_affairs/api';

// Initialize Admin Dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeAdminDashboard();
});

async function initializeAdminDashboard() {
    try {
        console.log("👨‍💼 Initializing Admin Dashboard...");
        
        // Check authentication
        const userData = localStorage.getItem('userData') || sessionStorage.getItem('userData');
        if (!userData) { // This check is now robust
            console.log("❌ No user data found, redirecting to login");
            window.location.href = '/student_affairs/Login/login.html';
            return;
        }

        const currentUser = JSON.parse(userData);
        console.log("👤 Current user:", currentUser);

        // Verify user is an admin
        if (currentUser.role !== 'admin') {
            console.log("❌ User is not an admin, redirecting");
            if (currentUser.role === 'student') {
                window.location.href = '/student_affairs/User/user-dashboard.html';
            } else {
                window.location.href = '/student_affairs/Login/login.html';
            }
            return;
        }
        
        // Update welcome message
        document.getElementById('adminWelcome').textContent = `Welcome, ${currentUser.full_name}`;
        
        // Load data from backend
        await loadDashboardData();
        await loadDocumentTypes();
        await loadUsers();
        
        // Setup event listeners
        setupEventListeners();
        
        console.log("✅ Admin Dashboard initialized successfully!");

    } catch (error) {
        console.error("❌ Admin Dashboard initialization failed:", error);
        // Optionally, display an error message to the user on the dashboard itself.
    }
}

function setupEventListeners() {
    // Navigation functionality
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('.item.content > section');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetSection = this.getAttribute('data-section');
            
            // Update active nav link
            navLinks.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
            
            // Show target section, hide others
            sections.forEach(section => {
                if (section.id === targetSection) {
                    section.removeAttribute('hidden');
                    
                    // Load section-specific data
                    if (section.id === 'registered-users') {
                        // loadUsers(); // Now loaded on init
                    }
                } else {
                    section.setAttribute('hidden', '');
                }
            });
        });
    });
    
    // Request status filter
    const adminStatusFilter = document.getElementById('adminStatusFilter');
    adminStatusFilter.addEventListener('change', function() {
        renderRequestsTable(this.value);
    });
    
    // User role filter
    const userRoleFilter = document.getElementById('userRoleFilter');
    userRoleFilter.addEventListener('change', function() {
        renderUsersTable(this.value);
    });

    // Logout
    document.getElementById('adminLogoutBtn').addEventListener('click', logout);
    
    // Modal functionality
    setupModalEventListeners();

    // Defer setup of document type management until section is visible
    let docTypeManagementInitialized = false;
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            const targetSection = this.getAttribute('data-section');
            if (targetSection === 'users' && !docTypeManagementInitialized) {
                setupDocumentTypeManagement();
                docTypeManagementInitialized = true;
            }
        });
    });
}

function setupModalEventListeners() {
    // Details Modal
    const modal = document.getElementById('detailsModal');
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // User Modal
    const userModal = document.getElementById('userModal');
    const closeUserModal = document.getElementById('closeUserModal');
    
    closeUserModal.addEventListener('click', function() {
        userModal.style.display = 'none';
    });
    
    window.addEventListener('click', function(e) {
        if (e.target === userModal) {
            userModal.style.display = 'none';
        }
    });

    // User Details Modal
    const userDetailsModal = document.getElementById('userDetailsModal');
    const closeUserDetailsModal = document.getElementById('closeUserDetailsModal');

    closeUserDetailsModal.addEventListener('click', function() {
        userDetailsModal.style.display = 'none';
    });

    window.addEventListener('click', function(e) {
        if (e.target === userDetailsModal) {
            userDetailsModal.style.display = 'none';
        }
    });
    
    // Document Type Modal
    const docTypeModal = document.getElementById('docTypeModal');
    const closeDocTypeModal = document.getElementById('closeDocTypeModal');
    
    closeDocTypeModal.addEventListener('click', function() {
        docTypeModal.style.display = 'none';
    });
    
    window.addEventListener('click', function(e) {
        if (e.target === docTypeModal) {
            docTypeModal.style.display = 'none';
        }
    });
    
    // Delete Modal logic is now fully handled by openDeleteModal
}

function setupDocumentTypeManagement() {
    // Add document type functionality
    const addDocTypeForm = document.getElementById('addDocTypeForm');
    
    addDocTypeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        handleAddDocumentType();
    });
    
    // Search document types
    const searchInput = document.getElementById('searchDocTypeInput');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const items = document.getElementById('docTypeList').getElementsByTagName('li');
        
        Array.from(items).forEach(item => {
            const nameElement = item.querySelector('.doc-type-name');
            if (nameElement) {
                const text = nameElement.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            }
        });
    });
}

async function handleAddDocumentType() {
    const input = document.getElementById('newDocTypeInput');
    const name = input.value.trim();
    if (!name) {
        alert('Document type name cannot be empty.');
        return;
    }

    try {
        const result = await apiService.addDocumentType(name);
        if (result.success) {
            alert('Document type added successfully!');
            input.value = '';
            await loadDocumentTypes(); // Reload the list
        }
    } catch (error) {
        alert('Failed to add document type: ' + error.message);
    }
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

            const headers = { ...options.headers };
            if (!(options.body instanceof FormData)) {
                headers['Content-Type'] = 'application/json';
            }

            headers['Authorization'] = `Bearer ${token}`;

            const response = await fetch(`${API_BASE}${endpoint}`, {
                ...options,
                headers,
            });
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Failed to parse error response.' }));
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
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

    async getRequestDocuments(requestId) {
        return await this.makeRequest(`/documents/get_request_documents.php?request_id=${requestId}`);
    },

    async getDocumentTypes() {
        return await this.makeRequest('/documents/types.php');
    },

    async updateRequestStatus(requestId, statusData) {
        return await this.makeRequest(`/request/request.php?id=${requestId}`, {
            method: 'PUT',
            body: JSON.stringify(statusData)
        });
    },

    async updateUser(userId, userData) {
        return await this.makeRequest(`/users?id=${userId}`, {
            method: 'PUT',
            body: JSON.stringify(userData)
        });
    },

    async addDocumentType(name) {
        return await this.makeRequest('/documents/types.php', {
            method: 'POST',
            body: JSON.stringify({ name })
        });
    },

    async updateDocumentType(id, data) {
        return await this.makeRequest(`/documents/types.php?id=${id}`, {
            method: 'POST',
            body: data
        });
    },

    async deleteDocumentType(docTypeId) {
        return await this.makeRequest(`/documents/types.php?id=${docTypeId}`, {
            method: 'DELETE'
        });
    },

    async getRequirements(docTypeId) {
        return await this.makeRequest(`/documents/requirements.php?doc_type_id=${docTypeId}`);
    },

    async addRequirement(docTypeId, name) {
        return await this.makeRequest('/documents/requirements.php', {
            method: 'POST',
            body: JSON.stringify({ document_type_id: docTypeId, name })
        });
    },

    async deleteRequirement(requirementId) {
        return await this.makeRequest(`/documents/requirements.php?id=${requirementId}`, {
            method: 'DELETE'
        });
    },

    async getUsers() {
        return await this.makeRequest('/users');
    },

    async getUserDetails(userId) {
        return await this.makeRequest(`/users/get_user_details.php?id=${userId}`);
    }
};

// Data Loading Functions
async function loadDashboardData() {
    try {
        console.log("📊 Loading admin dashboard data...");
        
        // Load dashboard statistics
        const dashResult = await apiService.getDashboardData();
        if (dashResult.success) {
            updateStatusCards(dashResult.data.stats);
        }
        
        // Load requests
        await loadRequests();
        
    } catch (error) {
        console.warn("⚠️ Dashboard data failed, using defaults:", error.message);
        // Set default values
        updateStatusCards({
            total_requests: 0,
            pending_requests: 0,
            processing_requests: 0,
            completed_requests: 0
        });
        await loadRequests();
    }
}

async function loadRequests() {
    try {
        console.log("📋 Loading requests...");
        const result = await apiService.getRequests();
        
        if (result.success) {
            requests = result.data || [];
            renderRequestsTable();
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error("❌ Failed to load requests:", error);
        requests = getSampleRequests();
        renderRequestsTable();
    }
}

async function loadDocumentTypes() {
    try {
        console.log("📄 Loading document types...");
        const result = await apiService.getDocumentTypes();
        
        if (result.success) {
            documentTypes = result.data || [];
            populateDocumentTypesList();
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Failed to load document types:', error);
        documentTypes = getSampleDocumentTypes();
        populateDocumentTypesList();
    }
}

async function loadUsers() {
    try {
        console.log("👥 Loading users...");
        const result = await apiService.getUsers();
        
        if (result.success) {
            users = result.data || [];
            renderUsersTable();
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Failed to load users:', error);
        users = getSampleUsers();
        renderUsersTable();
    }
}

// Update status cards
function updateStatusCards(stats) {
    document.getElementById('adminTotal').textContent = stats.total_requests || 0;
    document.getElementById('adminPending').textContent = stats.pending_requests || 0;
    document.getElementById('adminProcessing').textContent = stats.processing_requests || 0;
    document.getElementById('adminCompleted').textContent = stats.completed_requests || 0;
}

// Render requests table
function renderRequestsTable(statusFilter = '') {
    const adminTableBody = document.getElementById('adminTableBody');
    adminTableBody.innerHTML = '';
    
    const filteredRequests = statusFilter ? 
        requests.filter(request => request.status === statusFilter) : 
        requests;
    
    if (filteredRequests.length === 0) {
        adminTableBody.innerHTML = `
            <tr class="empty-state">
                <td colspan="6" class="no-data">No requests match the current filter. <button id="clearFilterBtn" class="btn blue">Clear Filter</button></td>
            </tr>
        `;
        
        const clearBtn = document.getElementById('clearFilterBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                document.getElementById('adminStatusFilter').value = '';
                renderRequestsTable();
            });
        }
        return;
    }
    
    filteredRequests.forEach(request => {
        const row = document.createElement('tr');
        const statusClass = `status-${request.status.toLowerCase().replace(' ', '-')}`;
        
        row.innerHTML = `
            <td>${request.request_id}</td>
            <td>
                <div class="user-row">
                    <div class="user-avatar">${(request.student_name || 'S').charAt(0)}</div>
                    <div class="user-info-small">
                        <span class="user-name">${request.student_name || 'N/A'}</span>
                        <span class="user-email">${request.student_email || 'N/A'}</span>
                    </div>
                </div>
            </td>
            <td>${request.document_name}</td>
            <td>${new Date(request.submission_date).toLocaleDateString()}</td>
            <td><span class="status-badge ${statusClass}">${request.status}</span></td>
            <td>
                <button class="btn blue small view-request" data-request-id="${request.request_id}">View & Update</button>
            </td>
        `;
        
        adminTableBody.appendChild(row);
    });
    
    // Add event listeners to view buttons
    document.querySelectorAll('.view-request').forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.getAttribute('data-request-id');
            const request = requests.find(r => r.request_id === requestId);
            openRequestModal(request);
        });
    });
}

// Open request details modal
async function openRequestModal(request) {
    const modal = document.getElementById('detailsModal');
    const modalContent = document.getElementById('modalContent');
    
    modalContent.innerHTML = `
        <div class="modal-header">
            <h3 id="modal-heading">Request Details & Status Update - ${request.request_id}</h3>
        </div>
        <div class="modal-body">
            <div class="request-details">
                <h4>Request Information</h4>
                <div class="detail-row">
                    <div class="detail-label">Request ID:</div>
                    <div class="detail-value">${request.request_id}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Student:</div>
                    <div class="detail-value">${request.student_name || 'N/A'} (${request.student_email || 'N/A'})</div>
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
                    <div class="detail-label">Purpose:</div>
                    <div class="detail-value">${request.purpose || 'N/A'}</div>
                </div>
            </div>

            <div id="requestDocumentsSection"></div>
            
            <div class="status-update-section">
                <h4>Update Request Status</h4>
                <div class="status-options">
                    <div class="status-option ${request.status === 'pending' ? 'selected' : ''}" data-status="pending">Pending</div>
                    <div class="status-option ${request.status === 'processing' ? 'selected' : ''}" data-status="processing">Processing</div>
                    <div class="status-option ${request.status === 'completed' ? 'selected' : ''}" data-status="completed">Completed</div>
                    <div class="status-option ${request.status === 'rejected' ? 'selected' : ''}" data-status="rejected">Rejected</div>
                    <div class="status-option ${request.status === 'approved' ? 'selected' : ''}" data-status="approved">Approved</div>
                </div>
                
                <div class="feedback-section">
                    <div class="form-group">
                        <label for="adminFeedback">Admin Notes</label>
                        <textarea id="adminFeedback" placeholder="Provide feedback or notes for the student...">${request.admin_notes || ''}</textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-actions">
            <button id="closeModal" class="btn gray">Cancel</button>
            <button id="updateRequestBtn" class="btn blue">Update Request</button>
        </div>
    `;

    // Fetch and render documents
    try {
        const result = await apiService.getRequestDocuments(request.request_id);
        if (result.success) {
            renderRequestDocuments(result.data);
        }
    } catch (error) {
        console.error('Failed to load request documents:', error);
    }
    
    // Add event listeners to status options
    document.querySelectorAll('.status-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.status-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            this.classList.add('selected');
        });
    });
    
    // Add event listener to close button
    document.getElementById('closeModal').addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    // Add event listener to update button
    document.getElementById('updateRequestBtn').addEventListener('click', function() {
        updateRequestStatus(request);
    });
    
    modal.style.display = 'block';
    modal.focus();
}

function renderRequestDocuments(documents) {
    const documentsSection = document.getElementById('requestDocumentsSection');
    if (!documents || documents.length === 0) {
        documentsSection.innerHTML = '<p>No documents uploaded for this request.</p>';
        return;
    }

    let documentsHTML = '<h4>Uploaded Documents</h4>';
    documents.forEach(doc => {
        documentsHTML += `
            <div class="document-row">
                <span>${doc.file_name}</span>
                <a href="/student_affairs/uploads/${doc.file_path}" class="btn blue small" download>Download</a>
            </div>
        `;
    });

    documentsSection.innerHTML = documentsHTML;
}

// Update request status
async function updateRequestStatus(request) {
    try {
        const selectedStatus = document.querySelector('.status-option.selected')?.getAttribute('data-status');
        const feedback = document.getElementById('adminFeedback')?.value;

        if (!selectedStatus) {
            alert('Please select a status');
            return;
        }

        const statusData = {
            status: selectedStatus,
            admin_notes: feedback || ''
        };

        const result = await apiService.updateRequestStatus(request.request_id, statusData);
        
        if (result.success) {
            alert(`Request ${request.request_id} has been updated to "${selectedStatus}".`);
        } else {
            throw new Error(result.message);
        }
        
        // Update the table and status cards
        renderRequestsTable(document.getElementById('adminStatusFilter').value);
        await loadDashboardData();
        
        // Close modal
        document.getElementById('detailsModal').style.display = 'none';
        
    } catch (error) {
        console.error('Failed to update request status:', error);
        alert('Failed to update request status: ' + error.message);
    }
}

// Document types management
function populateDocumentTypesList() {
    const docTypeList = document.getElementById('docTypeList');
    docTypeList.innerHTML = '';
    
    if (documentTypes.length === 0) {
        docTypeList.innerHTML = `
            <li class="empty-state">No document types found. Add some using the form above.</li>
        `;
        return;
    }
    
    documentTypes.forEach(docType => {
        const li = document.createElement('li');
        const templateInfo = docType.template_path ? 
            `<span class="doc-type-template">(Template Available)</span>` : 
            `<span class="doc-type-template">(No template)</span>`;
        
        li.innerHTML = `
            <div class="doc-type-item">
                <div>
                    <span class="doc-type-name">${docType.name}</span>
                    ${templateInfo}
                </div>
                <div class="doc-type-actions">
                    <button class="btn blue small edit-doc-type" data-id="${docType.id}">Edit</button>
                    <button class="btn red small delete-doc-type" data-id="${docType.id}">Delete</button>
                </div>
            </div>
        `;
        docTypeList.appendChild(li);
    });
    
    // Add event listeners to edit buttons
    document.querySelectorAll('.edit-doc-type').forEach(button => {
        button.addEventListener('click', function() {
            const docTypeId = this.getAttribute('data-id');
            const docType = documentTypes.find(d => d.id == docTypeId);
            openDocTypeModal(docType);
        });
    });
    
    // Add event listeners to delete buttons
    document.querySelectorAll('.delete-doc-type').forEach(button => {
        button.addEventListener('click', async function() {
            const docTypeId = this.getAttribute('data-id');
            const docType = documentTypes.find(d => d.id == docTypeId);

            if (confirm(`Are you sure you want to delete the document type "${docType.name}"?`)) {
                try {
                    const result = await apiService.deleteDocumentType(docType.id);
                    if (result.success) {
                        showAdminNotification('Document type deleted successfully!', 'success');
                        await loadDocumentTypes(); // Reload the list
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    showAdminNotification(`Failed to delete: ${error.message}`, 'error');
                }
            }
        });
    });
}

function setupFileUploadEventListeners() {
    const templateFileInput = document.getElementById('templateFileInput');
    const fileInfo = document.getElementById('fileInfo');

    templateFileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            fileInfo.textContent = this.files[0].name;
        } else {
            fileInfo.innerHTML = 'Supported formats: PDF, DOCX (Max: 10MB)';
        }
    });
}

// Document type modal functions
async function openDocTypeModal(docType) {
    console.log('Opening modal for docType:', docType);
    currentEditingDocType = { ...docType };
    currentEditingDocType.templateFile = null;
    
    document.getElementById('editDocTypeName').value = docType.name;
    
    // Update current template display
    updateCurrentTemplateDisplay();
    
    // Reset file input
    document.getElementById('templateFileInput').value = '';
    
    document.getElementById('docTypeModal').style.display = 'block';
    
    // Setup file upload listeners if not already done
    if (!fileUploadInitialized) {
        setupFileUploadEventListeners();
        fileUploadInitialized = true;
    }
    
    // Save button functionality
    document.getElementById('saveDocTypeBtn').onclick = function() {
        handleSaveDocumentType(docType.id);
    };

    // Load requirements for this document type
    await loadRequirementsForDocType(docType.id);

    // Store the docTypeId on the container
    const requirementsContainer = document.getElementById('requirementsContainer');
    requirementsContainer.setAttribute('data-doc-type-id', docType.id);

    // Add requirement functionality
    const addRequirementBtn = document.getElementById('addRequirementBtn');
    addRequirementBtn.onclick = function() {
        handleAddRequirement();
    };
}

async function loadRequirementsForDocType(docTypeId) {
    try {
        const result = await apiService.getRequirements(docTypeId);
        if (result.success) {
            populateRequirementsList(result.data, docTypeId);
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Failed to load requirements for doc type:', error);
        populateRequirementsList([], docTypeId);
    }
}

function populateRequirementsList(requirements, docTypeId) {
    const requirementsContainer = document.getElementById('requirementsContainer');
    requirementsContainer.innerHTML = '';
    requirementsContainer.setAttribute('data-doc-type-id', docTypeId);

    if (requirements.length === 0) {
        requirementsContainer.innerHTML = '<p>No requirements for this document type.</p>';
        return;
    }

    const ul = document.createElement('ul');
    requirements.forEach(req => {
        const li = document.createElement('li');
        li.innerHTML = `
            <span>${req.requirement_name}</span>
            <button class="btn red small delete-requirement" data-id="${req.id}">Delete</button>
        `;
        ul.appendChild(li);
    });
    requirementsContainer.appendChild(ul);

    // Add event listeners to delete buttons
    document.querySelectorAll('.delete-requirement').forEach(button => {
        button.addEventListener('click', function() {
            const requirementId = this.getAttribute('data-id');
            handleDeleteRequirement(requirementId, docTypeId);
        });
    });
}

async function handleAddRequirement() {
    const requirementsContainer = document.getElementById('requirementsContainer');
    const docTypeId = requirementsContainer.getAttribute('data-doc-type-id');
    
    const input = document.getElementById('newRequirementInput');
    const name = input.value.trim();
    if (!name) return;

    if (!docTypeId) { // Add a guard
        alert('Error: Could not find document type ID.');
        return;
    }

    try {
        const result = await apiService.addRequirement(docTypeId, name);
        if (result.success) {
            alert('Requirement added successfully!');
            input.value = '';
            await loadRequirementsForDocType(docTypeId);
        }
    } catch (error) {
        alert('Failed to add requirement: ' + error.message);
    }
}

async function handleDeleteRequirement(requirementId, docTypeId) {
    if (!confirm('Are you sure you want to delete this requirement?')) return;

    try {
        const result = await apiService.deleteRequirement(requirementId);
        if (result.success) {
            alert('Requirement deleted successfully!');
            await loadRequirementsForDocType(docTypeId);
        }
    } catch (error) {
        alert('Failed to delete requirement: ' + error.message);
    }
}



async function handleSaveDocumentType(docTypeId) {
    const newName = document.getElementById('editDocTypeName').value.trim();
    const templateFileInput = document.getElementById('templateFileInput');
    const file = templateFileInput.files[0];

    if (!newName) {
        alert('Document type name cannot be empty.');
        return;
    }

    const formData = new FormData();
    formData.append('name', newName);
    if (file) {
        formData.append('template', file);
    }
    formData.append('_method', 'PUT');

    try {
        const result = await apiService.updateDocumentType(docTypeId, formData);
        if (result.success) {
            alert('Document type updated successfully!');
            document.getElementById('docTypeModal').style.display = 'none';
            await loadDocumentTypes(); // Reload the list
        }
    } catch (error) {
        alert('Failed to update document type: ' + error.message);
    }
}

function updateCurrentTemplateDisplay() {
    const container = document.getElementById('currentTemplateContainer');
    
    if (currentEditingDocType.template_path) {
        container.innerHTML = `
            <div class="template-info">
                <div>
                    <div class="template-name">${currentEditingDocType.template_path}</div>
                    <div>Template file available</div>
                </div>
                <div class="template-actions">
                    <button class="btn blue small" id="downloadTemplateBtn">Download</button>
                    <button class="btn red small" id="deleteTemplateBtn">Delete</button>
                </div>
            </div>
        `;
        
        // Add event listeners for template actions
        document.getElementById('downloadTemplateBtn').addEventListener('click', function() {
            alert(`Would download: ${currentEditingDocType.template_path}`);
        });
        
        document.getElementById('deleteTemplateBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this template?')) {
                currentEditingDocType.template_path = null;
                // In a real app, this would be an API call. For now, just update UI.
                updateCurrentTemplateDisplay();
            }
        });
    } else {
        container.innerHTML = `
            <div class="no-template">No template uploaded for this document type</div>
        `;
    }
}

function showAdminNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `admin-notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    // Allow the element to be painted to get its width
    setTimeout(() => {
        notification.style.left = `calc(50% - ${notification.offsetWidth / 2}px)`;
        notification.classList.add('visible');
    }, 50);

    setTimeout(() => {
        notification.classList.remove('visible');
        setTimeout(() => {
            notification.remove();
        }, 500);
    }, 3000);
}




// Users management
function renderUsersTable(roleFilter = '') {
    const usersTableBody = document.getElementById('usersTableBody');
    usersTableBody.innerHTML = '';
    
    const filteredUsers = roleFilter ? 
        users.filter(user => user.role === roleFilter) : 
        users;
    
    if (filteredUsers.length === 0) {
        usersTableBody.innerHTML = `
            <tr class="empty-state">
                <td colspan="5" class="no-data">No users found with the selected role. <button id="clearUserFilterBtn" class="btn blue">Clear Filter</button></td>
            </tr>
        `;
        
        const clearBtn = document.getElementById('clearUserFilterBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                document.getElementById('userRoleFilter').value = '';
                renderUsersTable();
            });
        }
        return;
    }
    
    filteredUsers.forEach(user => {
        const row = document.createElement('tr');
        
        const statusClass = user.status === 'active' ? 'status-active' : 'status-inactive';
        const roleClass = `role-${user.role.toLowerCase()}`;
        
        row.innerHTML = `
            <td>
                <div class="user-row">
                    <div class="user-avatar">${user.full_name.charAt(0)}</div>
                    <div class="user-info-small">
                        <span class="user-name">${user.full_name}</span>
                        <span class="user-email">${user.email}</span>
                    </div>
                </div>
            </td>
            <td><span class="role-badge ${roleClass}">${user.role}</span></td>
            <td><span class="status-badge ${statusClass}">${user.status}</span></td>
            <td>${new Date(user.created_at).toLocaleDateString()}</td>
            <td>
                <button class="btn blue edit-user" data-user-id="${user.id}">Edit</button>
                <button class="btn ${user.status === 'active' ? 'red' : 'green'} toggle-user" data-user-id="${user.id}">
                    ${user.status === 'active' ? 'Deactivate' : 'Activate'}
                </button>
            </td>
        `;
        
        usersTableBody.appendChild(row);
    });


    
    // Add event listeners to edit buttons
    document.querySelectorAll('.edit-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const user = users.find(u => u.id == userId);
            openUserModal(user);
        });
    });
    
    // Add event listeners to toggle buttons
    document.querySelectorAll('.toggle-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            handleToggleUserStatus(userId);
        });
    });
}

async function openUserDetailsModal(user) {
    if (!user) {
        alert('Could not find user details.');
        return;
    }

    const modal = document.getElementById('userDetailsModal');
    const modalContent = document.getElementById('userDetailsModalContent');
    modalContent.innerHTML = `<div class="loading-state">Loading details...</div>`;
    modal.style.display = 'block';

    // Close modal event listener
    document.getElementById('closeUserDetailsModal').addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Fetch user details
    try {
        const result = await apiService.getUserDetails(user.id);
        if (result.success) {
            renderUserDetails(result.data);
        }
    } catch (error) {
        console.error('Failed to load user details:', error);
        modalContent.innerHTML = `<div class="error-state">Failed to load user details.</div>`;
    }
}

function renderUserDetails(user) {
    const modalContent = document.getElementById('userDetailsModalContent');

    let requestsHTML = '<h5>Requests</h5>';
    if (user.requests && user.requests.length > 0) {
        requestsHTML += '<ul>';
        user.requests.forEach(req => {
            requestsHTML += `<li>${req.document_name} - ${req.status}</li>`;
        });
        requestsHTML += '</ul>';
    } else {
        requestsHTML += '<p>No requests found for this user.</p>';
    }

    let documentsHTML = '<h5>Uploaded Documents</h5>';
    if (user.documents && user.documents.length > 0) {
        documentsHTML += '<ul>';
        user.documents.forEach(doc => {
            documentsHTML += `<li><a href="/student_affairs/uploads/${doc.file_path}" download>${doc.file_name}</a> (Uploaded on ${new Date(doc.upload_date).toLocaleDateString()})</li>`;
        });
        documentsHTML += '</ul>';
    } else {
        documentsHTML += '<p>No documents found for this user.</p>';
    }

    modalContent.innerHTML = `
        <div class="detail-row">
            <div class="detail-label">Full Name:</div>
            <div class="detail-value">${user.full_name}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Email:</div>
            <div class="detail-value">${user.email}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Role:</div>
            <div class="detail-value">${user.role}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Status:</div>
            <div class="detail-value">${user.status}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Registration Date:</div>
            <div class="detail-value">${new Date(user.created_at).toLocaleDateString()}</div>
        </div>
        ${requestsHTML}
        ${documentsHTML}
    `;
}

function openUserModal(user) {
    document.getElementById('editUserName').value = user.full_name;
    document.getElementById('editUserEmail').value = user.email;
    document.getElementById('editUserRole').value = user.role;
    document.getElementById('editUserStatus').value = user.status;
    
    document.getElementById('userModal').style.display = 'block';
    
    // Save button functionality
    document.getElementById('saveUserBtn').onclick = function() {
        handleSaveUser(user.id);
    };
}

function validateUserInput(userData) {
    if (!userData.full_name || userData.full_name.trim() === '') {
        throw new Error('Full name is required.');
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!userData.email || !emailRegex.test(userData.email)) {
        throw new Error('Invalid email format.');
    }

    const validRoles = ['admin', 'student'];
    if (!userData.role || !validRoles.includes(userData.role)) {
        throw new Error('Invalid role selected.');
    }

    const validStatuses = ['active', 'inactive'];
    if (!userData.status || !validStatuses.includes(userData.status)) {
        throw new Error('Invalid status selected.');
    }
}

async function handleSaveUser(userId) {
    try {
        const userData = {
            full_name: document.getElementById('editUserName').value,
            email: document.getElementById('editUserEmail').value,
            role: document.getElementById('editUserRole').value,
            status: document.getElementById('editUserStatus').value
        };

        validateUserInput(userData);

        const result = await apiService.updateUser(userId, userData);

        if (result.success) {
            alert('User updated successfully!');
            document.getElementById('userModal').style.display = 'none';
            await loadUsers(); // Reload users to reflect changes
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Failed to save user:', error);
        alert('Failed to save user. Please check the details and try again. Error: ' + error.message);
    }
}

async function handleToggleUserStatus(userId) {
    const user = users.find(u => u.id == userId);
    if (!user) return;

    const newStatus = user.status === 'active' ? 'inactive' : 'active';
    
    if (confirm(`Are you sure you want to set this user to "${newStatus}"?`)) {
        try {
            const result = await apiService.updateUser(userId, { status: newStatus });
            if (result.success) {
                alert(`User status updated to ${newStatus}.`);
                await loadUsers(); // Reload users to reflect changes
            }
        } catch (error) {
            alert('Failed to update user status: ' + error.message);
        }
    }
}
// Logout function
function logout() {
    // Clear both storages to ensure a clean logout
    localStorage.removeItem('userData');
    sessionStorage.removeItem('userData');
    
    alert('You have been logged out. Redirecting to login page...');
    window.location.href = '/student_affairs/Login/login.html';
}

// Sample data functions (fallbacks when API fails)
function getSampleRequests() {
    return [
        {
            request_id: "REQ-20241011-001",
            student_name: "John Smith",
            student_email: "john.smith@example.com",
            document_name: "Good Moral Certificate",
            submission_date: "2024-10-10",
            status: "pending",
            purpose: "Job application",
            admin_notes: ""
        },
        {
            request_id: "REQ-20241011-002",
            student_name: "Jane Doe",
            student_email: "jane.doe@example.com",
            document_name: "Transcript of Records",
            submission_date: "2024-10-09",
            status: "processing",
            purpose: "Graduate school application",
            admin_notes: "Processing your request"
        }
    ];
}

function getSampleDocumentTypes() {
    return [
        { id: 1, name: 'Good Moral Certificate', type_code: 'gmc', category: 'template', template_path: '/templates/gmc-template.pdf' },
        { id: 2, name: 'Transcript of Records', type_code: 'tor', category: 'template', template_path: '/templates/tor-template.pdf' }
    ];
}

function getSampleUsers() {
    return [
        { 
            id: 1, 
            full_name: "Admin User", 
            email: "admin@example.com", 
            role: "admin", 
            status: "active", 
            created_at: "2024-01-15" 
        },
        { 
            id: 2, 
            full_name: "John Smith", 
            email: "john.smith@example.com", 
            role: "student", 
            status: "active", 
            created_at: "2024-02-20" 
        },
        { 
            id: 3, 
            full_name: "Jane Doe", 
            email: "jane.doe@example.com", 
            role: "student", 
            status: "active", 
            created_at: "2024-03-10" 
        }
    ];
}

// Add CSS for status badges and modal styling
const style = document.createElement('style');
style.textContent = `
    .status-badge.status-pending { background-color: #fff3cd; color: #664d03; }
    .status-badge.status-processing { background-color: #cfe2ff; color: #052c65; }
    .status-badge.status-completed { background-color: #d1ecf1; color: #0c5460; }
    .status-badge.status-approved { background-color: #d4edda; color: #155724; }
    .status-badge.status-rejected { background-color: #f8d7da; color: #721c24; }
    .status-badge.status-active { background-color: #d4edda; color: #155724; }
    .status-badge.status-inactive { background-color: #f8d7da; color: #721c24; }
    .role-badge.role-admin { background-color: #cfe2ff; color: #052c65; }
    .role-badge.role-student { background-color: #fff3cd; color: #664d03; }
    .role-badge.role-staff { background-color: #d1ecf1; color: #0c5460; }
    .status-option.selected { background-color: #0d6efd; color: white; }
    .file-upload-area.dragover { border-color: #0d6efd; background-color: #f8f9ff; }

    .admin-notification {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        padding: 12px 20px;
        border-radius: 5px;
        background-color: #28a745;
        color: white;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.3s ease-in-out, top 0.3s ease-in-out;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }

    .admin-notification.success {
        background-color: #28a745;
    }

    .admin-notification.error {
        background-color: #dc3545;
    }

    .admin-notification.visible {
        opacity: 1;
        top: 40px;
    }
`;
document.head.appendChild(style);