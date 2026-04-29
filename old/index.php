<?php
session_start();
require_once('admin/connect.php');

// Check connection before query
$conn = checkConnection($conn);

$sql_header_logo = "select * from manage_website"; 
$result_header_logo = $conn->query($sql_header_logo);
if (!$result_header_logo) {
    die("Query failed: " . $conn->error);
}
$row_header_logo = mysqli_fetch_array($result_header_logo);

// Include the success modal first
include 'success_modal.php';

// Include the contact modal
include 'contact_modal.php';

// Include the common order modal template
include 'order_modal.php';

// Include the profile modal
include 'profile_modal.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Opulent Laundry Services</title>
<link rel="icon" type="image/x-icon" href="images/favicon.png">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.png">
<script src="https://cdn.tailwindcss.com/3.4.16"></script>
<script>
tailwind.config={
    theme:{
        extend:{
            colors:{
                primary:'#0B5FB0',
                secondary:'#10B981'
            },
            borderRadius:{
                'none':'0px',
                'sm':'4px',
                DEFAULT:'8px',
                'md':'12px',
                'lg':'16px',
                'xl':'20px',
                '2xl':'24px',
                '3xl':'32px',
                'full':'9999px',
                'button':'8px'
            }
        }
    }
}
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.16/dist/tailwind.min.css">
<script src="js/order.js"></script>
<style>
:where([class^="ri-"])::before { content: "\f3c2"; }
body {
    font-family: 'Inter', sans-serif;
}
.custom-checkbox {
    appearance: none;
    -webkit-appearance: none;
    width: 20px;
    height: 20px;
    border: 2px solid #d1d5db;
    border-radius: 4px;
    outline: none;
    cursor: pointer;
    position: relative;
}
.custom-checkbox:checked {
    background-color: #4F46E5;
    border-color: #4F46E5;
}
.custom-checkbox:checked::after {
    content: "";
    position: absolute;
    left: 6px;
    top: 2px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}
.custom-switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 24px;
}
.custom-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.switch-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #e5e7eb;
    transition: .4s;
    border-radius: 24px;
}
.switch-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}
input:checked + .switch-slider {
    background-color: #4F46E5;
}
input:checked + .switch-slider:before {
    transform: translateX(24px);
}
.custom-range {
    -webkit-appearance: none;
    width: 100%;
    height: 6px;
    border-radius: 5px;
    background: #e5e7eb;
    outline: none;
}
.custom-range::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #4F46E5;
    cursor: pointer;
}
.custom-range::-moz-range-thumb {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #4F46E5;
    cursor: pointer;
    border: none;
}
.custom-radio {
    appearance: none;
    -webkit-appearance: none;
    width: 20px;
    height: 20px;
    border: 2px solid #d1d5db;
    border-radius: 50%;
    outline: none;
    cursor: pointer;
    position: relative;
}
.custom-radio:checked {
    border-color: #4F46E5;
}
.custom-radio:checked::after {
    content: "";
    position: absolute;
    left: 3px;
    top: 3px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #4F46E5;
}
.hover\:bg-primary:hover {
    background-color: #0B5FB0 !important;
}
.bg-primary {
    background-color: #0B5FB0 !important;
}
.text-primary {
    color: #0B5FB0 !important;
}
.hover\:text-primary:hover {
    color: #0B5FB0 !important;
}
.focus\:ring-primary:focus {
    --tw-ring-color: #0B5FB0 !important;
}
.focus\:border-primary:focus {
    border-color: #0B5FB0 !important;
}

.service-carousel::-webkit-scrollbar {
            height: 6px;
        }
        .service-carousel::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .service-carousel::-webkit-scrollbar-thumb {
            background: #0e57a2;
            border-radius: 10px;
        }
        .service-carousel::-webkit-scrollbar-thumb:hover {
            background: #38b6ff;
        }
        .custom-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        .custom-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #5B21B6;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
</style>
<style>
.preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #fff 0%, #e3fbff 100%);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.5s, visibility 0.5s;
}

.preloader.fade-out {
    opacity: 0;
    visibility: hidden;
}

.loader-text {
    font-size: 5rem;
    font-family: 'Pacifico', cursive;
    display: flex;
    gap: 0.2em;
    margin-bottom: 1rem;
}

.loader-subtitle {
    font-family: 'Inter', sans-serif;
    color: #38b6ff;
    font-size: 1.2rem;
    opacity: 0;
    animation: fadeIn 0.5s forwards 1s;
}

.loader-text span {
    opacity: 0;
    display: inline-block;
    color: #0e57a2;
    text-shadow: 3px 3px 6px rgba(14, 87, 162, 0.2);
    position: relative;
    transform-origin: center;
}

.loader-text span:nth-child(2n) {
    color: #38b6ff;
}

.loader-text span::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 4px;
    background: currentColor;
    bottom: 0;
    left: 0;
    transform: scaleX(0);
    transform-origin: left;
    animation: underline 0.5s forwards;
    animation-delay: inherit;
    border-radius: 2px;
}

@keyframes revealText {
    0% {
        opacity: 0;
        transform: translateY(50px) rotate(-15deg) scale(0.8);
    }
    100% {
        opacity: 1;
        transform: translateY(0) rotate(0deg) scale(1);
    }
}

