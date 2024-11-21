// Add any client-side validation or interactivity here
document.addEventListener('DOMContentLoaded', function() {
    // Example: Password strength meter
    const passwordInput = document.querySelector('input[name="password"]');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            if (password.match(/[a-z]+/)) strength += 1;
            if (password.match(/[A-Z]+/)) strength += 1;
            if (password.match(/[0-9]+/)) strength += 1;
            if (password.match(/[$@#&!]+/)) strength += 1;

            let strengthText = '';
            switch (strength) {
                case 0:
                case 1:
                    strengthText = 'Weak';
                    break;
                case 2:
                    strengthText = 'Medium';
                    break;
                case 3:
                    strengthText = 'Strong';
                    break;
                case 4:
                    strengthText = 'Very Strong';
                    break;
            }

            const strengthMeter = document.getElementById('password-strength');
            if (strengthMeter) {
                strengthMeter.textContent = `Password Strength: ${strengthText}`;
            }
        });
    }
});