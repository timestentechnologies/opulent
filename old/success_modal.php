<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 z-[101] hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full relative">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Order Placed Successfully!</h3>
                    <button onclick="closeSuccessModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="ri-close-line text-2xl"></i>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-check-line text-green-500 text-3xl"></i>
                    </div>
                    <div id="successMessage" class="text-center mb-4 hidden">
                        <p class="text-gray-600"></p>
                    </div>
                    <div id="orderDetails" class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Tracking Number:</span>
                            <span id="successTrackingNumber" class="font-medium text-primary"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Service:</span>
                            <span id="successService" class="font-medium"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Weight:</span>
                            <span id="successWeight" class="font-medium"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Price:</span>
                            <span id="successPrice" class="font-medium"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Pickup Date:</span>
                            <span id="successPickupDate" class="font-medium"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Delivery Date:</span>
                            <span id="successDeliveryDate" class="font-medium"></span>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button onclick="closeSuccessModal()" class="px-6 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Success Modal Functions
function showSuccessModal(data) {
    console.log('Showing success modal with data:', data); // Debug log
    
    // Get the success modal elements
    const successModal = document.getElementById('successModal');
    const modalTitle = successModal.querySelector('h3');
    const successMessage = document.getElementById('successMessage');
    const orderDetails = document.getElementById('orderDetails');
    const iconContainer = successModal.querySelector('.w-16.h-16');
    const icon = iconContainer.querySelector('i');
    
    // Reset modal state
    successMessage.classList.add('hidden');
    orderDetails.style.display = 'none';
    iconContainer.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4';
    icon.className = 'text-3xl';
    
    if (typeof data === 'string') {
        // Simple message
        modalTitle.textContent = 'Success';
        successMessage.classList.remove('hidden');
        successMessage.querySelector('p').textContent = data;
        iconContainer.classList.add('bg-green-100');
        icon.classList.add('ri-check-line', 'text-green-500');
    } else if (data.error) {
        // Error message
        modalTitle.textContent = 'Error';
        successMessage.classList.remove('hidden');
        successMessage.querySelector('p').textContent = data.message;
        iconContainer.classList.add('bg-red-100');
        icon.classList.add('ri-close-line', 'text-red-500');
    } else if (data.success) {
        // Order success with details
        modalTitle.textContent = 'Order Placed Successfully!';
        orderDetails.style.display = 'block';
        iconContainer.classList.add('bg-green-100');
        icon.classList.add('ri-check-line', 'text-green-500');
        
        // Update order details
        document.getElementById('successTrackingNumber').textContent = data.tracking_number || '';
        document.getElementById('successService').textContent = data.service_name || '';
        document.getElementById('successWeight').textContent = data.weight ? data.weight + ' kg' : '';
        document.getElementById('successPrice').textContent = data.price ? 'KES ' + data.price : '';
        document.getElementById('successPickupDate').textContent = data.pickup_date || '';
        document.getElementById('successDeliveryDate').textContent = data.delivery_date || '';
    }
    
    // Show the modal
    successModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Auto-close after 3 seconds and refresh the page for successful orders
    setTimeout(() => {
        closeSuccessModal();
        if (data.success) {
            window.location.reload();
        }
    }, 3000);
}

function closeSuccessModal() {
    const successModal = document.getElementById('successModal');
    successModal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.getElementById('successModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSuccessModal();
    }
});
</script> 