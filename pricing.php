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
<title>Pricing - Opulent Laundry Services</title>
<link rel="icon" type="image/png" sizes="32x32" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
<link rel="icon" type="image/png" sizes="16x16" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
<link rel="shortcut icon" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
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
<script src="js/order.js"></script>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opulent Laundry - Price List 2025</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#4f46e5',secondary:'#f59e0b'},borderRadius:{'none':'0px','sm':'4px',DEFAULT:'8px','md':'12px','lg':'16px','xl':'20px','2xl':'24px','3xl':'32px','full':'9999px','button':'8px'}}}}</script>
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
        .price-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .price-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
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
        :where([class^="ri-"])::before { content: "\f3c2"; }
        body {
        font-family: 'Inter', sans-serif;
        }
        </style>
</head>
<body class="bg-white min-h-screen">
<!-- Header -->
<?php include 'includes/navigation.php'; ?>


    <main class="container mx-auto px-6 py-12 max-w-6xl">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Clothing Services -->
            <div class="bg-white rounded-lg shadow-md p-6 price-card">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-3">
                        <i class="ri-t-shirt-line text-primary ri-lg"></i>
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-800">Clothing Services</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="py-3 text-left text-sm font-medium text-gray-500">Weight Range</th>
                                <th class="py-3 text-left text-sm font-medium text-gray-500">Service</th>
                                <th class="py-3 text-right text-sm font-medium text-gray-500">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 text-sm text-gray-700">1–4 kg</td>
                                <td class="py-3 text-sm text-gray-700">Wash, Dry & Fold</td>
                                <td class="py-3 text-sm text-gray-700 text-right font-medium">Ksh 125/kg</td>
                            </tr>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 text-sm text-gray-700">1–4 kg</td>
                                <td class="py-3 text-sm text-gray-700">Wash, Dry, Iron & Fold</td>
                                <td class="py-3 text-sm text-gray-700 text-right font-medium">Ksh 165/kg</td>
                            </tr>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 text-sm text-gray-700">5 kg and above</td>
                                <td class="py-3 text-sm text-gray-700">Wash, Dry & Fold</td>
                                <td class="py-3 text-sm text-gray-700 text-right font-medium">Ksh 100/kg</td>
                            </tr>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 text-sm text-gray-700">5 kg and above</td>
                                <td class="py-3 text-sm text-gray-700">Wash, Dry, Iron & Fold</td>
                                <td class="py-3 text-sm text-gray-700 text-right font-medium">Ksh 140/kg</td>
                            </tr>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 text-sm text-gray-700">Per item</td>
                                <td class="py-3 text-sm text-gray-700">Ironing Only (standalone)</td>
                                <td class="py-3 text-sm text-gray-700 text-right font-medium">Ksh 60/item</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-6 bg-amber-50 p-4 rounded-md border border-amber-100">
                    <div class="flex items-start">
                        <div class="w-6 h-6 flex items-center justify-center mr-3 mt-0.5">
                            <i class="ri-flashlight-line text-amber-500"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-amber-800">Express Same-Day Service</h3>
                            <p class="text-amber-700 text-sm">Additional Ksh 300 per order</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Home & Bedding -->
            <div class="bg-white rounded-lg shadow-md p-6 price-card">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-3">
                        <i class="ri-home-line text-primary ri-lg"></i>
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-800">Home & Bedding</h2>
                </div>
                
                <ul class="space-y-4">
                    <li class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <span class="text-gray-700">Bedsheets & Pillowcases</span>
                        <span class="font-medium text-gray-800">Ksh 120/kg</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <span class="text-gray-700">Duvets (All Sizes)</span>
                        <span class="font-medium text-gray-800">Ksh 500 each</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <span class="text-gray-700">Blankets (Light/Medium)</span>
                        <span class="font-medium text-gray-800">Ksh 400 each</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <span class="text-gray-700">Blankets (Heavy/Thick)</span>
                        <span class="font-medium text-gray-800">Ksh 700 each</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <span class="text-gray-700">Curtains</span>
                        <span class="font-medium text-gray-800">Ksh 150/kg</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <span class="text-gray-700">Mattress Covers</span>
                        <span class="font-medium text-gray-800">Ksh 250 each</span>
                    </li>
                    <li class="flex justify-between items-center pb-1">
                        <span class="text-gray-700">Cushion/Seat Covers</span>
                        <span class="font-medium text-gray-800">Ksh 250 per 6-piece set</span>
                    </li>
                </ul>
            </div>

            <!-- Delicate & Specialty Items -->
            <div class="bg-white rounded-lg shadow-md p-6 price-card">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-3">
                        <i class="ri-shirt-line text-primary ri-lg"></i>
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-800">Delicate & Specialty Items</h2>
                </div>
                
                <ul class="space-y-4">
                    <li class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <span class="text-gray-700">Baby Clothes</span>
                        <span class="font-medium text-gray-800">Ksh 100/kg</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <span class="text-gray-700">Shoes (Sneakers/Canvas)</span>
                        <span class="font-medium text-gray-800">Ksh 150/pair</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <span class="text-gray-700">Wedding Gown</span>
                        <span class="font-medium text-gray-800">Ksh 3,000</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <span class="text-gray-700">Suit Dry Cleaning</span>
                        <span class="font-medium text-gray-800">Ksh 600</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <span class="text-gray-700">Graduation Gown</span>
                        <span class="font-medium text-gray-800">Ksh 700</span>
                    </li>
                    <li class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <span class="text-gray-700">Delicate Garments (Silk, Lace, Chiffon)</span>
                        <span class="font-medium text-gray-800">Ksh 150/kg</span>
                    </li>
                    <li class="flex justify-between items-center pb-1">
                        <span class="text-gray-700">Stain Removal</span>
                        <span class="font-medium text-gray-800">Ksh 50/item (as needed)</span>
                    </li>
                </ul>
            </div>

            <!-- Pick-Up & Delivery + Monthly Subscription -->
            <div class="bg-white rounded-lg shadow-md p-6 price-card">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-3">
                        <i class="ri-truck-line text-primary ri-lg"></i>
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-800">Pick-Up & Delivery</h2>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-md mb-8">
                    <p class="text-gray-700">Delivery fees vary depending on your location. Please contact us for specific pricing in your area.</p>
                </div>

                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-3">
                        <i class="ri-calendar-check-line text-primary ri-lg"></i>
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-800">Monthly Subscription Packages</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="py-3 text-left text-sm font-medium text-gray-500">Plan Name</th>
                                <th class="py-3 text-left text-sm font-medium text-gray-500">Weight Limit</th>
                                <th class="py-3 text-left text-sm font-medium text-gray-500">Price/Month</th>
                                <th class="py-3 text-left text-sm font-medium text-gray-500">Includes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 text-sm font-medium text-primary">Student Saver</td>
                                <td class="py-3 text-sm text-gray-700">Up to 30kg</td>
                                <td class="py-3 text-sm text-gray-700">Ksh 2,000</td>
                                <td class="py-3 text-sm text-gray-700">Weekly pick-up + folding</td>
                            </tr>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 text-sm font-medium text-primary">Bachelor's Bundle</td>
                                <td class="py-3 text-sm text-gray-700">Up to 50kg</td>
                                <td class="py-3 text-sm text-gray-700">Ksh 4,000</td>
                                <td class="py-3 text-sm text-gray-700">Weekly pick-up + ironing</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 text-sm font-medium text-primary">Family Comfort Plan</td>
                                <td class="py-3 text-sm text-gray-700">Up to 100kg</td>
                                <td class="py-3 text-sm text-gray-700">Ksh 7,000</td>
                                <td class="py-3 text-sm text-gray-700">Multiple pick-ups + bedding & ironing</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <div class="bg-white py-12">
        <div class="container mx-auto px-6 max-w-4xl text-center">
            <h2 class="text-3xl font-semibold text-gray-800 mb-8">Ready to Experience Premium Laundry Service?</h2>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-10">
                <button onclick="openContactModal()" class="bg-primary text-white px-8 py-3 rounded-button font-medium hover:bg-primary/90 transition whitespace-nowrap flex items-center justify-center"><i class="ri-phone-line mr-2"></i>Contact Us</button>
                <button onclick="openPlaceOrderModal()" class="border border-primary text-primary px-8 py-3 rounded-button font-medium hover:bg-primary/5 transition whitespace-nowrap flex items-center justify-center"> <i class="ri-calendar-line mr-2"></i>
                Schedule Pickup</button>
            </div>
            
            <p class="text-gray-600 max-w-2xl mx-auto">
                Experience the difference with Opulent Laundry. We handle your clothes with care, ensuring they come back fresh, clean, and perfectly folded or ironed. Our team of professionals uses premium detergents and state-of-the-art equipment for best results.
            </p>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
</body>
</html>