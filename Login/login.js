document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const rememberCheckbox = document.getElementById('remember');
    const loginBtn = document.querySelector('.login-btn');
    const passwordToggle = document.getElementById('passwordToggle');
    const successMessage = document.getElementById('successMessage');
    const loginCard = document.querySelector('.login-card');

    // --- Helper Functions for Validation ---
    const showError = (input, message) => {
        const formGroup = input.closest('.form-group');
        const errorElement = formGroup.querySelector('.error-message');
        formGroup.classList.add('error');
        errorElement.textContent = message;
        errorElement.classList.add('show');
    };

    const clearError = (input) => {
        const formGroup = input.closest('.form-group');
        const errorElement = formGroup.querySelector('.error-message');
        formGroup.classList.remove('error');
        errorElement.classList.remove('show');
        errorElement.textContent = '';
    };

    const validateEmail = (email) => {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(String(email).toLowerCase());
    };

    // --- Event Listeners ---

    // Clear errors on input
    emailInput.addEventListener('input', () => clearError(emailInput));
    passwordInput.addEventListener('input', () => clearError(passwordInput));

    // Password visibility toggle
    passwordToggle.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        passwordToggle.querySelector('.toggle-icon').classList.toggle('show-password', !isPassword);
        passwordToggle.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
    });

    // Form submission handler
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        // 1. Clear previous errors
        clearError(emailInput);
        clearError(passwordInput);

        // 2. Get form values
        const email = emailInput.value.trim();
        const password = passwordInput.value;
        const rememberMe = rememberCheckbox.checked;

        // 3. Basic frontend validation
        let isValid = true;
        if (!email) {
            showError(emailInput, 'Email is required.');
            isValid = false;
        } else if (!validateEmail(email)) {
            showError(emailInput, 'Please enter a valid email address.');
            isValid = false;
        }

        if (!password) {
            showError(passwordInput, 'Password is required.');
            isValid = false;
        }

        if (!isValid) {
            return;
        }

        // 4. Show loading state
        loginBtn.classList.add('loading');
        loginBtn.disabled = true;

        // 5. API Call
        try {
            const response = await fetch('/student_affairs/api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password, remember: rememberMe }),
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // --- Success ---
                // Store user data
                const storage = rememberMe ? localStorage : sessionStorage;
                storage.setItem('userData', JSON.stringify(result.user));

                // Show success animation and redirect
                loginForm.style.display = 'none';
                successMessage.classList.add('show');

                setTimeout(() => {
                    const destination = result.user.role === 'admin' 
                        ? '/student_affairs/admin/admin.html' 
                        : '/student_affairs/user/user-dashboard.html';
                    window.location.replace(destination);
                }, 1500); // Wait for animation

            } else {
                // --- Server-side error ---
                showError(passwordInput, result.message || 'Invalid credentials. Please try again.');
            }
        } catch (error) {
            // --- Network or unexpected error ---
            console.error('Login error:', error);
            showError(passwordInput, 'An unexpected error occurred. Please check your connection and try again.');
        } finally {
            // --- Always run this ---
            // 6. Reset button state
            loginBtn.classList.remove('loading');
            loginBtn.disabled = false;
        }
    });

    // Floating label functionality
    const inputs = document.querySelectorAll('.input-wrapper input');
    inputs.forEach(input => {
        // Add a placeholder with a space to make the :not(:placeholder-shown) selector work reliably
        if (!input.placeholder) {
            input.placeholder = ' ';
        }
        // For browsers that autofill, we need to check if the value is already there on load
        if (input.value) {
            input.classList.add('has-value');
        }
        input.addEventListener('input', () => {
            if (input.value) {
                input.classList.add('has-value');
            } else {
                input.classList.remove('has-value');
            }
        });
    });
});