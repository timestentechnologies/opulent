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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Opulent Laundry</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="shortcut icon" href="favicon.ico">
    <link rel="apple-touch-icon" href="images/favicon.png">
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script src="js/order.js"></script>

    <script>
        tailwind.config={
            theme:{
                extend:{
                    colors:{
                        primary:'#4F46E5',
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
    <!-- Define login status for use in order.js -->
    <script>
    var user_logged_in = <?php echo isset($_SESSION['customer_id']) ? 'true' : 'false'; ?>;
    </script>
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
        .hero-section {
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.6), rgba(255, 255, 255, 0.2)), url('https://readdy.ai/api/search-image?query=professional%20laundry%20service%20with%20neatly%20folded%20clean%20clothes%2C%20bright%20modern%20laundromat%20with%20washing%20machines%2C%20soft%20blue%20and%20white%20color%20scheme%2C%20clean%20environment%2C%20stacks%20of%20freshly%20laundered%20towels%20and%20garments%2C%20minimalist%20aesthetic%2C%20high-end%20appearance&width=1600&height=800&seq=1&orientation=landscape');
            background-size: cover;
            background-position: center right;
        }
        input:focus {
            outline: none;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <?php include 'includes/navigation.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section py-20">
        <div class="container mx-auto px-6 w-full">
            <div class="max-w-2xl">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Laundry? Not your thing? We get it.</h1>
                <p class="text-lg text-gray-700 mb-8">That's why Opulent Laundry is here—to take laundry off your to-do list and bring fresh, clean clothes straight to your door.</p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="#book-now" class="bg-primary text-white px-8 py-3 !rounded-button font-medium hover:bg-opacity-90 transition-colors text-center whitespace-nowrap">Book Our Services</a>
                    <a href="#services" class="bg-white text-primary border border-primary px-8 py-3 !rounded-button font-medium hover:bg-gray-50 transition-colors text-center whitespace-nowrap">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Value Proposition -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">For Everyone Who Values Their Time</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">Whether you're a student buried in assignments, a parent juggling everything, or a young professional chasing big goals—we've got you.</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-10">
                <div class="bg-gray-50 p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-16 h-16 bg-primary bg-opacity-10 rounded-full flex items-center justify-center mb-6 mx-auto">
                        <i class="ri-book-open-line ri-xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4 text-center">Busy Students</h3>
                    <p class="text-gray-600 text-center">Focus on your studies while we handle the laundry. No more late-night washing sessions or weekend laundromat trips.</p>
                </div>
                
                <div class="bg-gray-50 p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-16 h-16 bg-primary bg-opacity-10 rounded-full flex items-center justify-center mb-6 mx-auto">
                        <i class="ri-home-heart-line ri-xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4 text-center">Busy Parents</h3>
                    <p class="text-gray-600 text-center">We understand the endless cycle of family laundry. Let us give you back precious time to spend with your loved ones.</p>
                </div>
                
                <div class="bg-gray-50 p-8 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-16 h-16 bg-primary bg-opacity-10 rounded-full flex items-center justify-center mb-6 mx-auto">
                        <i class="ri-briefcase-4-line ri-xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4 text-center">Young Professionals</h3>
                    <p class="text-gray-600 text-center">Climbing the career ladder takes time and energy. We'll keep your wardrobe pristine while you focus on your professional goals.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Service Description -->
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row gap-12 items-center">
                <div class="md:w-1/2">
                    <img src="https://readdy.ai/api/search-image?query=professional%20laundry%20service%20worker%20carefully%20folding%20clothes%2C%20high-end%20laundry%20facility%20with%20modern%20equipment%2C%20clean%20and%20bright%20environment%2C%20attention%20to%20detail%2C%20eco-friendly%20detergents%20visible%2C%20premium%20laundry%20service%20setting&width=600&height=500&seq=2&orientation=landscape" alt="Professional laundry service" class="rounded-lg shadow-md w-full h-auto object-cover">
                </div>
                
                <div class="md:w-1/2">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">Quality Care for Every Fabric</h2>
                    <p class="text-lg text-gray-600 mb-6">From everyday wear to linens, towels, and delicate fabrics, we treat every piece like it matters—because it does.</p>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="w-10 h-10 flex items-center justify-center text-primary mr-4 mt-1">
                                <i class="ri-check-line ri-lg"></i>
                            </div>
                            <p class="text-gray-700">We use high-quality detergents that are gentle on fabrics but tough on stains.</p>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-10 h-10 flex items-center justify-center text-primary mr-4 mt-1">
                                <i class="ri-check-line ri-lg"></i>
                            </div>
                            <p class="text-gray-700">Our professional-grade machines ensure thorough cleaning while protecting your clothes.</p>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-10 h-10 flex items-center justify-center text-primary mr-4 mt-1">
                                <i class="ri-check-line ri-lg"></i>
                            </div>
                            <p class="text-gray-700">We're committed to eco-friendly practices that are better for your clothes and the planet.</p>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-10 h-10 flex items-center justify-center text-primary mr-4 mt-1">
                                <i class="ri-check-line ri-lg"></i>
                            </div>
                            <p class="text-gray-700">We pick up, we clean, we deliver. Simple as that. And yes, we're always on time.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Services List -->
    <section id="services" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Our Premium Services</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">Choose the service that fits your needs perfectly. All services include free pick-up and delivery.</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <div class="h-48 bg-gray-100 relative">
                        <div style="background-image: url('https://readdy.ai/api/search-image?query=freshly%20ironed%20and%20folded%20clothes%20stacked%20neatly%2C%20professional%20laundry%20service%2C%20crisp%20clean%20shirts%20and%20pants%2C%20premium%20quality%2C%20soft%20lighting%2C%20minimalist%20aesthetic&width=500&height=300&seq=3&orientation=landscape');" class="absolute inset-0 bg-cover bg-center"></div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Wash, Dry, Iron & Fold</h3>
                        <p class="text-gray-600 mb-6">Complete laundry service with professional ironing for a crisp, polished finish. Perfect for business attire and formal wear.</p>
                    </div>
                </div>
                
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <div class="h-48 bg-gray-100 relative">
                        <div style="background-image: url('https://readdy.ai/api/search-image?query=neatly%20folded%20clean%20laundry%20in%20stacks%2C%20fresh%20towels%20and%20clothes%2C%20no%20ironing%2C%20soft%20fabrics%2C%20bright%20clean%20environment%2C%20professional%20laundry%20service&width=500&height=300&seq=4&orientation=landscape');" class="absolute inset-0 bg-cover bg-center"></div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Wash, Dry & Fold</h3>
                        <p class="text-gray-600 mb-6">Clean, fresh laundry perfectly folded and ready to put away. Ideal for everyday clothes, towels, and bedding.</p>
                    </div>
                </div>
                
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <div class="h-48 bg-gray-100 relative">
                        <div style="background-image: url('https://readdy.ai/api/search-image?query=professional%20delivery%20person%20in%20uniform%20handing%20over%20packaged%20clean%20laundry%20at%20doorstep%2C%20modern%20apartment%20building%2C%20convenient%20service%2C%20contactless%20delivery%2C%20clean%20packaging&width=500&height=300&seq=5&orientation=landscape');" class="absolute inset-0 bg-cover bg-center"></div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Pick-up & Delivery Service</h3>
                        <p class="text-gray-600 mb-6">Convenient door-to-door service. We collect your laundry and deliver it back clean and fresh at a time that suits you.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Call-to-Action -->
    <section id="book-now" class="py-20 bg-primary bg-opacity-5">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
                <div class="md:flex">
                    <div class="md:w-1/2 p-10">
                        <h2 class="text-3xl font-bold text-gray-900 mb-6">Ready for Fresh, Clean Clothes?</h2>
                        <p class="text-gray-600 mb-8">Book our services now and experience the convenience of professional laundry care. We're just a message away!</p>
                      
                        <div class="space-y-6">
                            <a href="https://wa.me/0745812730" class="flex items-center justify-center gap-3 bg-[#25D366] text-white px-6 py-3 !rounded-button font-medium hover:bg-opacity-90 transition-colors whitespace-nowrap w-full">
                                <i class="ri-whatsapp-fill ri-lg"></i>
                                <span>Book via WhatsApp</span>
                            </a>
                            
                            <div class="relative">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-gray-200"></div>
                                </div>
                                <div class="relative flex justify-center">
                                    <span class="bg-white px-4 text-sm text-gray-500">or call us</span>
                                </div>
                            </div>
                            
                            <a href="tel:0745812730" class="flex items-center justify-center gap-3 bg-white border border-gray-300 text-gray-700 px-6 py-3 !rounded-button font-medium hover:bg-gray-50 transition-colors whitespace-nowrap w-full">
                                <i class="ri-phone-line ri-lg"></i>
                                <span>0745 812 730</span>
                            </a>
                        </div>
                    </div>
                    
                    <div class="md:w-1/2 bg-gray-100 p-10 flex flex-col justify-center">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">Our Business Hours</h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Monday - Friday</span>
                                <span class="font-medium">7:00 AM - 8:00 PM</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Saturday</span>
                                <span class="font-medium">9:00 AM - 5:00 PM</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Sunday</span>
                                <span class="font-medium">Available Upon Request</span>
                            </div>
                        </div>
                        
                        <div class="mt-8 pt-8 border-t border-gray-200">
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">Delivery Schedule</h3>
                            <p class="text-gray-600">We offer same-day service for orders placed before 10 AM, and next-day delivery for all other orders.</p>
                            <button onclick="openPlaceOrderModal()" class="bg-primary text-white px-8 py-3 rounded-button font-medium hover:bg-primary/90 transition whitespace-nowrap flex items-center justify-center"> <i class="ri-calendar-line mr-2"></i>
                            Schedule Pickup</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Footer -->
<?php include 'includes/footer.php'; ?>
</body>
</html>