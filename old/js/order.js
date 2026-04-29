// Global variables to track if event listeners are already attached
let priceCalculationInitialized = false;
let formSubmissionInitialized = false;
let isSubmitting = false;
let currentFormSubmitHandler = null;
let lastSubmitTime = 0;

// Modal functions
function openPlaceOrderModal() {
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

function showErrorModal(message) {
    const successModal = document.getElementById('successModal');
    const modalContent = successModal.querySelector('.text-center');
    const orderDetails = modalContent.querySelector('.bg-gray-50');
    
    // Update modal title
    successModal.querySelector('h3').textContent = 'Order Submission Status';
    
    // Show error message
    document.getElementById('successMessage').textContent = message;
    
    // Hide order details for error messages
    if (orderDetails) {
        orderDetails.style.display = 'none';
    }
    
    successModal.classList.remove('hidden');
    
    // Auto-close after 5 seconds
    setTimeout(() => {
        closeSuccessModal();
    }, 5000);
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
    
    const currentTime = Date.now();
    const timeSinceLastSubmit = currentTime - lastSubmitTime;
    
    // Prevent multiple submissions within 5 seconds
    if (timeSinceLastSubmit < 5000) {
        showSuccessModal({
            error: true,
            message: 'Please wait a few seconds before submitting another order'
        });
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
    lastSubmitTime = currentTime;
    
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
    .then(response => response.json())
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
            showSuccessModal({
                error: true,
                message: data.message || 'An error occurred while placing your order.'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showSuccessModal({
            error: true,
            message: 'An error occurred while placing your order.'
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

    // Handle Place Order Form Submission
    const placeOrderForm = document.getElementById('placeOrderForm');
    if (placeOrderForm) {
        placeOrderForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'Placing Order...';
            
            fetch('place_order_process.php', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closePlaceOrderModal();
                    showSuccessModal(data);
                } else {
                    showSuccessModal({
                        error: true,
                        message: data.message || 'An error occurred while placing your order.'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showSuccessModal({
                    error: true,
                    message: 'An error occurred while placing your order.'
                });
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            });
        });
    }
}); 