@keyframes underline {
    0% {
        transform: scaleX(0);
    }
    100% {
        transform: scaleX(1);
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-15px) rotate(3deg);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
</head>
<body class="bg-white min-h-screen">
<!-- Preloader -->
<div class="preloader">
    <div class="loader-text">
        <span style="animation: revealText 0.5s forwards 0.1s, float 3s ease-in-out infinite 1s">O</span>
        <span style="animation: revealText 0.5s forwards 0.2s, float 3s ease-in-out infinite 1.1s">p</span>
        <span style="animation: revealText 0.5s forwards 0.3s, float 3s ease-in-out infinite 1.2s">u</span>
        <span style="animation: revealText 0.5s forwards 0.4s, float 3s ease-in-out infinite 1.3s">l</span>
        <span style="animation: revealText 0.5s forwards 0.5s, float 3s ease-in-out infinite 1.4s">e</span>
        <span style="animation: revealText 0.5s forwards 0.6s, float 3s ease-in-out infinite 1.5s">n</span>
        <span style="animation: revealText 0.5s forwards 0.7s, float 3s ease-in-out infinite 1.6s">t</span>
    </div>
    <div class="loader-subtitle">Professional Laundry Services</div>
</div>

<!-- Track Order Modal -->
<div id="trackOrderModal" class="fixed inset-0 bg-black bg-opacity-50 z-[100] hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full relative">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Track Your Order</h3>
                    <button onclick="closeTrackOrderModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="ri-close-line text-2xl"></i>
                    </button>
                </div>
                <form id="trackOrderForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tracking Number</label>
                        <input type="text" name="tracking_number" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                               placeholder="Enter your tracking number">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                               placeholder="Enter your email">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition">
                            Track Order
                        </button>
                    </div>
                </form>
                <!-- Results Section -->
                <div id="trackOrderResults" class="mt-6 hidden">
                    <div class="border-t pt-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Order Details</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Status:</span>
                                <span id="orderStatus" class="px-3 py-1 rounded-full text-sm font-medium"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Service:</span>
                                <span id="orderService" class="font-medium"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Weight:</span>
                                <span id="orderWeight" class="font-medium"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Pickup Date:</span>
                                <span id="orderPickup" class="font-medium"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Delivery Date:</span>
                                <span id="orderDelivery" class="font-medium"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Price:</span>
                                <span id="orderPrice" class="font-medium"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                                    <a href="#" class="font-medium text-primary hover:text-indigo-500">Forgot password?</a>
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


<!-- Header -->
<?php include 'includes/navigation.php'; ?>


<!-- Place Order Modal -->
<div id="placeOrderModal" class="fixed inset-0 bg-black bg-opacity-50 z-[100] hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full relative">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Place Your Order</h3>
                    <button onclick="closePlaceOrderModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="ri-close-line text-2xl"></i>
                    </button>
                </div>
                <form id="placeOrderForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Service Type</label>
                            <select name="service_id" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">Select a service</option>
                                <?php
                                $sql_services = "SELECT * FROM service";
                                $result_services = $conn->query($sql_services);
                                while ($service = $result_services->fetch_assoc()): ?>
                                    <option value="<?php echo $service['id']; ?>">
                                        <?php echo htmlspecialchars($service['sname']); ?> - KES <?php echo number_format($service['prize'], 2); ?>/kg
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                            <input type="number" name="weight" step="0.1" min="0.1" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pickup Date</label>
                            <input type="date" name="pickup_date" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Date</label>
                            <input type="date" name="delivery_date" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="closePlaceOrderModal()" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-6 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition">Place Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Hero Section -->
<section class="relative overflow-hidden" style="background-image: url('https://readdy.ai/api/search-image?query=professional%2520black%2520woman%2520smiling%2520with%2520neatly%2520folded%2520clean%2520laundry%2520in%2520a%2520modern%2520bright%2520room%2520with%2520large%2520windows%2520and%2520natural%2520light%2520streaming%2520in%252C%2520creating%2520a%2520fresh%2520and%2520clean%2520atmosphere.%2520The%2520left%2520side%2520of%2520the%2520image%2520has%2520a%2520clean%2520white%2520background%2520that%2520gradually%2520transitions%2520to%2520the%2520scene&width=1200&height=600&seq=1&orientation=landscape'); background-size: cover; background-position: center;">
<div class="absolute inset-0 bg-gradient-to-r from-white via-white/90 to-transparent"></div>
<div class="container mx-auto px-4 py-20 md:py-32 relative">
<div class="max-w-2xl">
<h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Professional <span style="color: #0e57a2">Laundry Services</span> for Your Busy Life</h1>
<p class="text-lg text-gray-700 mb-8">We pick up, clean, and deliver your laundry with care. Save time and enjoy freshly cleaned clothes without the hassle.</p>
<div class="flex flex-col sm:flex-row gap-4">
    <?php if (isset($_SESSION['customer_id'])): ?>
        <button onclick="openPlaceOrderModal()" class="bg-primary text-white px-8 py-3 !rounded-button whitespace-nowrap hover:bg-opacity-90 transition text-lg font-medium">Place Order</button>
    <?php else: ?>
        <button onclick="openLoginModal()" class="bg-primary text-white px-8 py-3 !rounded-button whitespace-nowrap hover:bg-opacity-90 transition text-lg font-medium">Place Order</button>
    <?php endif; ?>
    <button type="button" onclick="openTrackOrderModal()" class="bg-white text-primary border-2 border-primary px-8 py-3 !rounded-button whitespace-nowrap hover:bg-gray-50 transition text-lg font-medium">Track Order</button>
</div>
</div>
</div>
</section>
 <!-- Services Section -->
 <section id="services" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Our Premium Services</h2>
                <p class="text-gray-700 max-w-2xl mx-auto">From everyday wear to linens, towels, and delicate fabrics, we treat every piece like it matters—because it does.</p>
            </div>
            
            <div class="service-carousel overflow-x-auto pb-6">
                <div class="flex space-x-6 min-w-max">
                    <!-- Service Card 1 -->
                    <div class="service-card bg-white rounded-lg shadow-lg overflow-hidden w-72">
                        <div class="h-48 overflow-hidden">
                            <img src="https://readdy.ai/api/search-image?query=neatly%2520folded%2520stack%2520of%2520clean%2520colorful%2520clothes%2520on%2520a%2520white%2520surface%2520with%2520a%2520minimalist%2520background.%2520The%2520clothes%2520look%2520fresh%2520and%2520perfectly%2520folded%2520with%2520precise%2520edges.%2520The%2520image%2520has%2520excellent%2520lighting%2520highlighting%2520the%2520cleanliness%2520and%2520organization%2520of%2520the%2520laundry&width=300&height=200&seq=5&orientation=landscape" alt="Wash, Dry & Fold" class="w-full h-full object-cover object-top">
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Wash, Dry & Fold</h3>
                            <p class="text-gray-700 mb-4">Effortless care for your daily laundry. Neatly folded and ready to wear.</p>
                        </div>
                    </div>
                    
                    <!-- Service Card 2 -->
                    <div class="service-card bg-white rounded-lg shadow-lg overflow-hidden w-72">
                        <div class="h-48 overflow-hidden">
                            <img src="https://readdy.ai/api/search-image?query=perfectly%2520ironed%2520business%2520shirts%2520and%2520pants%2520hanging%2520on%2520a%2520clothing%2520rack%2520with%2520a%2520clean%2520white%2520background.%2520The%2520clothes%2520look%2520crisp%2520and%2520professional%2520with%2520sharp%2520creases%2520and%2520no%2520wrinkles.%2520The%2520lighting%2520emphasizes%2520the%2520perfect%2520pressing%2520and%2520professional%2520finish&width=300&height=200&seq=6&orientation=landscape" alt="Wash, Dry, Iron & Fold" class="w-full h-full object-cover object-top">
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Wash, Dry, Iron & Fold</h3>
                            <p class="text-gray-700 mb-4">Perfectly pressed, fresh-smelling clothes delivered to your door.</p>
                        </div>
                    </div>
                    
                    <!-- Service Card 3 -->
                    <div class="service-card bg-white rounded-lg shadow-lg overflow-hidden w-72">
                        <div class="h-48 overflow-hidden">
                            <img src="https://readdy.ai/api/search-image?query=professional%2520black%2520person%2520ironing%2520a%2520dress%2520shirt%2520with%2520precision%2520in%2520a%2520bright%2520clean%2520room.%2520The%2520shirt%2520is%2520white%2520and%2520the%2520ironing%2520board%2520is%2520set%2520up%2520with%2520other%2520garments%2520waiting%2520to%2520be%2520pressed.%2520The%2520scene%2520conveys%2520attention%2520to%2520detail%2520and%2520professional%2520care&width=300&height=200&seq=7&orientation=landscape" alt="Ironing Only" class="w-full h-full object-cover object-top">
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Ironing Only</h3>
                            <p class="text-gray-700 mb-4">Crisp, professional finish for your already-washed garments.</p>
                        </div>
                    </div>
                    
                    <!-- Service Card 4 -->
                    <div class="service-card bg-white rounded-lg shadow-lg overflow-hidden w-72">
                        <div class="h-48 overflow-hidden">
                            <img src="https://readdy.ai/api/search-image?query=black%2520delivery%2520person%2520in%2520uniform%2520handing%2520over%2520a%2520package%2520of%2520neatly%2520wrapped%2520laundry%2520to%2520a%2520smiling%2520customer%2520at%2520their%2520doorstep.%2520The%2520package%2520is%2520branded%2520with%2520a%2520laundry%2520service%2520logo%2520and%2520the%2520scene%2520conveys%2520speed%2520and%2520efficiency%2520with%2520a%2520delivery%2520vehicle%2520visible%2520in%2520the%2520background&width=300&height=200&seq=8&orientation=landscape" alt="Express Same-Day Service" class="w-full h-full object-cover object-top">
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Express Same-Day</h3>
                            <p class="text-gray-700 mb-4">In a rush? We've got your back—same-day cleaning & delivery.</p>
                        </div>
                    </div>
                    
                    <!-- Service Card 5 -->
                    <div class="service-card bg-white rounded-lg shadow-lg overflow-hidden w-72">
                        <div class="h-48 overflow-hidden">
                            <img src="https://readdy.ai/api/search-image?query=eco-friendly%2520laundry%2520setup%2520with%2520biodegradable%2520detergents%252C%2520energy-efficient%2520washing%2520machine%252C%2520and%2520natural%2520cleaning%2520products.%2520The%2520scene%2520includes%2520green%2520plants%252C%2520bamboo%2520accessories%252C%2520and%2520sustainable%2520packaging%2520with%2520eco-labels.%2520The%2520image%2520has%2520a%2520bright%252C%2520clean%2520aesthetic%2520with%2520natural%2520lighting&width=300&height=200&seq=9&orientation=landscape" alt="Eco-Friendly Wash" class="w-full h-full object-cover object-top">
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Eco-Friendly Wash</h3>
                            <p class="text-gray-700 mb-4">Sustainable cleaning with biodegradable detergents & energy-efficient processes.</p>
                        </div>
                </div>
            </div>
        </div>
    </section>

<!-- Subscription Plans -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="heading text-3xl font-bold text-gray-900 mb-4">Monthly Subscription Plans</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">Save time and money with our convenient subscription plans. Regular laundry service at discounted rates.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <?php
            // Fetch subscription plans from database
            $sql = "SELECT * FROM subscription_plans ORDER BY price ASC";
            $result = $conn->query($sql);
            
            $count = 0;
            while($plan = $result->fetch_assoc()) {
                $count++;
                $isPopular = ($count == 2); // Middle plan is popular
                ?>
                
                <div class="bg-white rounded-xl <?php echo $isPopular ? 'shadow-md border-2 border-primary transform -translate-y-4' : 'shadow-sm border border-gray-100'; ?> overflow-hidden hover:shadow-md transition-all duration-300 <?php echo $isPopular ? 'relative' : ''; ?>">
                    <?php if($isPopular) { ?>
                        <div class="absolute top-0 right-0 bg-primary text-white px-4 py-1 text-sm font-medium">Popular</div>
                    <?php } ?>
                    <div class="<?php echo $isPopular ? 'bg-indigo-100' : 'bg-indigo-50'; ?> p-6 text-center">
                        <h3 class="heading text-xl font-bold text-gray-900"><?php echo htmlspecialchars($plan['name']); ?></h3>
                        <div class="mt-4">
                            <span class="text-4xl font-bold text-gray-900">Ksh <?php echo number_format($plan['price']); ?></span>
                            <span class="text-gray-600">/month</span>
                        </div>
                        <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($plan['description']); ?></p>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-3">
                            <?php if($count == 1) { // Student Plan Features ?>
                                <li class="flex items-center">
                                    <i class="ri-check-line text-primary mr-2"></i>
                                    <span class="text-gray-700">Up to 30kg</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="ri-check-line text-primary mr-2"></i>
                                    <span class="text-gray-700">Wash, Dry & Fold</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="ri-check-line text-primary mr-2"></i>
                                    <span class="text-gray-700">Free pickup & delivery Weekly</span>
                                </li>
                                <li class="flex items-center text-gray-400">
                                    <i class="ri-close-line mr-2"></i>
                                    <span>Ironing service</span>
                                </li>
                                <li class="flex items-center text-gray-400">
                                    <i class="ri-close-line mr-2"></i>
                                    <span>Stain treatment</span>
                                </li>
                            <?php } elseif($count == 2) { // Bachelor Plan Features ?>
                                <li class="flex items-center">
                                    <i class="ri-check-line text-primary mr-2"></i>
                                    <span class="text-gray-700">Up to 50kg</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="ri-check-line text-primary mr-2"></i>
                                    <span class="text-gray-700">Wash, Dry & Fold</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="ri-check-line text-primary mr-2"></i>
                                    <span class="text-gray-700">Free pickup & delivery Weekly</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="ri-check-line text-primary mr-2"></i>
                                    <span class="text-gray-700">Basic ironing included</span>
                                </li>
                                <li class="flex items-center text-gray-400">
                                    <i class="ri-close-line mr-2"></i>
                                    <span class="text-gray-700">Stain treatment</span>
                                </li>
                            <?php } else { // Family Plan Features ?>
                                <li class="flex items-center">
                                    <i class="ri-check-line text-primary mr-2"></i>
                                    <span class="text-gray-700">Up to 100kg</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="ri-check-line text-primary mr-2"></i>
                                    <span class="text-gray-700">Wash, Dry, Iron & Fold</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="ri-check-line text-primary mr-2"></i>
                                    <span class="text-gray-700">Priority pickup & delivery</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="ri-check-line text-primary mr-2"></i>
                                    <span class="text-gray-700">Full ironing service</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="ri-check-line text-primary mr-2"></i>
                                    <span class="text-gray-700">Beddings included</span>
                                </li>
                            <?php } ?>
                        </ul>
                        <button 
                            class="subscribe-plan-btn w-full mt-6 py-2 <?php echo $isPopular ? 'bg-primary text-white hover:bg-indigo-600' : 'bg-white border border-primary text-primary hover:bg-indigo-50'; ?> font-medium rounded-md transition-colors whitespace-nowrap"
                            data-plan-id="<?php echo $plan['id']; ?>"
                            data-plan-name="<?php echo htmlspecialchars($plan['name']); ?>"
                            data-plan-price="<?php echo $plan['price']; ?>"
                            data-plan-duration="<?php echo $plan['duration']; ?>"
                        >Choose Plan</button>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</section>

<!-- Subscription Modal -->
<div id="subscriptionModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Confirm Subscription
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500" id="subscriptionDetails"></p>
                        <div class="mt-4">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                    <input type="date" id="startDate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">End Date (Auto-calculated)</label>
                                    <input type="date" id="endDate" class="mt-1 block w-full bg-gray-50 border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirmSubscription" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm">
                    Confirm Subscription
                </button>
                <button type="button" onclick="closeSubscriptionModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Subscription Success Modal -->
<div id="subscriptionSuccessModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6">
            <div>
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Subscription Successful!
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Your subscription has been confirmed. Thank you for choosing our service!
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-6">
                <button type="button" onclick="closeSubscriptionSuccessModal()" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Alert Modal -->
<div id="alertModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="alertModalTitle"></h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500" id="alertModalMessage"></p>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button type="button" onclick="closeAlertModal()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const subscribeButtons = document.querySelectorAll('.subscribe-plan-btn');
    subscribeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            // Check if user is logged in using session
            if (!document.cookie.includes('PHPSESSID')) {
                showAlert('Login Required', 'Please log in to subscribe to a plan');
                setTimeout(() => {
                    closeModal('alertModal');
                    openModal('loginModal');
                }, 2000);
                return;
            }

            const planId = this.dataset.planId;
            const planName = this.dataset.planName;
            const planPrice = this.dataset.planPrice;
            const planDuration = this.dataset.planDuration;

            // Set today as minimum date for start date
            const today = new Date().toISOString().split('T')[0];
            const startDateInput = document.getElementById('startDate');
            if (startDateInput) {
                startDateInput.min = today;
                startDateInput.value = today;
                updateEndDate(today, planDuration);
            }

            // Update subscription details
            const detailsElement = document.getElementById('subscriptionDetails');
            if (detailsElement) {
                detailsElement.textContent = `You are about to subscribe to the ${planName} plan for KES ${planPrice} per month.`;
            }

            // Store plan details for submission
            const confirmBtn = document.getElementById('confirmSubscription');
            if (confirmBtn) {
                confirmBtn.dataset.planId = planId;
                confirmBtn.dataset.planDuration = planDuration;
            }

            // Show subscription modal
            openModal('subscriptionModal');
        });
    });
});

