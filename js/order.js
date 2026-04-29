// Global variables to track if event listeners are already attached
let priceCalculationInitialized = false;
let formSubmissionInitialized = false;
let isSubmitting = false;
let currentFormSubmitHandler = null;
let lastSubmitTime = 0;

// Modal functions
function openPlaceOrderModal() {
    // Check if user is logged in
    if (typeof user_logged_in !== 'undefined' && !user_logged_in) {
        // User is not logged in, show a message and redirect to login
        showSuccessModal({
            error: true,
            message: 'Please login to place an order'
        });
        
        // Redirect to login page after 2 seconds
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 2000);
        return;
    }
    
    document.getElementById('placeOrderModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    // Initialize price calculation when modal opens
    calculatePrice();
}

function closePlaceOrderModal() {
    document.getElementById('placeOrderModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    // Reset form
    const form = document.getElementById('placeOrderForm');
    if (form) {
        form.reset();
    }
    const priceDisplay = document.getElementById('priceDisplay');
    if (priceDisplay) {
        priceDisplay.textContent = 'KES 0.00';
    }
}

function openSuccessModal() {
    document.getElementById('successModal').classList.remove('hidden');
    // Auto-close success modal after 5 seconds
    setTimeout(() => {
        closeSuccessModal();
        window.location.reload();
    }, 5000);
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
}

function showSuccessModal(data) {
    console.log('Showing success modal with data:', data); // Debug log
    
    // Get the success modal element
    const successModal = document.getElementById('successModal');
    if (!successModal) {
        console.error('Success modal not found in the DOM');
        return;
    }
    
    // Get required modal elements
    const modalTitle = successModal.querySelector('h3');
    const successMessage = document.getElementById('successMessage');
    const orderDetails = document.getElementById('orderDetails');
    const iconContainer = successModal.querySelector('.w-16.h-16');
    const icon = iconContainer.querySelector('i');
    
    // Ensure all elements exist
    if (!modalTitle || !successMessage || !orderDetails || !iconContainer || !icon) {
        console.error('Required modal elements not found');
        return;
    }
    
    // Reset modal state
    successMessage.classList.add('hidden');
    orderDetails.style.display = 'none';
    iconContainer.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4';
    icon.className = 'text-3xl';
    
    if (typeof data === 'string') {
        // Simple message
        modalTitle.textContent = 'Message';
        successMessage.classList.remove('hidden');
        successMessage.querySelector('p').textContent = data;
        iconContainer.classList.add('bg-green-100');
        icon.classList.add('ri-check-line', 'text-green-500');
    } else if (data.error) {
        // Error message
        modalTitle.textContent = 'Notice';
        successMessage.classList.remove('hidden');
        successMessage.querySelector('p').textContent = data.message;
        iconContainer.classList.add('bg-red-100');
        icon.classList.add('ri-information-line', 'text-red-500');
    } else if (data.success) {
        // Order success with details
        modalTitle.textContent = 'Order Placed Successfully!';
        orderDetails.style.display = 'block';
        iconContainer.classList.add('bg-green-100');
        icon.classList.add('ri-check-line', 'text-green-500');
        
        // Update order details
        if (document.getElementById('successTrackingNumber')) {
            document.getElementById('successTrackingNumber').textContent = data.tracking_number || '';
        }
        if (document.getElementById('successService')) {
            document.getElementById('successService').textContent = data.service_name || '';
        }
        if (document.getElementById('successWeight')) {
            document.getElementById('successWeight').textContent = data.weight ? data.weight + ' kg' : '';
        }
        if (document.getElementById('successPrice')) {
            document.getElementById('successPrice').textContent = data.price ? 'KES ' + data.price : '';
        }
        if (document.getElementById('successPickupDate')) {
            document.getElementById('successPickupDate').textContent = data.pickup_date || '';
        }
        if (document.getElementById('successDeliveryDate')) {
            document.getElementById('successDeliveryDate').textContent = data.delivery_date || '';
        }
    }
    
    // Show the modal
    successModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Auto-close after 3 seconds only for success cases, otherwise after 5 seconds
    const closeDelay = data.success ? 3000 : 5000;
    setTimeout(() => {
        closeSuccessModal();
        if (data.success) {
            window.location.reload();
        }
    }, closeDelay);
}

// Price calculation
function calculatePrice() {
    const serviceSelect = document.querySelector('select[name="service_id"]');
    const weightInput = document.querySelector('input[name="weight"]');
    const priceDisplay = document.getElementById('priceDisplay');
    
    if (serviceSelect && weightInput && priceDisplay) {
        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        const pricePerKg = selectedOption ? parseFloat(selectedOption.dataset.price) || 0 : 0;
        const weight = parseFloat(weightInput.value) || 0;
        const totalPrice = (pricePerKg * weight).toFixed(2);
        priceDisplay.textContent = 'KES ' + totalPrice;
    }
}

// Form submission handler
function handleFormSubmit(e) {
    e.preventDefault();
    
    // Check if user is logged in
    if (typeof user_logged_in !== 'undefined' && !user_logged_in) {
        showSuccessModal({
            error: true,
            message: 'Please login to place an order'
        });
        
        // Redirect to login page after 2 seconds
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 2000);
        return;
    }
    
    // Prevent multiple submissions
    if (isSubmitting) {
        showSuccessModal({
            error: true,
            message: 'Please wait while your previous order is being processed'
        });
        return;
    }
    
    isSubmitting = true;
    // Record last submit time but don't enforce a cooldown
    lastSubmitTime = Date.now();
    
    const form = e.target;
    const formData = new FormData(form);
    
    // Show loading state
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Placing Order...';
    submitButton.disabled = true;
    
    fetch('place_order_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Server response:', data); // Debug log
        
        if (data.success) {
            closePlaceOrderModal();
            // Ensure all required fields are present in the response
            const orderData = {
                success: true,
                tracking_number: data.tracking_number,
                service_name: data.service_name,
                weight: data.weight,
                price: data.price,
                pickup_date: data.pickup_date,
                delivery_date: data.delivery_date
            };
            showSuccessModal(orderData);
        } else {
            console.error('Order placement failed:', data.message);
            showSuccessModal({
                error: true,
                message: data.message || 'An error occurred while placing your order.'
            });
        }
    })
    .catch(error => {
        console.error('Error details:', error);
        showSuccessModal({
            error: true,
            message: 'An error occurred while placing your order. Please check your connection and try again.'
        });
    })
    .finally(() => {
        // Reset button state
        submitButton.textContent = originalText;
        submitButton.disabled = false;
        isSubmitting = false;
    });
}

