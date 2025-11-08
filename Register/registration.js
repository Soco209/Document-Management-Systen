// =======================
// DOM ELEMENTS
// =======================
const registrationForm = document.getElementById("registrationForm");
const studentIdInput = document.getElementById("studentId");
const fullNameInput = document.getElementById("fullName");
const courseSelect = document.getElementById("course");
const emailInput = document.getElementById("email");
const passwordInput = document.getElementById("password");
const confirmPasswordInput = document.getElementById("confirmPassword");
const registerBtn = document.getElementById("registerBtn");
const successMessage = document.getElementById("successMessage");

// =======================
// VALIDATION FUNCTIONS
// =======================

function validateStudentId(studentId) {
    const studentIdRegex = /^S\d{8}$/;
    if (!studentIdRegex.test(studentId)) {
        return "Student ID must be in format S01234567";
    }
    return null;
}

function validateFullName(fullName) {
    if (fullName.length < 2) {
        return "Full name must be at least 2 characters long";
    }
    return null;
}

function validateCourse(course) {
    if (!course) {
        return "Please select a course";
    }
    return null;
}

function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email) {
        return "Email is required";
    }
    if (!emailRegex.test(email)) {
        return "Please enter a valid email address";
    }
    return null;
}

function validatePassword(password) {
    if (!password) {
        return "Password is required";
    }
    if (password.length < 6) {
        return "Password must be at least 6 characters long";
    }
    return null;
}

function validateConfirmPassword(password, confirmPassword) {
    if (!confirmPassword) {
        return "Please confirm your password";
    }
    if (password !== confirmPassword) {
        return "Passwords do not match";
    }
    return null;
}

// =======================
// UI HELPER FUNCTIONS
// =======================

function showError(field, message) {
    const formGroup = document.getElementById(field).closest('.form-group');
    const errorElement = document.getElementById(`${field}Error`);
    
    if (formGroup && errorElement) {
        formGroup.classList.add('error');
        errorElement.textContent = message;
        errorElement.classList.add('show');
    }
}

function clearError(field) {
    const formGroup = document.getElementById(field).closest('.form-group');
    const errorElement = document.getElementById(`${field}Error`);
    
    if (formGroup && errorElement) {
        formGroup.classList.remove('error');
        errorElement.classList.remove('show');
        setTimeout(() => {
            errorElement.textContent = '';
        }, 200);
    }
}

function setLoading(loading) {
    if (registerBtn) {
        registerBtn.classList.toggle('loading', loading);
        registerBtn.disabled = loading;
    }
}

function showSuccess() {
    if (registrationForm && successMessage) {
        registrationForm.style.display = 'none';
        successMessage.classList.add('show');
        
        // Redirect to login page after 2 seconds
        setTimeout(() => {
            window.location.href = "../Login/login.html";
        }, 2000);
    }
}

// =======================
// API REGISTRATION FUNCTION
// =======================

async function registerUser(email, password, studentId, course, fullName) {
    try {
        console.log('Sending registration request...');
        
        const response = await fetch('http://localhost/student_affairs/api/register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                studentId: studentId,
                fullName: fullName,
                course: course,
                email: email,
                password: password,
                confirmPassword: confirmPasswordInput.value
            })
        });

        console.log('Response status:', response.status);
        const data = await response.json();
        console.log('Response data:', data);
        
        return {
            success: response.ok,
            message: data.message
        };
    } catch (error) {
        console.error('Registration error:', error);
        return {
            success: false,
            message: 'Network error. Please check if server is running.'
        };
    }
}

// =======================
// EVENT HANDLERS
// =======================

function handleInputValidation() {
    // Real-time validation as user types
    if (studentIdInput) {
        studentIdInput.addEventListener('input', () => clearError('studentId'));
    }
    if (fullNameInput) {
        fullNameInput.addEventListener('input', () => clearError('fullName'));
    }
    if (courseSelect) {
        courseSelect.addEventListener('change', () => clearError('course'));
    }
    if (emailInput) {
        emailInput.addEventListener('input', () => clearError('email'));
    }
    if (passwordInput) {
        passwordInput.addEventListener('input', () => clearError('password'));
    }
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', () => clearError('confirmPassword'));
    }
}

function validateAllFields(studentId, fullName, course, email, password, confirmPassword) {
    let isValid = true;

    // Validate each field
    const studentIdError = validateStudentId(studentId);
    if (studentIdError) {
        showError('studentId', studentIdError);
        isValid = false;
    }

    const fullNameError = validateFullName(fullName);
    if (fullNameError) {
        showError('fullName', fullNameError);
        isValid = false;
    }

    const courseError = validateCourse(course);
    if (courseError) {
        showError('course', courseError);
        isValid = false;
    }

    const emailError = validateEmail(email);
    if (emailError) {
        showError('email', emailError);
        isValid = false;
    }

    const passwordError = validatePassword(password);
    if (passwordError) {
        showError('password', passwordError);
        isValid = false;
    }

    const confirmPasswordError = validateConfirmPassword(password, confirmPassword);
    if (confirmPasswordError) {
        showError('confirmPassword', confirmPasswordError);
        isValid = false;
    }

    return isValid;
}

async function handleRegistration(e) {
    e.preventDefault();

    const studentId = studentIdInput.value.trim();
    const fullName = fullNameInput.value.trim();
    const course = courseSelect.value;
    const email = emailInput.value.trim();
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    // Clear previous errors
    clearError('studentId');
    clearError('fullName');
    clearError('course');
    clearError('email');
    clearError('password');
    clearError('confirmPassword');

    // Validate all fields
    if (!validateAllFields(studentId, fullName, course, email, password, confirmPassword)) {
        return;
    }

    setLoading(true);

    try {
        // Register the user via API
        const result = await registerUser(email, password, studentId, course, fullName);

        if (result.success) {
            showSuccess();
        } else {
            // Show appropriate error message
            if (result.message.includes('email') || result.message.includes('Email')) {
                showError('email', result.message);
            } else if (result.message.includes('Student ID')) {
                showError('studentId', result.message);
            } else if (result.message.includes('Password')) {
                showError('password', result.message);
            } else {
                showError('email', result.message);
            }
        }
    } catch (error) {
        console.error('Registration failed:', error);
        showError('email', 'Registration failed. Please try again.');
    } finally {
        setLoading(false);
    }
}

// =======================
// INITIALIZATION
// =======================

function initRegistrationForm() {
    if (registrationForm) {
        registrationForm.addEventListener("submit", handleRegistration);
        handleInputValidation();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initRegistrationForm);