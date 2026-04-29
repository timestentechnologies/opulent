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
    <title>Opulent Laundry Services</title>
    <link rel="icon" type="image/png" sizes="32x32" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
    <link rel="shortcut icon" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#4f46e5',secondary:'#8b5cf6'},borderRadius:{'none':'0px','sm':'4px',DEFAULT:'8px','md':'12px','lg':'16px','xl':'20px','2xl':'24px','3xl':'32px','full':'9999px','button':'8px'}}}}</script>
    <!-- Define login status for use in order.js -->
    <script>
    var user_logged_in = <?php echo isset($_SESSION['customer_id']) ? 'true' : 'false'; ?>;
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fcfcfd;
        }
        .heading {
            font-family: 'Playfair Display', serif;
        }
        .service-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            height: 20px;
            width: 20px;
            background-color: #fff;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            outline: none;
        }
        input[type="checkbox"]:checked {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }
        input[type="checkbox"]:checked::after {
            content: "✓";
            color: white;
            font-size: 14px;
        }
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e5e7eb;
            transition: .4s;
            border-radius: 34px;
        }
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .toggle-slider {
            background-color: #4f46e5;
        }
        input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }
    </style>
</head>
<body>
<?php include 'includes/navigation.php'; ?>

    <main>
        <section class="py-16 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="heading text-4xl md:text-5xl font-bold text-gray-900 mb-4">Opulent Laundry Services</h2>
                    <p class="text-lg text-gray-600 max-w-3xl mx-auto">Experience premium care for your garments with our comprehensive laundry solutions. From everyday essentials to delicate fabrics, we treat each item with meticulous attention.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Everyday Laundry -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-indigo-50 p-6 flex items-center justify-between">
                            <h3 class="heading text-xl font-bold text-gray-900">Everyday Laundry</h3>
                            <div class="w-10 h-10 bg-primary bg-opacity-10 rounded-full flex items-center justify-center">
                                <i class="ri-t-shirt-2-line text-primary ri-lg"></i>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-checkbox-circle-line text-primary ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Wash, Dry & Fold</h4>
                                        <p class="text-sm text-gray-600">Effortless care for your daily laundry.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-checkbox-circle-line text-primary ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Wash, Dry, Iron & Fold</h4>
                                        <p class="text-sm text-gray-600">Perfectly pressed, fresh-smelling clothes.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-checkbox-circle-line text-primary ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Ironing Only</h4>
                                        <p class="text-sm text-gray-600">Crisp finish for already-washed garments.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-time-line text-primary ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Express Same-Day Service</h4>
                                        <p class="text-sm text-gray-600">In a rush? Same-day cleaning & delivery.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-leaf-line text-primary ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Eco-Friendly Wash</h4>
                                        <p class="text-sm text-gray-600">Sustainable cleaning with biodegradable detergents.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Options -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-purple-50 p-6 flex items-center justify-between">
                            <h3 class="heading text-xl font-bold text-gray-900">Delivery Options</h3>
                            <div class="w-10 h-10 bg-secondary bg-opacity-10 rounded-full flex items-center justify-center">
                                <i class="ri-truck-line text-secondary ri-lg"></i>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-bike-line text-secondary ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Pick-Up & Delivery</h4>
                                        <p class="text-sm text-gray-600">We come to you, clean, and return fresh.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-calendar-check-line text-secondary ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Scheduled Pick-Up & Drop-Off</h4>
                                        <p class="text-sm text-gray-600">Set a time—reliable and punctual.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-message-2-line text-secondary ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">On-Demand Pick-Up</h4>
                                        <p class="text-sm text-gray-600">Message us anytime—we'll be there.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-home-smile-line text-secondary ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Door-to-Door Convenience</h4>
                                        <p class="text-sm text-gray-600">From your hands to ours and back.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-repeat-line text-secondary ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Weekly Subscription Plans</h4>
                                        <p class="text-sm text-gray-600">Regular laundry at discounted rates.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Garment Care -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-blue-50 p-6 flex items-center justify-between">
                            <h3 class="heading text-xl font-bold text-gray-900">Garment Care</h3>
                            <div class="w-10 h-10 bg-blue-500 bg-opacity-10 rounded-full flex items-center justify-center">
                                <i class="ri-shirt-line text-blue-500 ri-lg"></i>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-briefcase-4-line text-blue-500 ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Business & Office Wear</h4>
                                        <p class="text-sm text-gray-600">Sharp, clean, and presentation-ready.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-user-star-line text-blue-500 ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Formal & Event Wear</h4>
                                        <p class="text-sm text-gray-600">Gowns, tuxedos, traditional wear handled with care.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-hand-heart-line text-blue-500 ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Delicate Fabric Care</h4>
                                        <p class="text-sm text-gray-600">Silk, lace, chiffon—gentle cycles only.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-hand-coin-line text-blue-500 ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Hand Wash Service</h4>
                                        <p class="text-sm text-gray-600">Extra-soft care for special items.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-baby-line text-blue-500 ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Baby Clothes</h4>
                                        <p class="text-sm text-gray-600">Safe, fragrance-free, and skin-sensitive.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Home & Linen Cleaning -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-teal-50 p-6 flex items-center justify-between">
                            <h3 class="heading text-xl font-bold text-gray-900">Home & Linen</h3>
                            <div class="w-10 h-10 bg-teal-500 bg-opacity-10 rounded-full flex items-center justify-center">
                                <i class="ri-home-line text-teal-500 ri-lg"></i>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-hotel-bed-line text-teal-500 ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Bedsheets & Pillowcases</h4>
                                        <p class="text-sm text-gray-600">Crisp, hotel-style freshness.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-quill-pen-line text-teal-500 ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Duvets & Comforters</h4>
                                        <p class="text-sm text-gray-600">Deep-cleaned, fluffy, and cozy.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-curtain-line text-teal-500 ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Curtains & Drapes</h4>
                                        <p class="text-sm text-gray-600">Dust-free and neatly pressed.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-water-flash-line text-teal-500 ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Towels & Bathrobes</h4>
                                        <p class="text-sm text-gray-600">Soft, fresh, and fluffy.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="service-card transition-all duration-300 p-4 rounded-lg hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 flex items-center justify-center mr-3 mt-1">
                                        <i class="ri-table-line text-teal-500 ri-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Tablecloths & Napkins</h4>
                                        <p class="text-sm text-gray-600">Spotless and perfectly pressed.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Extra Services -->
                <div class="mt-16">
                    <h3 class="heading text-2xl font-bold text-gray-900 mb-8 text-center">Extra Services</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-rose-50 rounded-full flex items-center justify-center mr-4">
                                    <i class="ri-eraser-line text-rose-500 ri-lg"></i>
                                </div>
                                <h4 class="font-medium text-gray-900">Stain Removal</h4>
                            </div>
                            <p class="text-gray-600">Tough stains treated before the wash with specialized solutions for different fabric types.</p>
                        </div>
                        
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center mr-4">
                                    <i class="ri-drop-line text-blue-500 ri-lg"></i>
                                </div>
                                <h4 class="font-medium text-gray-900">Fabric Softener Add-On</h4>
                            </div>
                            <p class="text-gray-600">Premium softeners that leave your clothes feeling luxuriously soft and smelling fresh.</p>
                        </div>
                        
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-green-50 rounded-full flex items-center justify-center mr-4">
                                    <i class="ri-leaf-line text-green-500 ri-lg"></i>
                                </div>
                                <h4 class="font-medium text-gray-900">Fragrance-Free Wash</h4>
                            </div>
                            <p class="text-gray-600">Ideal for sensitive skin or allergies, using hypoallergenic detergents without added scents.</p>
                        </div>
                        
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-purple-50 rounded-full flex items-center justify-center mr-4">
                                    <i class="ri-virus-line text-purple-500 ri-lg"></i>
                                </div>
                                <h4 class="font-medium text-gray-900">Sanitization Wash</h4>
                            </div>
                            <p class="text-gray-600">High-temperature washing with antibacterial agents for complete elimination of germs and odors.</p>
                        </div>
                        
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-amber-50 rounded-full flex items-center justify-center mr-4">
                                    <i class="ri-scissors-line text-amber-500 ri-lg"></i>
                                </div>
                                <h4 class="font-medium text-gray-900">Minor Repairs</h4>
                            </div>
                            <p class="text-gray-600">Quick fixes for loose buttons, small tears, and minor alterations while your clothes are with us.</p>
                        </div>
                        
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-cyan-50 rounded-full flex items-center justify-center mr-4">
                                    <i class="ri-footprint-line text-cyan-500 ri-lg"></i>
                                </div>
                                <h4 class="font-medium text-gray-900">Shoe Cleaning</h4>
                            </div>
                            <p class="text-gray-600">Professional cleaning for sneakers, leather shoes, and boots to restore their original appearance.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        
       
        
    </main>

    <!-- Footer -->
