<?php
session_start();
require_once('admin/connect.php');

$sql_header_logo = "select * from manage_website"; 
$result_header_logo = $conn->query($sql_header_logo);
$row_header_logo = mysqli_fetch_array($result_header_logo);

// Include the success modal first
include 'success_modal.php';

// Include the contact modal
include 'contact_modal.php';

// Include the common order modal template
include 'order_modal.php';

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

// Get customer information
$customer_id = $_SESSION['customer_id'];
$stmt = $conn->prepare("SELECT * FROM customer WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

// Get current orders
$current_orders_stmt = $conn->prepare("
    SELECT o.*, s.sname as service_name, s.prize as service_price,
           (o.weight * s.prize) as total_price 
    FROM `order` o 
    JOIN service s ON o.service_id = s.id 
    WHERE o.customer_id = ? AND o.status != 'delivered' 
    ORDER BY o.created_at DESC
");
$current_orders_stmt->bind_param("i", $customer_id);
$current_orders_stmt->execute();
$current_orders = $current_orders_stmt->get_result();

// Get order history
$history_orders_stmt = $conn->prepare("
    SELECT o.*, s.sname as service_name, s.prize as service_price,
           (o.weight * s.prize) as total_price 
    FROM `order` o 
    JOIN service s ON o.service_id = s.id 
    WHERE o.customer_id = ? AND o.status = 'delivered' 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$history_orders_stmt->bind_param("i", $customer_id);
$history_orders_stmt->execute();
$history_orders = $history_orders_stmt->get_result();

// Function to get status color
function getStatusColor($status) {
    switch ($status) {
        case 'received':
            return 'bg-blue-100 text-blue-800';
        case 'cleaning':
            return 'bg-yellow-100 text-yellow-800';
        case 'processing':
            return 'bg-purple-100 text-purple-800';
        case 'in_transit':
            return 'bg-green-100 text-green-800';
        case 'delivered':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($customer['fname']); ?> Dashboard - Opulent Laundry Services</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="js/order.js"></script>
    <style>
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
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <?php include 'includes/navigation.php'; ?>


    <!-- Main Content -->
    <main class="container mx-auto px-6 py-8 max-w-7xl">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-primary to-primary/80 text-white rounded-lg p-8 mb-8">
            <h1 class="text-2xl font-bold">Welcome, <?php echo htmlspecialchars($customer['fname']); ?>!</h1>
            <p class="mt-2">Track your laundry orders and manage your account</p>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <button onclick="openPlaceOrderModal()" class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition flex items-center space-x-4">
                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                    <i class="ri-add-circle-line text-primary text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-medium text-gray-900">Place New Order</h3>
                    <p class="text-sm text-gray-600">Schedule a new laundry service</p>
                </div>
            </button>
            <a href="#orderHistory" class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition flex items-center space-x-4">
                <div class="w-12 h-12 bg-secondary/10 rounded-full flex items-center justify-center">
                    <i class="ri-history-line text-secondary text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-medium text-gray-900">Order History</h3>
                    <p class="text-sm text-gray-600">View your past orders</p>
                </div>
            </a>
            <a href="profile.php" class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition flex items-center space-x-4">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="ri-user-line text-purple-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-medium text-gray-900">Profile Settings</h3>
                    <p class="text-sm text-gray-600">Update your information</p>
                </div>
            </a>
        </div>

        <!-- Tracking Number Search -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Track Your Order</h2>
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

        <!-- Current Orders -->
        <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900">Current Orders</h2>
                <button onclick="openPlaceOrderModal()" class="text-primary hover:text-primary/80 font-medium">Place New Order</button>
            </div>
            <?php if ($current_orders->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pickup Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivery Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($order = $current_orders->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $order['tracking_number']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($order['description']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold <?php echo getStatusColor($order['status']); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('M d, Y', strtotime($order['pickup_date'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('M d, Y', strtotime($order['delivery_date'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">KES <?php echo number_format($order['total_price'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($order['payment_status'] === 'pending'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        <?php elseif ($order['payment_status'] === 'paid'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Paid
                                            </span>
                                        <?php else: ?>
                                            <button onclick="payOrder(<?php echo $order['id']; ?>, <?php echo $order['total_price']; ?>)" 
                                                    class="px-3 py-1 bg-primary text-white rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary">
                                                Pay
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="ri-inbox-line text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-600">No current orders found.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Order History -->
        <div id="orderHistory" class="bg-white rounded-lg shadow-sm p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Recent Order History</h2>
            <?php if ($history_orders->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivery Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($order = $history_orders->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#<?php echo $order['id']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($order['description']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">KES <?php echo number_format($order['total_price'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('M d, Y', strtotime($order['delivery_date'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php 
                                            echo $order['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : 
                                                ($order['payment_status'] === 'refunded' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'); ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="ri-history-line text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-600">No order history found.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Inquiries Section -->
        <section class="py-8">
            <div class="container mx-auto px-4">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">My Inquiries </h2>
                        
                        <?php
                        // Get customer email from session
                        $customer_email = $_SESSION['email'];
                        
                        // Fetch inquiries for the logged-in customer only using email
                        $sql_inquiries = "SELECT * FROM inquiries WHERE email = ? ORDER BY created_at DESC";
                        $stmt_inquiries = $conn->prepare($sql_inquiries);
                        $stmt_inquiries->bind_param("s", $customer_email);
                        $stmt_inquiries->execute();
                        $result_inquiries = $stmt_inquiries->get_result();
                        
                        if ($result_inquiries && $result_inquiries->num_rows > 0) {
                            echo '<div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Response</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">';
                        
                            while ($row = $result_inquiries->fetch_assoc()) {
                                $status_color = match($row['status']) {
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'responded' => 'bg-green-100 text-green-800',
                                    'closed' => 'bg-gray-100 text-gray-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                
                                echo '<tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . date('M d, Y', strtotime($row['created_at'])) . '</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($row['name']) . '</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div>Email: ' . htmlspecialchars($row['email']) . '</div>
                                        <div>Phone: ' . htmlspecialchars($row['phone']) . '</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">' . htmlspecialchars($row['message']) . '</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $status_color . '">
                                            ' . ucfirst($row['status']) . '
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">' . 
                                        (empty($row['response']) ? '-' : htmlspecialchars($row['response'])) . 
                                    '</td>
                                    
                                </tr>';
                            }
                            
                            echo '</tbody></table></div>';
                        } else {
                            echo '<p class="text-gray-500 text-center py-4">No inquiries found.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Subscription Plans Section -->
        <section class="py-8">
            <div class="container mx-auto px-4">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">My Subscription Plans</h2>
                        <?php
                        // Fetch subscription plans for the logged-in customer from user_subscriptions and subscription_plans tables
                        $sql_subscriptions = "SELECT us.*, sp.name as plan_name, sp.price, sp.duration, sp.description FROM user_subscriptions us JOIN subscription_plans sp ON us.plan_id = sp.id WHERE us.customer_id = ? ORDER BY us.created_at DESC";
                        $stmt_subscriptions = $conn->prepare($sql_subscriptions);
                        $stmt_subscriptions->bind_param("i", $customer_id);
                        $stmt_subscriptions->execute();
                        $result_subscriptions = $stmt_subscriptions->get_result();
                        
                        if ($result_subscriptions && $result_subscriptions->num_rows > 0) {
                            echo '<div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">';
                        
                            while ($plan = $result_subscriptions->fetch_assoc()) {
                                echo '<tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($plan['plan_name']) . '</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">KES ' . number_format($plan['price'], 2) . '</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($plan['duration']) . ' days</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . date('M d, Y', strtotime($plan['start_date'])) . '</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . date('M d, Y', strtotime($plan['end_date'])) . '</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($plan['status']) . '</td>
                                </tr>';
                            }
                            
                            echo '</tbody></table></div>';
                        } else {
                            echo '<p class="text-gray-500 text-center py-4">No subscription plans found.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </section>
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
                        <li><a href="services.php" class="text-gray-300 hover:text-white transition">Services</a></li>
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
                            <span class="text-gray-300">123 Laundry Lane, Suite 100<br>Cleanville, NY 10001</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-phone-line text-primary mr-3"></i>
                            <span class="text-gray-300">(555) 123-4567</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-mail-line text-primary mr-3"></i>
                            <span class="text-gray-300">info@timestenlaundry.com</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="pt-8 border-t border-white/10 text-center text-gray-300">
                <div class="flex justify-center space-x-6 mb-6">
                    <i class="ri-visa-fill text-2xl"></i>
                    <i class="ri-mastercard-fill text-2xl"></i>
                    <i class="ri-paypal-fill text-2xl"></i>
                    <i class="ri-apple-fill text-2xl"></i>
                    <i class="ri-google-fill text-2xl"></i>
                </div>
                <p>&copy; 2025 Timesten Laundry. All rights reserved. | <a href="#" class="hover:text-white transition">Privacy Policy</a> | <a href="#" class="hover:text-white transition">Terms of Service</a></p>
            </div>
        </div>
    </footer>

    <!-- Place Order Modal -->
    <div id="placeOrderModal" class="fixed inset-0 bg-black bg-opacity-50 z-[100] hidden">
        <div class="flex items-center justify-center min-h-screen p-6">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full relative">
                <div class="p-8">
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
                                <select name="service_id" id="service_id" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                    <option value="">Select a service</option>
                                    <?php
                                    $sql_services = "SELECT * FROM service";
                                    $result_services = $conn->query($sql_services);
                                    while ($service = $result_services->fetch_assoc()): ?>
                                        <option value="<?php echo $service['id']; ?>" data-price="<?php echo $service['prize']; ?>">
                                            <?php echo htmlspecialchars($service['sname']); ?> - $<?php echo number_format($service['prize'], 2); ?>/kg
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                                <input type="number" name="weight" id="weight" step="0.1" min="0.1" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pickup Date</label>
                                <input type="date" name="pickup_date" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Date</label>
                                <input type="date" name="delivery_date" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="description" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" rows="3" placeholder="Describe your laundry items..."></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                                <textarea name="notes" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" rows="2" placeholder="Any additional notes..."></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Total Price</label>
                                <input type="text" id="total_price" readonly class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50">
                            </div>
                        </div>
                        <div class="flex justify-end space-x-4">
                            <button type="button" onclick="closePlaceOrderModal()" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition">Place Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-6 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Process Payment</h3>
                
                <div class="mt-4 px-6 py-4">
                    <div class="payment-details mb-4">
                        <h4 class="font-medium text-gray-700">Order Details</h4>
                        <p class="text-sm text-gray-600">Order ID: <span id="paymentOrderId"></span></p>
                        <p class="text-sm text-gray-600">Amount: KES <span id="paymentAmount"></span></p>
                    </div>

                    <form id="paymentForm" class="space-y-4">
                        <input type="hidden" id="paymentOrderIdInput" name="order_id">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                            <select name="payment_method" id="paymentMethod" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary" required>
                                <option value="">Select Payment Method</option>
                                <option value="mpesa">M-Pesa STK Push</option>
                                <option value="mpesa_manual">M-Pesa Manual Payment</option>
                                <option value="paypal">PayPal</option>
                                <option value="stripe">Stripe</option>
                            </select>
                        </div>

                        <div id="mpesaDetails" class="hidden">
                            <div class="mb-4 p-4 bg-blue-50 rounded-md">
                                <h4 class="font-medium text-blue-800 mb-2">M-Pesa Payment Instructions</h4>
                                <ol class="text-sm text-blue-700 space-y-2">
                                    <li>1. Go to M-Pesa on your phone</li>
                                    <li>2. Select "Pay Bill"</li>
                                    <li>3. Enter Business Number: <span class="font-bold">123456</span></li>
                                    <li>4. Enter Account Number: <span class="font-bold" id="mpesaAccountNumber"></span></li>
                                    <li>5. Enter Amount: <span class="font-bold" id="mpesaAmount"></span></li>
                                    <li>6. Enter your M-Pesa PIN</li>
                                    <li>7. Complete the transaction</li>
                                </ol>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Safaricom Phone Number</label>
                                <input type="tel" name="mpesa_number" id="mpesaNumber" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary" placeholder="e.g., 254700000000">
                            </div>
                        </div>

                        <div id="mpesaManualDetails" class="hidden">
                            <div class="mb-4 p-4 bg-blue-50 rounded-md">
                                <h4 class="font-medium text-blue-800 mb-2">M-Pesa Manual Payment Instructions</h4>
                                <ol class="text-sm text-blue-700 space-y-2">
                                    <li>1. Go to M-Pesa on your phone</li>
                                    <li>2. Select "Pay Bill"</li>
                                    <li>3. Enter Business Number: <span class="font-bold">123456</span></li>
                                    <li>4. Enter Account Number: <span class="font-bold" id="mpesaManualAccountNumber"></span></li>
                                    <li>5. Enter Amount: <span class="font-bold" id="mpesaManualAmount"></span></li>
                                    <li>6. Enter your M-Pesa PIN</li>
                                    <li>7. Complete the transaction</li>
                                </ol>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Safaricom Phone Number</label>
                                <input type="tel" name="phone_number" id="manualMpesaNumber" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary" placeholder="e.g., 254700000000">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">M-Pesa Transaction Code</label>
                                <input type="text" name="mpesa_code" id="mpesaCode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary" placeholder="Enter the M-Pesa transaction code">
                                <p class="mt-1 text-xs text-gray-500">Enter the transaction code you received from M-Pesa</p>
                            </div>
                        </div>

                        <div id="paymentMessage" class="hidden">
                            <p class="text-sm"></p>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closePaymentModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Cancel
                            </button>
                            <button type="submit" id="submitPaymentBtn" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary">
                                Submit Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Response Modal -->
    <div id="responseModal" class="fixed inset-0 bg-black bg-opacity-50 z-[100] hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full relative">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-900">Respond to Inquiry</h3>
                        <button onclick="closeResponseModal()" class="text-gray-500 hover:text-gray-700">
                            <i class="ri-close-line text-2xl"></i>
                        </button>
                    </div>
                    <form id="responseForm" class="space-y-4">
                        <input type="hidden" name="inquiry_id" id="inquiryId">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="pending">Pending</option>
                                <option value="responded">Responded</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Response</label>
                            <textarea name="response" rows="4" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                                      placeholder="Enter your response"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition">
                                Save Response
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Track Order Form submission
        const trackOrderForm = document.getElementById('trackOrderForm');
        if (trackOrderForm) {
            trackOrderForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                // Show loading state
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;
                submitButton.disabled = true;
                submitButton.textContent = 'Tracking...';
                
                // Send request to track_order.php
                fetch('track_order.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Display results
                        const resultsDiv = document.getElementById('trackOrderResults');
                        resultsDiv.classList.remove('hidden');
                        
                        // Update result fields
                        const statusElement = document.getElementById('orderStatus');
                        statusElement.textContent = data.order.status;
                        
                        // Set status button color based on status
                        const statusColors = {
                            'Received': 'bg-blue-100 text-blue-800',
                            'Cleaning': 'bg-yellow-100 text-yellow-800',
                            'Processing': 'bg-purple-100 text-purple-800',
                            'In Transit': 'bg-green-100 text-green-800',
                            'Delivered': 'bg-gray-100 text-gray-800'
                        };
                        
                        // Remove all status color classes
                        statusElement.className = 'px-3 py-1 rounded-full text-sm font-medium';
                        // Add the appropriate color class
                        statusElement.classList.add(...statusColors[data.order.status].split(' '));
                        
                        document.getElementById('orderService').textContent = data.order.service;
                        document.getElementById('orderWeight').textContent = data.order.weight + ' kg';
                        document.getElementById('orderPickup').textContent = data.order.pickup_date;
                        document.getElementById('orderDelivery').textContent = data.order.delivery_date;
                        document.getElementById('orderPrice').textContent = 'KES ' + data.order.price;
                        
                        // Scroll to results
                        resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    } else {
                        alert(data.message || 'Order not found. Please check your tracking number and email.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while tracking your order. Please try again.');
                })
                .finally(() => {
                    // Reset button state
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                });
            });
        }

        const serviceSelect = document.getElementById('service_id');
        const weightInput = document.getElementById('weight');
        const totalPriceInput = document.getElementById('total_price');

        function calculatePrice() {
            const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
            const pricePerKg = parseFloat(selectedOption.dataset.price) || 0;
            const weight = parseFloat(weightInput.value) || 0;
            const totalPrice = (pricePerKg * weight).toFixed(2);
            totalPriceInput.value = 'KES ' + totalPrice;
        }

        serviceSelect.addEventListener('change', calculatePrice);
        weightInput.addEventListener('input', calculatePrice);

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
                        // Refresh the page after 3 seconds
                        setTimeout(() => {
                            window.location.reload();
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
            document.getElementById('contactModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        window.closeContactModal = function() {
            document.getElementById('contactModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Success Modal functions
        function showSuccessModal(data) {
            // Get the success modal elements
            const successModal = document.getElementById('successModal');
            const modalTitle = successModal.querySelector('h3');
            const modalContent = successModal.querySelector('.space-y-4');
            
            // Update modal title
            modalTitle.textContent = 'Order Placed Successfully!';
            
            // Update order details
            document.getElementById('successTrackingNumber').textContent = data.tracking_number;
            document.getElementById('successService').textContent = data.service_name;
            document.getElementById('successWeight').textContent = data.weight + ' kg';
            document.getElementById('successPrice').textContent = 'KES ' + data.price;
            document.getElementById('successPickupDate').textContent = data.pickup_date;
            document.getElementById('successDeliveryDate').textContent = data.delivery_date;
            
            // Show the modal
            successModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Auto-close after 3 seconds and refresh the page
            setTimeout(() => {
                closeSuccessModal();
                window.location.reload();
            }, 3000);
        }

        function closeSuccessModal() {
            const successModal = document.getElementById('successModal');
            successModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Handle contact form submission
        const contactForm = document.getElementById('contactForm');
        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                // Add your form submission logic here
                showSuccessModal('Thank you for your message. We will get back to you soon!');
                closeContactModal();
                this.reset();
            });
        }

        // Close modals when clicking outside
        document.querySelectorAll('.fixed.inset-0').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    if (this.id === 'contactModal') {
                        closeContactModal();
                    } else if (this.id === 'successModal') {
                        closeSuccessModal();
                    }
                }
            });
        });

        // Payment Modal functions
        function openPaymentModal(orderId, amount) {
            const modal = document.getElementById('paymentModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                document.getElementById('paymentOrderId').textContent = orderId;
                document.getElementById('paymentOrderIdInput').value = orderId;
                document.getElementById('paymentAmount').textContent = amount.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                // Set M-Pesa account numbers and amounts
                document.getElementById('mpesaAccountNumber').textContent = orderId;
                document.getElementById('mpesaAmount').textContent = amount.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                document.getElementById('mpesaManualAccountNumber').textContent = orderId;
                document.getElementById('mpesaManualAmount').textContent = amount.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        }

        function closePaymentModal() {
            const modal = document.getElementById('paymentModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
                document.getElementById('paymentForm').reset();
                document.getElementById('mpesaDetails').classList.add('hidden');
                document.getElementById('mpesaManualDetails').classList.add('hidden');
                document.getElementById('paymentMessage').classList.add('hidden');
            }
        }

        // Global function to handle pay button clicks
        window.payOrder = function(orderId, amount) {
            openPaymentModal(orderId, amount);
        };

        // Payment method change handler
        const paymentMethodSelect = document.getElementById('paymentMethod');
        if (paymentMethodSelect) {
            paymentMethodSelect.addEventListener('change', function() {
                const mpesaDetails = document.getElementById('mpesaDetails');
                const mpesaManualDetails = document.getElementById('mpesaManualDetails');
                const submitButton = document.getElementById('submitPaymentBtn');
                
                if (mpesaDetails && mpesaManualDetails && submitButton) {
                    // Hide all payment method details first
                    mpesaDetails.classList.add('hidden');
                    mpesaManualDetails.classList.add('hidden');
                    
                    // Remove required attribute from all inputs
                    document.querySelectorAll('input[type="tel"], input[type="text"]').forEach(input => {
                        input.removeAttribute('required');
                    });
                    
                    // Show the selected payment method details and set required attributes
                    if (this.value === 'mpesa') {
                        mpesaDetails.classList.remove('hidden');
                        document.getElementById('mpesaNumber').setAttribute('required', 'required');
                        submitButton.textContent = 'Proceed to Payment';
                    } else if (this.value === 'mpesa_manual') {
                        mpesaManualDetails.classList.remove('hidden');
                        document.getElementById('manualMpesaNumber').setAttribute('required', 'required');
                        document.getElementById('mpesaCode').setAttribute('required', 'required');
                        submitButton.textContent = 'Submit Payment';
                    } else {
                        submitButton.textContent = 'Proceed to Payment';
                    }
                }
            });
        }

        // Payment form submission handler
        const paymentForm = document.getElementById('paymentForm');
        if (paymentForm) {
            paymentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;
                const orderId = formData.get('order_id');
                
                // Debug: Log all form data
                console.log('Form Data:', {
                    paymentMethod: formData.get('payment_method'),
                    phoneNumber: formData.get('phone_number'),
                    mpesaCode: formData.get('mpesa_code'),
                    orderId: orderId
                });
                
                // Validate form before submission
                const paymentMethod = formData.get('payment_method');
                if (!paymentMethod) {
                    alert('Please select a payment method');
                    return;
                }
                
                if (paymentMethod === 'mpesa_manual') {
                    const phoneNumber = formData.get('phone_number');
                    const mpesaCode = formData.get('mpesa_code');
                    
                    // Debug: Log validation checks
                    console.log('Validation Checks:', {
                        hasPhoneNumber: !!phoneNumber,
                        hasMpesaCode: !!mpesaCode,
                        phoneNumberLength: phoneNumber?.length,
                        mpesaCodeLength: mpesaCode?.length
                    });
                    
                    if (!phoneNumber || !mpesaCode) {
                        alert('Please fill in all required fields:\n' + 
                              (!phoneNumber ? '- Phone number is required\n' : '') +
                              (!mpesaCode ? '- M-Pesa transaction code is required' : ''));
                        return;
                    }
                    
                    // Validate phone number format
                    if (!/^254[0-9]{9}$/.test(phoneNumber)) {
                        alert('Please enter a valid Safaricom phone number (e.g., 254700000000)');
                        return;
                    }
                    
                    // Validate M-Pesa code format
                    if (!/^[A-Z0-9]{10}$/.test(mpesaCode)) {
                        alert('Please enter a valid M-Pesa transaction code (10 alphanumeric characters)');
                        return;
                    }
                }
                
                // Add phone number to formData with the correct name
                if (paymentMethod === 'mpesa_manual') {
                    formData.set('mpesa_number', formData.get('phone_number'));
                }
                
                // Disable submit button while processing
                submitButton.disabled = true;
                submitButton.textContent = 'Submitting...';
                
                fetch('process_payment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Server Response:', data);
                    
                    const messageDiv = document.getElementById('paymentMessage');
                    messageDiv.classList.remove('hidden');
                    
                    if (data.success) {
                        if (formData.get('payment_method') === 'mpesa_manual') {
                            messageDiv.querySelector('p').textContent = 'Thank you for your payment! Please wait as we verify your payment.';
                            messageDiv.classList.add('text-green-600');
                            messageDiv.classList.remove('text-red-600');
                            
                            // Update the payment button to show pending status
                            const payButton = document.querySelector(`button[onclick="payOrder(${orderId}, ${formData.get('amount')})"]`);
                            if (payButton) {
                                const pendingBadge = document.createElement('span');
                                pendingBadge.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800';
                                pendingBadge.textContent = 'Pending';
                                payButton.parentNode.replaceChild(pendingBadge, payButton);
                            }
                            
                            // Close modal after 3 seconds
                            setTimeout(() => {
                                closePaymentModal();
                            }, 3000);
                        } else {
                            messageDiv.querySelector('p').textContent = data.message || 'Payment initiated successfully!';
                            messageDiv.classList.add('text-green-600');
                            messageDiv.classList.remove('text-red-600');
                        }
                    } else {
                        messageDiv.querySelector('p').textContent = data.message || 'Payment failed. Please try again.';
                        messageDiv.classList.add('text-red-600');
                        messageDiv.classList.remove('text-green-600');
                        // Re-enable the button if there's an error
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const messageDiv = document.getElementById('paymentMessage');
                    messageDiv.classList.remove('hidden');
                    messageDiv.classList.add('text-red-600');
                    messageDiv.querySelector('p').textContent = 'An error occurred. Please try again.';
                    // Re-enable the button if there's an error
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                });
            });
        }

        // Function to update payment status display
        function updatePaymentStatus(orderId, status) {
            const payButton = document.querySelector(`button[onclick="payOrder(${orderId}, ${amount})"]`);
            if (payButton) {
                const statusBadge = document.createElement('span');
                let className = '';
                
                switch(status) {
                    case 'pending':
                        className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800';
                        break;
                    case 'paid':
                        className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800';
                        break;
                    default:
                        return;
                }
                
                statusBadge.className = className;
                statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                payButton.parentNode.replaceChild(statusBadge, payButton);
            }
        }

        // Response Modal Functions
        function openResponseModal(inquiryId) {
            document.getElementById('inquiryId').value = inquiryId;
            document.getElementById('responseModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeResponseModal() {
            document.getElementById('responseModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('responseModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeResponseModal();
            }
        });

        // Handle response form submission
        document.getElementById('responseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            // Disable submit button and show loading state
            submitButton.disabled = true;
            submitButton.textContent = 'Saving...';
            
            // Send form data to server
            fetch('process_inquiry_response.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessModal(data.message);
                    closeResponseModal();
                    // Reload the page to show updated data
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showSuccessModal('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showSuccessModal('An error occurred while saving the response. Please try again.');
            })
            .finally(() => {
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            });
        });
    });
    </script>
</body>
</html>
