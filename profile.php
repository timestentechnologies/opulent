<?php
session_start();
require_once('admin/connect.php');

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$sql_header_logo = "select * from manage_website"; 
$result_header_logo = $conn->query($sql_header_logo);
$row_header_logo = mysqli_fetch_array($result_header_logo);

// Get customer information
$customer_id = $_SESSION['customer_id'];
$stmt = $conn->prepare("SELECT id, fname, lname, email, contact, address, city, state, zip_code FROM customer WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

if (!$customer) {
    die("Error: Customer not found");
}

// Debug connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Debug output
echo "<!-- Debug customer data: ";
var_export($customer);
echo " -->";

// Debug session
echo "<!-- Customer ID from session: " . $_SESSION['customer_id'] . " -->";

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zip_code = trim($_POST['zip_code']);

    $update_stmt = $conn->prepare("UPDATE customer SET fname=?, lname=?, contact=?, address=?, city=?, state=?, zip_code=? WHERE id=?");
    $update_stmt->bind_param("sssssssi", $fname, $lname, $contact, $address, $city, $state, $zip_code, $customer_id);

    if ($update_stmt->execute()) {
        $success = "Profile updated successfully!";
        // Refresh customer data
        $stmt->execute();
        $customer = $stmt->get_result()->fetch_assoc();
    } else {
        $error = "Failed to update profile. Please try again.";
    }
}

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
    <title>Profile - FreshPress Laundry Services</title>
    <link rel="icon" type="image/png" sizes="32x32" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
    <link rel="shortcut icon" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#4F46E5',secondary:'#10B981'},borderRadius:{'none':'0px','sm':'4px',DEFAULT:'8px','md':'12px','lg':'16px','xl':'20px','2xl':'24px','3xl':'32px','full':'9999px','button':'8px'}}}}</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <?php include 'includes/navigation.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Your Profile</h1>
                    <p class="text-gray-600 mt-2">View and update your personal information</p>
                </div>

                <?php if ($success): ?>
                    <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded mb-4">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-4">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" name="fname" value="<?php echo htmlspecialchars($customer['fname']); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" name="lname" value="<?php echo htmlspecialchars($customer['lname']); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" value="<?php echo htmlspecialchars($customer['email']); ?>" disabled class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50">
                        <p class="text-sm text-gray-500 mt-1">Email cannot be changed</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                        <input type="tel" name="contact" value="<?php echo htmlspecialchars($customer['contact']); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea name="address" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" rows="3"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <input type="text" name="city" value="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                            <input type="text" name="state" value="<?php echo htmlspecialchars($customer['state'] ?? ''); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ZIP Code</label>
                            <input type="text" name="zip_code" value="<?php echo htmlspecialchars($customer['zip_code'] ?? ''); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="dashboard.php" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">Cancel</a>
                        <button type="submit" class="px-6 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>

    <!-- Include necessary JavaScript files -->
    <script src="js/order.js"></script>
    <script src="js/contact.js"></script>
</body>
</html> 