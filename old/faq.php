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

// Include the contact modal
include 'contact_modal.php';

// Include the common order modal template
include 'order_modal.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FAQ - FreshPress Laundry Services</title>
<script src="https://cdn.tailwindcss.com/3.4.16"></script>
<script>tailwind.config={theme:{extend:{colors:{primary:'#4F46E5',secondary:'#10B981'},borderRadius:{'none':'0px','sm':'4px',DEFAULT:'8px','md':'12px','lg':'16px','xl':'20px','2xl':'24px','3xl':'32px','full':'9999px','button':'8px'}}}}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
<style>
:where([class^="ri-"])::before { content: "\f3c2"; }
body {
font-family: 'Inter', sans-serif;
}
</style>
</head>
<body class="bg-white min-h-screen">
<!-- Header -->
<?php include 'includes/navigation.php'; ?>

<!-- FAQ Section -->
<section class="py-16 md:py-24 bg-gradient-to-b from-gray-50 to-white">
  <div class="container mx-auto px-4">
    <div class="text-center mb-16">
      <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Frequently Asked Questions</h2>
      <p class="text-lg text-gray-600 max-w-3xl mx-auto">Find answers to common questions about our services, timing, care, and payment options.</p>
    </div>
    <div class="max-w-3xl mx-auto space-y-6">

      <!-- General -->
      <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300">
        <button class="faq-btn flex justify-between items-center w-full p-6 text-left focus:outline-none">
          <h3 class="text-xl font-semibold text-gray-900">What services do you offer?</h3>
          <i class="ri-arrow-down-s-line text-primary text-2xl transition-transform duration-300"></i>
        </button>
        <div class="faq-content px-6 pb-6 hidden">
          <p class="text-gray-600 leading-relaxed">We offer wash, dry & fold, wash, dry, iron & fold, ironing only, pick-up & delivery, and special garment care. We also handle linens, baby clothes, and more.</p>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300">
        <button class="faq-btn flex justify-between items-center w-full p-6 text-left focus:outline-none">
          <h3 class="text-xl font-semibold text-gray-900">Do I need to sort my laundry before pick-up?</h3>
          <i class="ri-arrow-down-s-line text-primary text-2xl transition-transform duration-300"></i>
        </button>
        <div class="faq-content px-6 pb-6 hidden">
          <p class="text-gray-600 leading-relaxed">Not at all! We'll take care of sorting by color, fabric type, and washing instructions.</p>
        </div>
      </div>

      <!-- Pick-up & Delivery -->
      <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300">
        <button class="faq-btn flex justify-between items-center w-full p-6 text-left focus:outline-none">
          <h3 class="text-xl font-semibold text-gray-900">How do I schedule a pick-up?</h3>
          <i class="ri-arrow-down-s-line text-primary text-2xl transition-transform duration-300"></i>
        </button>
        <div class="faq-content px-6 pb-6 hidden">
          <p class="text-gray-600 leading-relaxed">Simply message us on WhatsApp at 0745812730 with your name, location, and preferred pick-up time or via our website.</p>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300">
        <button class="faq-btn flex justify-between items-center w-full p-6 text-left focus:outline-none">
          <h3 class="text-xl font-semibold text-gray-900">What are your pick-up and delivery hours?</h3>
          <i class="ri-arrow-down-s-line text-primary text-2xl transition-transform duration-300"></i>
        </button>
        <div class="faq-content px-6 pb-6 hidden">
          <p class="text-gray-600 leading-relaxed">We operate Monday to Saturday from 8:00 AM to 8:00 PM. Let us know what works for you!</p>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300">
        <button class="faq-btn flex justify-between items-center w-full p-6 text-left focus:outline-none">
          <h3 class="text-xl font-semibold text-gray-900">Is there a delivery fee?</h3>
          <i class="ri-arrow-down-s-line text-primary text-2xl transition-transform duration-300"></i>
        </button>
        <div class="faq-content px-6 pb-6 hidden">
          <p class="text-gray-600 leading-relaxed">Delivery is free within our service area. For longer distances, a small fee may apply—we'll inform you upfront.</p>
        </div>
      </div>

      <!-- Timing & Turnaround -->
      <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300">
        <button class="faq-btn flex justify-between items-center w-full p-6 text-left focus:outline-none">
          <h3 class="text-xl font-semibold text-gray-900">How long does it take to get my laundry back?</h3>
          <i class="ri-arrow-down-s-line text-primary text-2xl transition-transform duration-300"></i>
        </button>
        <div class="faq-content px-6 pb-6 hidden">
          <p class="text-gray-600 leading-relaxed">Standard turnaround is 24 to 48 hours. We also offer same-day express service on request (subject to availability).</p>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300">
        <button class="faq-btn flex justify-between items-center w-full p-6 text-left focus:outline-none">
          <h3 class="text-xl font-semibold text-gray-900">Can I schedule weekly or regular laundry service?</h3>
          <i class="ri-arrow-down-s-line text-primary text-2xl transition-transform duration-300"></i>
        </button>
        <div class="faq-content px-6 pb-6 hidden">
          <p class="text-gray-600 leading-relaxed">Yes! Ask about our subscription plans for weekly pick-up and delivery.</p>
        </div>
      </div>

      <!-- Clothes & Care -->
      <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300">
        <button class="faq-btn flex justify-between items-center w-full p-6 text-left focus:outline-none">
          <h3 class="text-xl font-semibold text-gray-900">Do you handle delicate or special fabrics?</h3>
          <i class="ri-arrow-down-s-line text-primary text-2xl transition-transform duration-300"></i>
        </button>
        <div class="faq-content px-6 pb-6 hidden">
          <p class="text-gray-600 leading-relaxed">Absolutely. We use gentle cycles, proper settings, and hand-wash where needed to care for delicates like silk, lace, and baby clothes.</p>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300">
        <button class="faq-btn flex justify-between items-center w-full p-6 text-left focus:outline-none">
          <h3 class="text-xl font-semibold text-gray-900">What detergent do you use? Can I request fragrance-free?</h3>
          <i class="ri-arrow-down-s-line text-primary text-2xl transition-transform duration-300"></i>
        </button>
        <div class="faq-content px-6 pb-6 hidden">
          <p class="text-gray-600 leading-relaxed">We use high-quality, skin-friendly detergents. Fragrance-free and hypoallergenic options are available—just let us know when booking.</p>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300">
        <button class="faq-btn flex justify-between items-center w-full p-6 text-left focus:outline-none">
          <h3 class="text-xl font-semibold text-gray-900">What happens if something is damaged or missing?</h3>
          <i class="ri-arrow-down-s-line text-primary text-2xl transition-transform duration-300"></i>
        </button>
        <div class="faq-content px-6 pb-6 hidden">
          <p class="text-gray-600 leading-relaxed">We treat every item with care. In the rare case of damage or loss, we follow a fair compensation policy—your satisfaction is our priority.</p>
        </div>
      </div>

      <!-- Payment -->
      <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300">
        <button class="faq-btn flex justify-between items-center w-full p-6 text-left focus:outline-none">
          <h3 class="text-xl font-semibold text-gray-900">How do I pay?</h3>
          <i class="ri-arrow-down-s-line text-primary text-2xl transition-transform duration-300"></i>
        </button>
        <div class="faq-content px-6 pb-6 hidden">
          <p class="text-gray-600 leading-relaxed">We accept cash, mobile money, and bank transfers. Payment is made upon delivery unless otherwise arranged.</p>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300">
        <button class="faq-btn flex justify-between items-center w-full p-6 text-left focus:outline-none">
          <h3 class="text-xl font-semibold text-gray-900">Are there discounts for bulk laundry or regular customers?</h3>
          <i class="ri-arrow-down-s-line text-primary text-2xl transition-transform duration-300"></i>
        </button>
        <div class="faq-content px-6 pb-6 hidden">
          <p class="text-gray-600 leading-relaxed">Yes! We offer discounts for bulk orders, students, and weekly plans. Ask us for more details when booking.</p>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- Sign Up Modal -->