// Add start date change handler
document.getElementById('startDate').addEventListener('change', function() {
    const planDuration = document.getElementById('confirmSubscription').dataset.planDuration;
    updateEndDate(this.value, planDuration);
});

// Add confirm subscription handler
document.getElementById('confirmSubscription').addEventListener('click', function() {
    const planId = this.dataset.planId;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    // Send subscription data to server
    fetch('process_subscription.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `plan_id=${encodeURIComponent(planId)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close subscription modal
            closeSubscriptionModal();
            // Show success modal
            document.getElementById('subscriptionSuccessModal').classList.remove('hidden');
        } else {
            // Show alert modal with error message
            document.getElementById('alertModalTitle').textContent = 'Error';
            document.getElementById('alertModalMessage').textContent = data.message || 'An error occurred. Please try again.';
            document.getElementById('alertModal').classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});

// Function to update end date based on start date and plan duration
function updateEndDate(startDate, monthsDuration) {
    const date = new Date(startDate);
    date.setMonth(date.getMonth() + parseInt(monthsDuration));
    document.getElementById('endDate').value = date.toISOString().split('T')[0];
}

// Function to close subscription modal
function closeSubscriptionModal() {
    document.getElementById('subscriptionModal').classList.add('hidden');
}

// Function to close subscription success modal
function closeSubscriptionSuccessModal() {
    document.getElementById('subscriptionSuccessModal').classList.add('hidden');
}

// Sign Up Form Handler
const signUpForm = document.getElementById('signUpForm');
if (signUpForm) {
    signUpForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('signup_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Success', 'Account created successfully! Please login.');
                setTimeout(() => {
                    openModal('loginModal');
                }, 2000);
            } else {
                showAlert('Error', data.message || 'Failed to create account. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error', 'An error occurred. Please try again.');
        });
    });
}