// Initialize all functionality
function initializeOrderFunctionality() {
    // Initialize price calculation if not already done
    if (!priceCalculationInitialized) {
        const serviceSelect = document.querySelector('select[name="service_id"]');
        const weightInput = document.querySelector('input[name="weight"]');
        
        if (serviceSelect && weightInput) {
            serviceSelect.addEventListener('change', calculatePrice);
            weightInput.addEventListener('input', calculatePrice);
            priceCalculationInitialized = true;
        }
    }

    // Initialize form submission if not already done
    if (!formSubmissionInitialized) {
        const placeOrderForm = document.getElementById('placeOrderForm');
        if (placeOrderForm) {
            // Remove any existing event listeners
            if (currentFormSubmitHandler) {
                placeOrderForm.removeEventListener('submit', currentFormSubmitHandler);
            }
            
            // Add new event listener
            currentFormSubmitHandler = handleFormSubmit;
            placeOrderForm.addEventListener('submit', currentFormSubmitHandler);
            formSubmissionInitialized = true;
        }
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', initializeOrderFunctionality);

// Also initialize when the place order modal is opened
document.addEventListener('click', function(e) {
    if (e.target && e.target.matches('button[onclick*="openPlaceOrderModal"]')) {
        setTimeout(initializeOrderFunctionality, 100);
    }
});

// Calculate price when service or weight changes
document.addEventListener('DOMContentLoaded', function() {
    const serviceSelect = document.querySelector('select[name="service_id"]');
    const weightInput = document.querySelector('input[name="weight"]');
    const priceDisplay = document.getElementById('priceDisplay');

    function calculatePrice() {
        if (!serviceSelect || !weightInput || !priceDisplay) return;
        
        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        const pricePerKg = selectedOption ? parseFloat(selectedOption.dataset.price) || 0 : 0;
        const weight = parseFloat(weightInput.value) || 0;
        const totalPrice = (pricePerKg * weight).toFixed(2);
        priceDisplay.textContent = 'KES ' + totalPrice;
    }

    if (serviceSelect) {
        serviceSelect.addEventListener('change', calculatePrice);
    }
    if (weightInput) {
        weightInput.addEventListener('input', calculatePrice);
    }
}); 