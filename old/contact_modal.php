<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Get customer information if logged in
$customerLoggedIn = isset($_SESSION['customer_id']);
$customerName = '';
$customerEmail = '';
$customerPhone = '';

if ($customerLoggedIn) {
    require_once('admin/connect.php');
    $stmt = $conn->prepare("SELECT fname, lname, email, contact FROM customer WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['customer_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($customer = $result->fetch_assoc()) {
        $customerName = $customer['fname'] . ' ' . $customer['lname'];
        $customerEmail = $customer['email'];
        $customerPhone = $customer['contact'];
    }
    $stmt->close();
}
?>
<!-- Contact Modal -->
<div id="contactModal" class="fixed inset-0 bg-black bg-opacity-50 z-[100] hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full relative">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Contact Us</h3>
                    <button onclick="closeContactModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="ri-close-line text-2xl"></i>
                    </button>
                </div>
                <form id="contactForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" name="name" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                                   placeholder="Enter your name"
                                   value="<?php echo htmlspecialchars($customerName); ?>"
                                   <?php echo $customerLoggedIn ? 'readonly' : ''; ?>>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="tel" name="phone" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                                   placeholder="Enter your phone number"
                                   value="<?php echo htmlspecialchars($customerPhone); ?>"
                                   <?php echo $customerLoggedIn ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                               placeholder="Enter your email"
                               value="<?php echo htmlspecialchars($customerEmail); ?>"
                               <?php echo $customerLoggedIn ? 'readonly' : ''; ?>>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea name="message" required rows="4" 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                                  placeholder="Enter your message"></textarea>
                    </div>
                    <?php if ($customerLoggedIn): ?>
                    <input type="hidden" name="customer_id" value="<?php echo $_SESSION['customer_id']; ?>">
                    <?php endif; ?>
                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition">
                            Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'success_modal.php'; ?>

<script>
function openContactModal() {
    document.getElementById('contactModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeContactModal() {
    document.getElementById('contactModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.getElementById('contactModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeContactModal();
    }
});

// Handle form submission
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    
    // Disable submit button and show loading state
    submitButton.disabled = true;
    submitButton.textContent = 'Sending...';
    
    // Send form data to server
    fetch('process_contact.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showSuccessModal(data.message);
            closeContactModal();
            this.reset();
        } else {
            // Show error message
            showSuccessModal('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showSuccessModal('An error occurred while sending your message. Please try again.');
    })
    .finally(() => {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    });
});
</script> 