// Alert Modal Functions
window.showAlert = function(title, message) {
    document.getElementById('alertModalTitle').textContent = title;
    document.getElementById('alertModalMessage').textContent = message;
    openModal('alertModal');
}

window.closeAlertModal = function() {
    closeModal('alertModal');
}

// Update subscription button click handler to use alert modal
const subscribeButtons = document.querySelectorAll('.subscribe-plan-btn');
if (subscribeButtons.length > 0) {
    subscribeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Check if user is logged in first
           // Check if user is logged in using session
            if (!document.cookie.includes('PHPSESSID')) {
                showAlert('Login Required', 'Please log in to subscribe to a plan');
                setTimeout(() => {
                    closeModal('alertModal');
                    openModal('loginModal');
                }, 2000);
                return;
            }
                
                const planId = this.dataset.planId;
                const planName = this.dataset.planName;
                const planPrice = this.dataset.planPrice;
                const planDuration = this.dataset.planDuration;
                
                // Set today as minimum date for start date
                const today = new Date().toISOString().split('T')[0];
                const startDateInput = document.getElementById('startDate');
                if (startDateInput) {
                    startDateInput.min = today;
                    startDateInput.value = today;
                    updateEndDate(today, planDuration);
                }
                
                // Update subscription details
                const detailsElement = document.getElementById('subscriptionDetails');
                if (detailsElement) {
                    detailsElement.textContent = `You are about to subscribe to the ${planName} plan for KES ${planPrice} per month.`;
                }
                
                // Store plan details for submission
                const confirmBtn = document.getElementById('confirmSubscription');
                if (confirmBtn) {
                    confirmBtn.dataset.planId = planId;
                    confirmBtn.dataset.planDuration = planDuration;
                }
                
                // Show subscription modal
                openModal('subscriptionModal');
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error', 'An error occurred. Please try again.');
            });
        });
}

