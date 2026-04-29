<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
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
<li><a href="aboutus.php" class="text-gray-300 hover:text-white transition">About Us</a></li>
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
<span class="text-gray-300">Mlolongo Phase 3 near Turning point church.<br>Nairobi, Kenya</span>
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
<div style="display: flex; justify-content: space-between; align-items: center;">
  
  <p style="margin: 0;">
    Powered By 
    <a href="https://timestentechnologies.co.ke/" target="_blank" style="color: #FF8C42; text-decoration: none;">
      Timesten Technologies
    </a>
  </p>

  <p style="margin: 0; text-align: center; flex: 1;">
    &copy; <?php echo date('Y'); ?> <?php echo $row_header_logo['title']; ?>. All rights reserved. |
    <a href="#" class="hover:text-white transition">Privacy Policy</a> |
    <a href="#" class="hover:text-white transition">Terms of Service</a>
  </p>

</div>
</div>
</div>
</footer>