// Load form data from session storage when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Check if we have form data from PHP processing
    const urlParams = new URLSearchParams(window.location.search);
    const submitted = urlParams.get('submitted');
    
    if (submitted) {
        // In a real app, you might fetch this via AJAX or use PHP sessions
        // For now, we'll rely on the form showing its own state after POST
        console.log('Form was submitted');
    }
    
    // Add form validation
    const form = document.getElementById('signupForm');
    form.addEventListener('submit', function(e) {
        let valid = true;
        
        // Clear previous errors
        document.querySelectorAll('.error').forEach(el => el.textContent = '');
        
        // Validate name
        const name = document.getElementById('name').value.trim();
        if (!name) {
            document.getElementById('name-error').textContent = 'Please enter your name.';
            valid = false;
        } else if (!/^[a-zA-Z ]*$/.test(name)) {
            document.getElementById('name-error').textContent = 'Only letters and white space allowed.';
            valid = false;
        }
        
        // Validate email
        const email = document.getElementById('email').value.trim();
        if (!email) {
            document.getElementById('email-error').textContent = 'Please enter an email.';
            valid = false;
        } else if (!/\S+@\S+\.\S+/.test(email)) {
            document.getElementById('email-error').textContent = 'Invalid email format.';
            valid = false;
        }
        
        // Validate password
        const password = document.getElementById('password').value;
        if (!password) {
            document.getElementById('password-error').textContent = 'Please enter a password.';
            valid = false;
        } else if (password.length < 6) {
            document.getElementById('password-error').textContent = 'Password must have at least 6 characters.';
            valid = false;
        }
        
        // Validate confirm password
        const confirmPassword = document.getElementById('confirm_password').value;
        if (!confirmPassword) {
            document.getElementById('confirm-password-error').textContent = 'Please confirm password.';
            valid = false;
        } else if (password !== confirmPassword) {
            document.getElementById('confirm-password-error').textContent = 'Password did not match.';
            valid = false;
        }
        
        if (!valid) {
            e.preventDefault();
        }
    });
});