// Newsletter form submission
const newsletterForm = document.getElementById('newsletterForm');
if (newsletterForm) {
    newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form elements
        const emailInput = this.querySelector('input[name="email"]');
        const submitButton = this.querySelector('button[type="submit"]');
        const subscribeText = submitButton.querySelector('.subscribe-text');
        const loadingText = submitButton.querySelector('.loading-text');
        const messageDiv = document.getElementById('newsletterMessage');
        const messagePara = messageDiv.querySelector('p');
        
        // Show loading state
        subscribeText.classList.add('hidden');
        loadingText.classList.remove('hidden');
        submitButton.disabled = true;
        
        // Send form data
        fetch('process_newsletter.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `email=${encodeURIComponent(emailInput.value)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                messagePara.textContent = data.message;
                messagePara.classList.remove('text-red-600');
                messagePara.classList.add('text-green-600');
                messageDiv.classList.remove('hidden');
                
                // Clear form
                emailInput.value = '';
                
                // Show success modal
                document.getElementById('newsletterSuccessModal').classList.remove('hidden');
            } else {
                // Show error message
                messagePara.textContent = data.message;
                messagePara.classList.remove('text-green-600');
                messagePara.classList.add('text-red-600');
                messageDiv.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Show error message
            messagePara.textContent = 'An error occurred. Please try again.';
            messagePara.classList.remove('text-green-600');
            messagePara.classList.add('text-red-600');
            messageDiv.classList.remove('hidden');
        })
        .finally(() => {
            // Reset button state
            subscribeText.classList.remove('hidden');
            loadingText.classList.add('hidden');
            submitButton.disabled = false;
        });
    });
}

// Function to close newsletter success modal
window.closeNewsletterModal = function() {
    document.getElementById('newsletterSuccessModal').classList.add('hidden');
}
</script>

<!-- How It Works -->
<section class="py-16 md:py-24">
<div class="container mx-auto px-4">
<div class="text-center mb-16">
<h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">How It Works</h2>
<!-- <h1 class="font-['Pacifico'] text-5xl text-#0e57a2 mb-2">Opulent Laundry</h1> -->
<p class="text-lg text-gray-600 max-w-3xl mx-auto">Clean clothes in three simple steps. No hassle, no waiting.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-12">
<!-- Step 1 -->
<div class="text-center p-8 bg-white rounded-xl shadow-lg border-2 border-primary/10 hover:shadow-xl transition-shadow duration-300">
<div class="w-20 h-20 flex items-center justify-center bg-primary/10 rounded-full mx-auto mb-6">
<i class="ri-calendar-check-line text-3xl text-primary"></i>
</div>
<h3 class="text-xl font-bold text-gray-900 mb-3">Schedule Pickup</h3>
<p class="text-gray-600">Book a convenient time for us to collect your laundry through our website or app.</p>
</div>
<!-- Step 2 -->
<div class="text-center p-8 bg-white rounded-xl shadow-lg border-2 border-primary/10 hover:shadow-xl transition-shadow duration-300">
<div class="w-20 h-20 flex items-center justify-center bg-primary/10 rounded-full mx-auto mb-6">
<i class="ri-t-shirt-line text-3xl text-primary"></i>
</div>
<h3 class="text-xl font-bold text-gray-900 mb-3">We Clean</h3>
<p class="text-gray-600">Our experts clean your clothes using premium products and professional equipment.</p>
</div>
<!-- Step 3 -->
<div class="text-center p-8 bg-white rounded-xl shadow-lg border-2 border-primary/10 hover:shadow-xl transition-shadow duration-300">
<div class="w-20 h-20 flex items-center justify-center bg-primary/10 rounded-full mx-auto mb-6">
<i class="ri-truck-line text-3xl text-primary"></i>
</div>
<h3 class="text-xl font-bold text-gray-900 mb-3">Delivery</h3>
<p class="text-gray-600">We deliver your fresh, clean clothes back to your doorstep at your preferred time.</p>
</div>
</div>
</div>
</section>

