// scripts.js

// Function to validate registration form
function validateRegistrationForm() {
    const username = document.querySelector('input[name="username"]').value.trim();
    const email = document.querySelector('input[name="email"]').value.trim();
    const password = document.querySelector('input[name="password"]').value.trim();
    const errorMessage = document.getElementById('error-message');

    // Clear previous error messages
    errorMessage.textContent = '';

    if (username === '' || email === '' || password === '') {
        errorMessage.textContent = 'All fields are required.';
        return false;
    }

    if (!validateEmail(email)) {
        errorMessage.textContent = 'Invalid email address.';
        return false;
    }

    return true;
}

// Function to validate email format
function validateEmail(email) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailPattern.test(email);
}

// Function to handle form submission with AJAX
function submitForm(event, formId, url) {
    event.preventDefault();

    const form = document.getElementById(formId);
    const formData = new FormData(form);

    fetch(url, {
        method: 'POST',
        body: formData,
    })
    .then(response => response.text())
    .then(result => {
        document.getElementById('result-message').innerHTML = result;
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('result-message').textContent = 'An error occurred. Please try again.';
    });
}

// Event listeners for form submissions
document.addEventListener('DOMContentLoaded', () => {
    const registrationForm = document.getElementById('registration-form');
    const loginForm = document.getElementById('login-form');

    if (registrationForm) {
        registrationForm.addEventListener('submit', (event) => {
            if (validateRegistrationForm()) {
                submitForm(event, 'registration-form', 'register.php');
            }
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', (event) => {
            submitForm(event, 'login-form', 'login.php');
        });
    }
});
