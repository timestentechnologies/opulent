<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('admin/connect.php');

// Check connection before query
$conn = checkConnection($conn);

$sql_header_logo = "select * from manage_website"; 
$result_header_logo = $conn->query($sql_header_logo);
if (!$result_header_logo) {
    die("Query failed: " . $conn->error);
}
$row_header_logo = mysqli_fetch_array($result_header_logo);
?>

<!-- Login Modal -->
<div id="loginModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 relative">
            <div class="absolute top-0 right-0 pt-4 pr-4">
                <button type="button" onclick="closeModal('loginModal')" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Close</span>
                    <i class="ri-close-line text-2xl"></i>
                </button>
            </div>
            <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Login to Your Account</h3>
                    <div class="mt-4">
                        <form id="loginForm" class="space-y-6">
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <div class="mt-1">
                                    <input type="email" name="email" id="email" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                </div>
                            </div>
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                <div class="mt-1 relative">
                                    <input type="password" name="password" id="password" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                    <button type="button" class="toggle-password absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <i class="ri-eye-line text-gray-400 hover:text-gray-500"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                                    <label for="remember" class="ml-2 block text-sm text-gray-900">Remember me</label>
                                </div>
                                <div class="text-sm">
                                    <a href="forgot_password.php" class="font-medium text-primary hover:text-indigo-500">Forgot password?</a>
                                </div>
                            </div>
                            <div>
                                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">Login</button>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-600">Don't have an account? <a href="sign_up.php" class="font-medium text-primary hover:text-indigo-500">Sign up</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Modal functionality
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

function closeAllModals() {
    const modals = ['loginModal', 'placeOrderModal', 'profileModal', 'trackOrderModal', 'contactModal', 'subscriptionModal', 'subscriptionSuccessModal'];
    modals.forEach(modalId => closeModal(modalId));
}

// Add click event listeners for opening modals
document.addEventListener('DOMContentLoaded', function() {
    // Add click event listeners for opening modals
    document.querySelectorAll('[href="login.php"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            closeAllModals();
            openModal('loginModal');
        });
    });

    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('ri-eye-line');
                icon.classList.add('ri-eye-off-line');
            } else {
                input.type = 'password';
                icon.classList.remove('ri-eye-off-line');
                icon.classList.add('ri-eye-line');
            }
        });
    });

    // Form submissions using AJAX
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('login_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    if (typeof showSuccessModal === 'function' && document.getElementById('successModal')) {
                        showSuccessModal({ error: true, message: data.message || 'Login failed. Please try again.' });
                    } else {
                        const existing = document.getElementById('loginModalError');
                        if (existing) existing.remove();
                        const container = document.querySelector('#loginModal form');
                        if (container) {
                            const div = document.createElement('div');
                            div.id = 'loginModalError';
                            div.className = 'bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded';
                            div.textContent = data.message || 'Login failed. Please try again.';
                            container.prepend(div);
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof showSuccessModal === 'function' && document.getElementById('successModal')) {
                    showSuccessModal({ error: true, message: 'An error occurred. Please try again.' });
                } else {
                    const existing = document.getElementById('loginModalError');
                    if (existing) existing.remove();
                    const container = document.querySelector('#loginModal form');
                    if (container) {
                        const div = document.createElement('div');
                        div.id = 'loginModalError';
                        div.className = 'bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded';
                        div.textContent = 'An error occurred. Please try again.';
                        container.prepend(div);
                    }
                }
            });
        });
    }
});
</script>