<!-- Track Orders Section -->
<section class="py-16 md:py-24 bg-gray-50">
<div class="container mx-auto px-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <!-- Left side with images -->
        <div class="relative">
            <div class="relative w-full max-w-md mx-auto">
                <!-- Phones Image -->
                <img src="images/tracking-phones.png" alt="Order Tracking App Interface" class="w-full h-auto">
                <!-- Delivery Van Image -->
                <div class="absolute -bottom-12 -right-12 w-48 h-48 hidden md:block">
                    <img src="images/opulnet.png" alt="Delivery Van" class="w-full h-full object-contain">
                </div>
                <!-- Decorative Elements -->
                <div class="absolute -top-4 -left-4 w-20 h-20 bg-primary/10 rounded-full hidden md:block"></div>
                <div class="absolute -bottom-4 right-20 w-12 h-12 bg-secondary/10 rounded-full hidden md:block"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-gray-50 to-transparent opacity-20"></div>
            </div>
        </div>
        
        <!-- Right side with content -->
        <div class="space-y-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900">Track your Orders</h2>
            
            <!-- Every step of the way -->
            <div class="space-y-4">
                <h3 class="text-xl font-bold text-gray-900">Every step of the way</h3>
                <p class="text-lg text-gray-600">
                    You'll be notified as your laundry, wash and fold, wash and press, and dry cleaning orders are picked-up, being cleaned, and ready for scheduling delivery.
                </p>
            </div>
            
            <!-- We have history -->
            <div class="space-y-4">
                <h3 class="text-xl font-bold text-gray-900">We have history</h3>
                <p class="text-lg text-gray-600">
                    All of your orders are conveniently located in the app showing a list of each garment cleaned, 
                    <a href="pricing.php" class="text-primary hover:underline">all prices</a>, and your order total.
                </p>
            </div>
            
            <!-- Track Order Button -->
            <div class="pt-4">
                <button onclick="openTrackOrderModal()" class="bg-primary text-white px-8 py-3 !rounded-button whitespace-nowrap hover:bg-opacity-90 transition text-lg font-medium">
                    Track Your Order
                </button>
            </div>
        </div>
    </div>
</div>
</section>


<!-- Testimonials Section -->
<section class="py-16 md:py-24">
<div class="container mx-auto px-4">
<div class="text-center mb-16">
<h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">What Our Customers Say</h2>
<p class="text-lg text-gray-600 max-w-3xl mx-auto">Don't just take our word for it. Here's what our satisfied customers have to say about our services.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
<!-- Testimonial 1 -->
<div class="bg-white p-8 rounded shadow-lg">
<div class="flex text-yellow-400 mb-4">
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
</div>
<p class="text-gray-700 mb-6">"Their attention to detail is remarkable. My shirts have never looked better, and the convenience of pickup and delivery makes my busy life so much easier."</p>
<div class="flex items-center">
<div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center mr-4">
<i class="ri-user-line text-gray-500"></i>
</div>
<div>
<h4 class="font-semibold text-gray-900">Geofrey Mokaya</h4>
<p class="text-sm text-gray-500">Marketing Director</p>
</div>
</div>
</div>
<!-- Testimonial 2 -->
<div class="bg-white p-8 rounded shadow-lg">
<div class="flex text-yellow-400 mb-4">
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
</div>
<p class="text-gray-700 mb-6">"As a working mother of three, their weekly subscription plan has been a lifesaver. The quality is consistent, and they handle my children's clothes with such care."</p>
<div class="flex items-center">
<div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center mr-4">
<i class="ri-user-line text-gray-500"></i>
</div>
<div>
<h4 class="font-semibold text-gray-900">Samantha Wilson</h4>
<p class="text-sm text-gray-500">Healthcare Professional</p>
</div>
</div>
</div>
<!-- Testimonial 3 -->
<div class="bg-white p-8 rounded shadow-lg">
<div class="flex text-yellow-400 mb-4">
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
<i class="ri-star-fill"></i>
</div>
<p class="text-gray-700 mb-6">"I had a wine stain on my favorite white shirt that I thought was ruined forever. Their stain removal service worked miracles. I'm a customer for life!"</p>
<div class="flex items-center">
<div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center mr-4">
<i class="ri-user-line text-gray-500"></i>
</div>
<div>
<h4 class="font-semibold text-gray-900">Alex Mwangi</h4>
<p class="text-sm text-gray-500">Software Engineer</p>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- CTA Section -->
<section class="py-16 md:py-24 bg-primary">
<div class="container mx-auto px-4 text-center">
<h2 class="text-3xl md:text-4xl font-bold text-white mb-6">Ready to Save Time on Laundry?</h2>
<p class="text-xl text-white/90 mb-8 max-w-3xl mx-auto">Join thousands of satisfied customers who have reclaimed their time by choosing Timesten Laundry Services.</p>
<div class="flex flex-col sm:flex-row gap-4 justify-center">
<a href="sign_up.php"><button class="bg-white text-primary px-8 py-3 !rounded-button whitespace-nowrap hover:bg-gray-50 transition text-lg font-medium">Get Started Today</button></a>
<a href="pricing.php"><button class="bg-transparent text-white border-2 border-white px-8 py-3 !rounded-button whitespace-nowrap hover:bg-white/10 transition text-lg font-medium">View Pricing</button></a>
</div>
</div>
</div>
</section>
<!-- Contact Section -->
<section class="py-16 md:py-24">
<div class="container mx-auto px-8">
<div class="grid grid-cols-1 md:grid-cols-2 gap-12">
<div>
<h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">Contact Us</h2>
<p class="text-lg text-gray-600 mb-8">Have questions or need assistance? Our customer service team is here to help. Fill out the form or use our contact information below.</p>
<div class="mb-8">
<div class="flex items-start mb-4">
<div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-4">
<i class="ri-map-pin-line text-primary"></i>
</div>
<div>
<h4 class="font-semibold text-gray-900 mb-1">Address</h4>
    <p class="text-gray-600">Mlolongo Phase 3 near Turning point church.<br>Nairobi, Kenya</span>
