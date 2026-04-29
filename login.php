<?php
// Start output buffering
ob_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $stmt = $conn->prepare("SELECT id, email, password, fname, lname FROM customer WHERE email = ? AND status = 'active'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Update last login
                $update_stmt = $conn->prepare("UPDATE customer SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                
                // Set session variables
                $_SESSION['customer_id'] = $user['id'];
                $_SESSION['customer_email'] = $user['email'];
                $_SESSION['customer_name'] = $user['fname'] . ' ' . $user['lname'];
                
                // Debug session variables
                error_log("Session variables set: " . print_r($_SESSION, true));
                
                // Clear output buffer before redirect
                ob_end_clean();
                
                // Redirect to home page
                header("Location: /index.php");
                exit();
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}

// Include the contact modal
include 'contact_modal.php';

// Include the common header
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Opulent Laundry Services</title>
<link rel="icon" type="image/png" sizes="32x32" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
<link rel="icon" type="image/png" sizes="16x16" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
<link rel="shortcut icon" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
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
background-color: #f9fafb;
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
</style>
</head>
<body class="bg-gray-50 min-h-screen">
<?php include 'includes/navigation.php'; ?>

<!-- Main Content -->
<main class="flex-grow flex items-center justify-center py-12 px-4">
<div class="w-full max-w-md">
<div class="bg-white shadow-lg rounded-lg p-8">
<div class="text-center mb-8">
<h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back</h1>
<p class="text-gray-600">Login to your FreshPress account</p>
</div>

<?php if ($error): ?>
    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-4">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<form method="POST" action="" class="space-y-6">
<div>
<label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
<input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
</div>

<div>
<label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
<input type="password" name="password" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
</div>

<div class="flex items-center justify-between">
<div class="flex items-center">
<input type="checkbox" id="remember" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
<label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
</div>
<a href="forgot_password.php" class="text-sm text-primary hover:underline">Forgot password?</a>
</div>

<button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded-md hover:bg-opacity-90 transition">
Login
</button>
</form>
<div class="relative flex items-center justify-center my-6">
<div class="border-t border-gray-300 absolute w-full"></div>
<span class="bg-white px-4 text-sm text-gray-500 relative">or</span>
</div>
<!-- <div class="grid grid-cols-2 gap-4 mb-6">
<button class="flex items-center justify-center py-3 px-4 border border-gray-300 !rounded-button whitespace-nowrap hover:bg-gray-50 transition">
<i class="ri-google-fill mr-2 text-red-500"></i>
<span class="text-sm font-medium text-gray-700">Google</span>
</button>
<button class="flex items-center justify-center py-3 px-4 border border-gray-300 !rounded-button whitespace-nowrap hover:bg-gray-50 transition">
<i class="ri-facebook-fill mr-2 text-blue-600"></i>
<span class="text-sm font-medium text-gray-700">Facebook</span>
</button>
</div> -->
<div class="text-center">
<p class="text-gray-600">
Don't have an account?
<a href="sign_up.php" data-readdy="true" class="text-primary hover:text-primary/80 font-medium">Sign up</a>
</p>
</div>
</div>
</div>
</main>
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
// Password visibility toggle
const passwordToggle = document.querySelector('.ri-eye-off-line');
const passwordInput = document.getElementById('password');
if (passwordToggle && passwordInput) {
passwordToggle.addEventListener('click', function() {
if (passwordInput.type === 'password') {
passwordInput.type = 'text';
this.classList.remove('ri-eye-off-line');
this.classList.add('ri-eye-line');
} else {
passwordInput.type = 'password';
this.classList.remove('ri-eye-line');
this.classList.add('ri-eye-off-line');
}
});
}
});
</script>
</body>
</html>
