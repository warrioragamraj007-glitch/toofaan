document.getElementById('registrationForm').addEventListener('submit', function(event) {
    event.preventDefault();
    let valid = true;

    // Clear previous errors
    document.querySelectorAll('.error').forEach(function(errorElement) {
        errorElement.textContent = '';
        errorElement.style.display = 'none';
    });

    // Email validation
    const email = document.getElementById('email').value;
    const emailError = document.getElementById('emailError');
    if (!validateEmail(email)) {
        emailError.textContent = 'Please enter a valid email address.';
        emailError.style.display = 'block';
        valid = false;
    }

    // Phone number validation
    const phone = document.getElementById('phone').value;
    const phoneError = document.getElementById('phoneError');
    if (!/^\d{10}$/.test(phone)) {
        phoneError.textContent = 'Please enter a valid 10-digit phone number.';
        phoneError.style.display = 'block';
        valid = false;
    }

    // Password validation
    const password = document.getElementById('password').value;
    const passwordError = document.getElementById('passwordError');
    if (password.length < 8) {
        passwordError.textContent = 'Password must be at least 8 characters long.';
        passwordError.style.display = 'block';
        valid = false;
    }

    // Confirm password validation
    const confirmPassword = document.getElementById('confirmPassword').value;
    const confirmPasswordError = document.getElementById('confirmPasswordError');
    if (password !== confirmPassword) {
        confirmPasswordError.textContent = 'Passwords do not match.';
        confirmPasswordError.style.display = 'block';
        valid = false;
    }

    if (valid) {
        const confirmRegister = document.getElementById('confirmregister');
        confirmRegister.textContent = 'Registration successful!';
        confirmRegister.style.display = 'block';
    }
});

function validateEmail(email) {
    const re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return re.test(String(email).toLowerCase());
}