</div>
</div>
<div class="flex items-start mb-4">
<div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-4">
<i class="ri-phone-line text-primary"></i>
</div>
<div>
<h4 class="font-semibold text-gray-900 mb-1">Phone</h4>
<p class="text-gray-600">0745812730</p>
</div>
</div>
<div class="flex items-start">
<div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-4">
<i class="ri-mail-line text-primary"></i>
</div>
<div>
<h4 class="font-semibold text-gray-900 mb-1">Email</h4>
<p class="text-gray-600">opulentlaundry1@gmail.com</p>
</div>
</div>
</div>
<div>
<h4 class="font-semibold text-gray-900 mb-4">Business Hours</h4>
<ul class="space-y-2 text-gray-600">
<li>Monday - Friday: 7:00 AM - 9:00 PM</li>
<li>Saturday: 8:00 AM - 7:00 PM</li>
<li>Sunday: 9:00 AM - 5:00 PM</li>
</ul>
</div>
</div>
<div>
<div style="position: relative;">
        <div style="position: relative; padding-bottom: 75%; height: 0; overflow: hidden;">
        <iframe
  src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3978.578442822027!2d36.9539129!3d-1.3920337!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0%3A0!2zMcKwMjMnMzEuMyJTIDM2wrA1Nyc0My40IkU!5e0!3m2!1sen!2ske!4v1713513361234!5m2!1sen!2ske"
  width="700"
  height="550"
  style="border:0;"
  allowfullscreen=""
  loading="lazy"
  referrerpolicy="no-referrer-when-downgrade">
</iframe>

</div>
</div>
</div>
</div>
</div>
</section>

<!-- Newsletter Section -->
<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto text-center">
            <h3 class="text-2xl font-bold text-gray-900 mb-4">Subscribe to Our Newsletter</h3>
            <p class="text-gray-600 mb-6">Stay updated with our latest offers and laundry care tips.</p>
            <form id="newsletterForm" class="flex flex-col sm:flex-row gap-4">
                <input type="email" name="email" required placeholder="Enter your email" class="flex-1 px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                <button type="submit" class="px-8 py-3 bg-primary text-white font-medium rounded-md hover:bg-opacity-90 transition flex items-center justify-center min-w-[140px]">
                    <span class="subscribe-text">Subscribe</span>
                    <span class="loading-text hidden">
                        <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Subscribing...
                    </span>
                </button>
            </form>
            <div id="newsletterMessage" class="mt-4 text-sm hidden">
                <p class="text-green-600"></p>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Success Modal -->
<div id="newsletterSuccessModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6">
            <div>
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Subscription Successful!
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Thank you for subscribing to our newsletter. You'll be the first to know about our latest offers and updates!
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-6">
                <button type="button" onclick="closeNewsletterModal()" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script>

document.addEventListener('DOMContentLoaded', function() {
    // Close modals when clicking outside
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                const modal = this.closest('.fixed.inset-0');
                if (modal && modal.id) {
                    if (modal.id === 'trackOrderModal') {
                        closeTrackOrderModal();
                    } else {
                        closeModal(modal.id);
                    }
                }
            }
        });
    });

    // Close modals when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });

    // Track Order Form Submission
    const trackOrderForm = document.getElementById('trackOrderForm');
    if (trackOrderForm) {
        trackOrderForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const trackingNumber = document.getElementById('trackingNumber').value;
            // Add your tracking logic here
            document.getElementById('trackOrderResults').classList.remove('hidden');
        });
    }

    // Newsletter Form Submission
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[type="email"]');
            const submitButton = this.querySelector('button[type="submit"]');
            const subscribeText = submitButton.querySelector('.subscribe-text');
            const loadingText = submitButton.querySelector('.loading-text');
            const originalText = subscribeText.textContent;
            
            // Show loading state
            subscribeText.classList.add('hidden');
            loadingText.classList.remove('hidden');
            submitButton.disabled = true;
            
            // Prepare form data
            const formData = new FormData();
            formData.append('email', emailInput.value);
            
            // Send request
            fetch('process_newsletter.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success modal
                    const successModal = document.getElementById('newsletterSuccessModal');
                    successModal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    
                    // Clear form
                    emailInput.value = '';
                } else {
                    // Show error message
                    const messageDiv = document.getElementById('newsletterMessage');
                    const messageText = messageDiv.querySelector('p');
                    messageText.textContent = data.message || 'An error occurred. Please try again.';
                    messageText.className = 'text-red-600';
                    messageDiv.classList.remove('hidden');
                    
                    // Auto-hide error message after 5 seconds
                    setTimeout(() => {
                        messageDiv.classList.add('hidden');
                    }, 5000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const messageDiv = document.getElementById('newsletterMessage');
                const messageText = messageDiv.querySelector('p');
                messageText.textContent = 'An error occurred. Please try again.';
                messageText.className = 'text-red-600';
                messageDiv.classList.remove('hidden');
                
                // Auto-hide error message after 5 seconds
                setTimeout(() => {
                    messageDiv.classList.add('hidden');
                }, 5000);
            })
            .finally(() => {
                // Reset button state
                subscribeText.classList.remove('hidden');
                loadingText.classList.add('hidden');
                submitButton.disabled = false;
            });
        });
    }

    // Close Newsletter Modal
    window.closeNewsletterModal = function() {
        const successModal = document.getElementById('newsletterSuccessModal');
        successModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    };
});