<?php include 'includes/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle mobile menu
            const menuButton = document.querySelector('.ri-menu-line');
            if (menuButton) {
                menuButton.addEventListener('click', function() {
                    // Mobile menu toggle logic would go here
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Custom checkbox functionality
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Any additional checkbox logic can go here
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Service card hover effect
            const serviceCards = document.querySelectorAll('.service-card');
            serviceCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    // Additional hover effects can be added here
                });
                
                card.addEventListener('mouseleave', function() {
                    // Reset effects here
                });
            });
        });
    </script>
    <script src="js/order.js"></script>
    <script>
        // Ensure the success modal functions are properly defined
        // This will override any existing implementations to ensure consistency
        window.closeSuccessModal = function() {
            const successModal = document.getElementById('successModal');
            if (successModal) {
                successModal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        };

        // Make sure showSuccessModal is properly defined for this page
        window.showSuccessModal = function(data) {
            console.log('Services page showing success modal with data:', data);
            
            // Get the success modal elements
            const successModal = document.getElementById('successModal');
            if (!successModal) {
                console.error('Success modal not found');
                return;
            }
            
            const modalTitle = successModal.querySelector('h3');
            const successMessage = document.getElementById('successMessage');
            const orderDetails = document.getElementById('orderDetails');
            const iconContainer = successModal.querySelector('.w-16.h-16');
            const icon = iconContainer.querySelector('i');
            
            // Reset modal state
            if (successMessage) successMessage.classList.add('hidden');
            if (orderDetails) orderDetails.style.display = 'none';
            if (iconContainer) {
                iconContainer.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4';
                icon.className = 'text-3xl';
            }
            
            if (data.error) {
                // Error message
                if (modalTitle) modalTitle.textContent = 'Notice';
                if (successMessage) {
                    successMessage.classList.remove('hidden');
                    const messageP = successMessage.querySelector('p');
                    if (messageP) messageP.textContent = data.message || 'An error occurred.';
                }
                if (iconContainer) {
                    iconContainer.classList.add('bg-red-100');
                    icon.classList.add('ri-information-line', 'text-red-500');
                }
            } else if (data.success) {
                // Order success with details
                if (modalTitle) modalTitle.textContent = 'Order Placed Successfully!';
                if (orderDetails) orderDetails.style.display = 'block';
                if (iconContainer) {
                    iconContainer.classList.add('bg-green-100');
                    icon.classList.add('ri-check-line', 'text-green-500');
                }
                
                // Update order details
                const fields = [
                    {id: 'successTrackingNumber', value: data.tracking_number || ''},
                    {id: 'successService', value: data.service_name || ''},
                    {id: 'successWeight', value: data.weight ? data.weight + ' kg' : ''},
                    {id: 'successPrice', value: data.price ? 'KES ' + data.price : ''},
                    {id: 'successPickupDate', value: data.pickup_date || ''},
                    {id: 'successDeliveryDate', value: data.delivery_date || ''}
                ];
                
                fields.forEach(field => {
                    const element = document.getElementById(field.id);
                    if (element) element.textContent = field.value;
                });
            }
            
            // Show the modal
            successModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Auto-close after 3 seconds for success, 5 for errors
            const closeDelay = data.success ? 3000 : 5000;
            setTimeout(() => {
                closeSuccessModal();
                if (data.success) {
                    window.location.reload();
                }
            }, closeDelay);
        };
    </script>
</body>
</html>