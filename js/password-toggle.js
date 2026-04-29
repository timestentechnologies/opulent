/**
 * Password Toggle functionality - Add show/hide eye icon to password fields
 * Usage: Add class 'password-toggle' to any password input
 * Or call initPasswordToggles() after dynamically adding password fields
 */

function initPasswordToggles() {
    const passwordInputs = document.querySelectorAll('input[type="password"]');

    passwordInputs.forEach(function(input) {
        // Skip if already processed
        if (input.classList.contains('toggle-initialized')) {
            return;
        }

        // Create wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'password-input-wrapper';
        wrapper.style.position = 'relative';
        wrapper.style.display = 'block';

        // Wrap the input
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        // Style the input for the toggle button
        input.style.paddingRight = '40px';
        input.classList.add('toggle-initialized');

        // Create toggle button
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'password-toggle-btn';
        toggleBtn.innerHTML = '<i class="fa fa-eye"></i>';
        toggleBtn.title = 'Show password';

        // Style the toggle button
        toggleBtn.style.cssText = `
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px 8px;
            color: #666;
            font-size: 16px;
            z-index: 10;
        `;

        wrapper.appendChild(toggleBtn);

        // Toggle functionality
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();

            if (input.type === 'password') {
                input.type = 'text';
                toggleBtn.innerHTML = '<i class="fa fa-eye-slash"></i>';
                toggleBtn.title = 'Hide password';
            } else {
                input.type = 'password';
                toggleBtn.innerHTML = '<i class="fa fa-eye"></i>';
                toggleBtn.title = 'Show password';
            }
        });
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initPasswordToggles);