<div id="signUpModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
<div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden">
<div class="flex justify-between items-center p-6 border-b">
<h3 class="text-2xl font-bold text-gray-900">Create Your Account</h3>
<button class="close-modal text-gray-500 hover:text-gray-700">
<i class="ri-close-line ri-lg"></i>
</button>
</div>
<div class="p-6">
<div class="mb-6">
<div class="flex justify-center mb-6">
<a href="#" class="text-3xl font-['Pacifico'] text-primary">Opulent Laundry</a>
</div>
<p class="text-gray-600 text-center mb-6">Join Opulent Laundry for convenient, professional laundry services.</p>
<!-- Social Sign Up Buttons -->
<div class="space-y-3 mb-6">
<button class="w-full flex items-center justify-center gap-2 border border-gray-300 py-3 rounded !rounded-button hover:bg-gray-50 transition">
<i class="ri-google-fill text-red-500"></i>
<span>Continue with Google</span>
</button>
<button class="w-full flex items-center justify-center gap-2 border border-gray-300 py-3 rounded !rounded-button hover:bg-gray-50 transition">
<i class="ri-facebook-fill text-blue-600"></i>
<span>Continue with Facebook</span>
</button>
</div>
<div class="relative flex items-center justify-center mb-6">
<div class="border-t border-gray-300 absolute w-full"></div>
<span class="bg-white px-4 relative text-sm text-gray-500">or sign up with email</span>
</div>
</div>
<form id="signUpForm">
<div class="grid grid-cols-2 gap-4 mb-4">
<div>
<label for="firstName" class="block text-gray-700 text-sm font-medium mb-1">First Name</label>
<input type="text" id="firstName" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="First name" required>
</div>
<div>
<label for="lastName" class="block text-gray-700 text-sm font-medium mb-1">Last Name</label>
<input type="text" id="lastName" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Last name" required>
</div>
</div>
<div class="mb-4">
<label for="signUpEmail" class="block text-gray-700 text-sm font-medium mb-1">Email Address</label>
<input type="email" id="signUpEmail" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Your email address" required>
</div>
<div class="mb-4">
<label for="signUpPhone" class="block text-gray-700 text-sm font-medium mb-1">Phone Number</label>
<input type="tel" id="signUpPhone" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Your phone number" required>
</div>
<div class="mb-4">
<label for="signUpPassword" class="block text-gray-700 text-sm font-medium mb-1">Password</label>
<div class="relative">
<input type="password" id="signUpPassword" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Create a password" required>
<button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700" id="togglePassword">
<i class="ri-eye-line"></i>
</button>
</div>
<p class="text-xs text-gray-500 mt-1">Password must be at least 8 characters long with a number and a special character.</p>
</div>
<div class="mb-6">
<label class="flex items-start">
<input type="checkbox" class="custom-checkbox mt-1" required>
<span class="ml-2 text-sm text-gray-600">I agree to the <a href="#" class="text-primary hover:underline">Terms of Service</a> and <a href="#" class="text-primary hover:underline">Privacy Policy</a>.</span>
</label>
</div>
<button type="submit" class="w-full bg-primary text-white py-3 !rounded-button whitespace-nowrap hover:bg-opacity-90 transition font-medium">Create Account</button>
</form>
<div class="mt-6 text-center">
<p class="text-sm text-gray-600">Already have an account? <a href="#" class="text-primary font-medium hover:underline">Log In</a></p>
</div>
</div>
</div>
</div>