document.addEventListener('DOMContentLoaded', function() {
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
    document.querySelectorAll('[href="login.php"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            closeAllModals();
            openModal('loginModal');
        });
    });

  
    document.querySelectorAll('[href="profile.php"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            closeAllModals();
            openModal('profileModal');
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
    document.getElementById('loginForm').addEventListener('submit', function(e) {
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
                alert(data.message || 'Login failed. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });

    // Place Order Modal functions
    window.openPlaceOrderModal = function() {
        <?php if (isset($_SESSION['customer_id'])): ?>
            document.getElementById('placeOrderModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        <?php else: ?>
            window.location.href = 'login.php';
        <?php endif; ?>
    }

    window.closePlaceOrderModal = function() {
        document.getElementById('placeOrderModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Success Modal functions
    window.closeSuccessModal = function() {
        document.getElementById('successModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    window.showSuccessModal = function(data) {
        document.getElementById('successTrackingNumber').textContent = data.tracking_number;
        document.getElementById('successService').textContent = data.service_name;
        document.getElementById('successWeight').textContent = data.weight + ' kg';
        document.getElementById('successPrice').textContent = 'KES ' + data.price;
        document.getElementById('successPickupDate').textContent = data.pickup_date;
        document.getElementById('successDeliveryDate').textContent = data.delivery_date;
        
        document.getElementById('successModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Place Order form submission
    const placeOrderForm = document.getElementById('placeOrderForm');
    if (placeOrderForm) {
        // Remove any existing event listeners
        const newPlaceOrderForm = placeOrderForm.cloneNode(true);
        placeOrderForm.parentNode.replaceChild(newPlaceOrderForm, placeOrderForm);
        
        newPlaceOrderForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'Placing Order...';
            
            fetch('place_order_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closePlaceOrderModal();
                    showSuccessModal(data);
                    // Redirect to dashboard after 3 seconds
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 3000);
                } else {
                    // Show error in modal instead of alert
                    const successModal = document.getElementById('successModal');
                    successModal.classList.remove('hidden');
                    
                    // Update modal title and content
                    const modalTitle = successModal.querySelector('h3');
                    modalTitle.textContent = 'Order Submission Status';
                    
                    // Clear previous content
                    const modalContent = successModal.querySelector('.space-y-4');
                    modalContent.innerHTML = '';
                    
                    // Add error message
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'text-red-600 font-medium text-center py-4';
                    errorMessage.textContent = data.message || 'An error occurred. Please try again.';
                    modalContent.appendChild(errorMessage);
                    
                    // Auto-close after 5 seconds
                    setTimeout(() => {
                        closeSuccessModal();
                    }, 5000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Show error in modal instead of alert
                const successModal = document.getElementById('successModal');
                successModal.classList.remove('hidden');
                
                // Update modal title and content
                const modalTitle = successModal.querySelector('h3');
                modalTitle.textContent = 'Order Submission Status';
                
                // Clear previous content
                const modalContent = successModal.querySelector('.space-y-4');
                modalContent.innerHTML = '';
                
                // Add error message
                const errorMessage = document.createElement('div');
                errorMessage.className = 'text-red-600 font-medium text-center py-4';
                errorMessage.textContent = 'An error occurred. Please try again.';
                modalContent.appendChild(errorMessage);
                
                // Auto-close after 5 seconds
                setTimeout(() => {
                    closeSuccessModal();
                }, 5000);
            })
            .finally(() => {
                // Reset button state
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            });
        });
    }

    // Contact Modal functions
    window.openContactModal = function() {
        closeAllModals();
        openModal('contactModal');
    }

    window.closeContactModal = function() {
        closeModal('contactModal');
    }

    const faqButtons = document.querySelectorAll('.faq-btn');
    
    faqButtons.forEach(button => {
        button.addEventListener('click', () => {
            const content = button.nextElementSibling;
            const icon = button.querySelector('i');
            
            // Toggle content
            content.classList.toggle('hidden');
            
            // Rotate icon
            icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
            
            // Close other open FAQs
            faqButtons.forEach(otherButton => {
                if (otherButton !== button) {
                    otherButton.nextElementSibling.classList.add('hidden');
                    otherButton.querySelector('i').style.transform = 'rotate(0deg)';
                }
            });
        });
    });
});
</script>

<script>
// Preloader
document.addEventListener('DOMContentLoaded', function() {
    // Show preloader
    const preloader = document.querySelector('.preloader');
    
    // Hide preloader after page loads
    window.addEventListener('load', function() {
        setTimeout(() => {
            preloader.classList.add('fade-out');
            // Remove preloader from DOM after animation
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 500);
        }, 2000); // Show preloader for 2 seconds
    });
});

// Modal Functions
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
    const modals = document.querySelectorAll('.fixed.inset-0.z-50');
    modals.forEach(modal => {
        modal.classList.add('hidden');
    });
    document.body.style.overflow = 'auto';
}

// Track Order Modal Functions
function openTrackOrderModal() {
    closeAllModals();
    const modal = document.getElementById('trackOrderModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeTrackOrderModal() {
    const modal = document.getElementById('trackOrderModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

// Success Modal Functions
function openSuccessModal(title, message, isSuccess = true) {
    const modal = document.getElementById('successModal');
    if (modal) {
        const modalTitle = modal.querySelector('h3');
        const modalContent = modal.querySelector('.space-y-4');
        
        modalTitle.textContent = title;
        modalContent.innerHTML = `
            <div class="${isSuccess ? 'text-green-600' : 'text-red-600'} font-medium text-center py-4">
                ${message}
            </div>
        `;
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Auto-close after 5 seconds
        setTimeout(() => {
            closeSuccessModal();
        }, 5000);
    }
}

function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}
</script>

<style>
.faq-btn:hover {
    background-color: rgba(79, 70, 229, 0.05);
}

.faq-content {
    transition: all 0.3s ease-in-out;
}

@media (max-width: 640px) {
    .faq-btn h3 {
        font-size: 1.125rem;
    }
    
    .faq-content p {
        font-size: 0.875rem;
    }
}
</style>
</body>
</html>
