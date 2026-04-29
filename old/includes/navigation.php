<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Header -->
<header class="sticky top-0 z-50 bg-[#0F214D] shadow-sm">
    <div class="container mx-auto px-8 py-4 flex items-center justify-between">
        <a href="index.php" class="flex items-center">
            <img src="admin/uploadImage/Logo/<?php echo $row_header_logo['logo'];?>" alt="logo" class="h-14 w-auto object-contain">
        </a>
        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center space-x-8">
            <a href="index.php" class="text-white font-medium hover:text-primary transition">Home</a>
            <a href="aboutus.php" class="text-white font-medium hover:text-primary transition">About Us</a>
            <a href="services.php" class="text-white font-medium hover:text-primary transition">Services</a>
            <a href="pricing.php" class="text-white font-medium hover:text-primary transition">Pricing</a>
            <a href="faq.php" class="text-white font-medium hover:text-primary transition">FAQ</a>
            <button onclick="openContactModal()" class="text-white font-medium hover:text-primary transition">Contact Us</button>
            <?php if (isset($_SESSION['customer_id'])): ?>
                <a href="dashboard.php" class="text-white font-medium hover:text-primary transition">Dashboard</a>
                <button onclick="openPlaceOrderModal()" class="text-white font-medium hover:text-primary transition">Place Order</button>
                <a href="profile.php" class="text-white font-medium hover:text-primary transition">Profile</a>
                <a href="logout.php" class="text-white font-medium hover:text-primary transition">Logout</a>
            <?php else: ?>
                <a href="login.php" class="text-white font-medium hover:text-primary transition">Login</a>
                <a href="sign_up.php" class="text-white font-medium hover:text-primary transition">Sign Up</a>
            <?php endif; ?>
        </nav>
        <!-- Mobile Menu Button -->
        <div class="flex items-center md:hidden">
            <button onclick="toggleMobileMenu()" class="text-white p-2 focus:outline-none">
                <i class="ri-menu-line text-2xl"></i>
            </button>
        </div>
    </div>
    <!-- Mobile Navigation -->
    <div id="mobileMenu" class="hidden md:hidden bg-[#0F214D] border-t border-gray-700">
        <nav class="container mx-auto px-8 py-4 flex flex-col space-y-4">
            <a href="index.php" class="text-white font-medium hover:text-primary transition">Home</a>
            <a href="aboutus.php" class="text-white font-medium hover:text-primary transition">About Us</a>
            <a href="services.php" class="text-white font-medium hover:text-primary transition">Services</a>
            <a href="pricing.php" class="text-white font-medium hover:text-primary transition">Pricing</a>
            <a href="faq.php" class="text-white font-medium hover:text-primary transition">FAQ</a>
            <button onclick="openContactModal()" class="text-white font-medium hover:text-primary transition text-left">Contact Us</button>
            <?php if (isset($_SESSION['customer_id'])): ?>
                <a href="dashboard.php" class="text-white font-medium hover:text-primary transition">Dashboard</a>
                <button onclick="openPlaceOrderModal()" class="text-white font-medium hover:text-primary transition text-left">Place Order</button>
                <a href="profile.php" class="text-white font-medium hover:text-primary transition">Profile</a>
                <a href="logout.php" class="text-white font-medium hover:text-primary transition">Logout</a>
            <?php else: ?>
                <a href="login.php" class="text-white font-medium hover:text-primary transition">Login</a>
                <a href="sign_up.php" class="text-white font-medium hover:text-primary transition">Sign Up</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<script>
function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobileMenu');
    mobileMenu.classList.toggle('hidden');
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(e) {
    const mobileMenu = document.getElementById('mobileMenu');
    const menuButton = document.querySelector('.ri-menu-line');
    if (mobileMenu && !mobileMenu.contains(e.target) && e.target !== menuButton && !menuButton.contains(e.target)) {
        mobileMenu.classList.add('hidden');
    }
});

// Close mobile menu when window is resized to desktop view
window.addEventListener('resize', function() {
    const mobileMenu = document.getElementById('mobileMenu');
    if (window.innerWidth >= 768 && mobileMenu && !mobileMenu.classList.contains('hidden')) {
        mobileMenu.classList.add('hidden');
    }
});
</script>