<!-- Login Modal -->
<div id="loginModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-2xl font-bold text-gray-900">Login to Your Account</h3>
            <button class="close-modal text-gray-500 hover:text-gray-700">
                <i class="ri-close-line ri-lg"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-6">
                <div class="flex justify-center mb-6">
                    <img src="admin/uploadImage/Logo/<?php echo $row_header_logo['logo'];?>" alt="logo" class="h-24 w-auto object-contain">
                </div>
                <p class="text-gray-600 text-center mb-6">Welcome back! Please login to your account.</p>
            </div>
            <form id="loginForm" method="POST" action="login_process.php">
                <div class="mb-4">
                    <label for="loginEmail" class="block text-gray-700 text-sm font-medium mb-1">Email Address</label>
                    <input type="email" id="loginEmail" name="email" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                </div>
                <div class="mb-6">
                    <label for="loginPassword" class="block text-gray-700 text-sm font-medium mb-1">Password</label>
                    <div class="relative">
                        <input type="password" id="loginPassword" name="password" class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                        <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 toggle-password">
                            <i class="ri-eye-line"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="w-full bg-primary text-white py-3 rounded-button hover:bg-opacity-90 transition font-medium">Login</button>
            </form>
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">Don't have an account? <a href="#" class="text-primary font-medium hover:underline open-signup">Sign Up</a></p>
            </div>
        </div>
    </div>
</div>
<!-- Footer -->
<footer class="bg-[#0F214D] text-white pt-16 pb-8">
<div class="container mx-auto px-8">
<div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
<div>
<img src="admin/uploadImage/Logo/<?php echo $row_header_logo['logo'];?>" alt="logo" class="h-24 w-auto object-contain mb-6">
<p class="text-gray-300 mb-6">Professional laundry and dry cleaning services that save you time and deliver exceptional results.</p>
<div class="flex space-x-4">
<a href="#" class="w-10 h-10 flex items-center justify-center bg-white/10 rounded-full hover:bg-primary transition">
<i class="ri-facebook-fill text-white"></i>
</a>
<a href="#" class="w-10 h-10 flex items-center justify-center bg-white/10 rounded-full hover:bg-primary transition">
<i class="ri-twitter-x-fill text-white"></i>
</a>
<a href="#" class="w-10 h-10 flex items-center justify-center bg-white/10 rounded-full hover:bg-primary transition">
<i class="ri-instagram-fill text-white"></i>
</a>
<a href="#" class="w-10 h-10 flex items-center justify-center bg-white/10 rounded-full hover:bg-primary transition">
<i class="ri-linkedin-fill text-white"></i>
</a>
</div>
</div>
<div>
<h4 class="text-lg font-semibold mb-6 text-white">Quick Links</h4>
<ul class="space-y-3">
<li><a href="index.php" class="text-gray-300 hover:text-white transition">Home</a></li>
<li><a href="about.php" class="text-gray-300 hover:text-white transition">About Us</a></li>
<li><a href="pricing.php" class="text-gray-300 hover:text-white transition">Pricing</a></li>
<li><a href="faq.php" class="text-gray-300 hover:text-white transition">FAQ</a></li>
<li><a href="contact.php" class="text-gray-300 hover:text-white transition">Contact Us</a></li>
</ul>
</div>
<div>
<h4 class="text-lg font-semibold mb-6 text-white">Services</h4>
<ul class="space-y-3">
<li><a href="#" class="text-gray-300 hover:text-white transition">Regular Laundry</a></li>
<li><a href="#" class="text-gray-300 hover:text-white transition">Dry Cleaning</a></li>
<li><a href="#" class="text-gray-300 hover:text-white transition">Express Service</a></li>
<li><a href="#" class="text-gray-300 hover:text-white transition">Subscription Plans</a></li>
<li><a href="#" class="text-gray-300 hover:text-white transition">Business Solutions</a></li>
</ul>
</div>
<div>
<h4 class="text-lg font-semibold mb-6 text-white">Contact</h4>
<ul class="space-y-3">
<li class="flex items-start">
<i class="ri-map-pin-line text-primary mr-3 mt-1"></i>
<span class="text-gray-300">Mlolongo Phase 3 near Turning point church.<br>Nairobi, Kenya</span>
</li>
<li class="flex items-center">
<i class="ri-phone-line text-primary mr-3"></i>
<span class="text-gray-300">0745812730</span>
</li>
<li class="flex items-center">
<i class="ri-mail-line text-primary mr-3"></i>
<span class="text-gray-300">opulentlaundry1@gmail.com</span>
</li>
</ul>
</div>
</div>
<div class="pt-8 border-t border-white/10 text-center text-gray-300">
<div class="flex justify-center space-x-6 mb-6">
<i class="ri-visa-fill text-2xl"></i>
<i class="ri-mastercard-fill text-2xl"></i>
<i class="ri-paypal-fill text-2xl"></i>
<img style="width: 50px; height: 50px;" src="images/mpesa.png" alt="logo" class="h-24 w-auto object-contain">
</div>
<p>&copy; 2025 Opulent Laundry. All rights reserved. | <a href="#" class="hover:text-white transition">Privacy Policy</a> | <a href="#" class="hover:text-white transition">Terms of Service</a></p>
</div>
</div>
</footer>
<script>
document.addEventListener('DOMContentLoaded', function() {
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
<script src="js/order.js"></script>
</html> 