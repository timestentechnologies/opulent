<?php
session_start();
require_once('admin/connect.php');

$sql_header_logo = "select * from manage_website"; 
$result_header_logo = $conn->query($sql_header_logo);
$row_header_logo = mysqli_fetch_array($result_header_logo);

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    // Validate inputs
    if (empty($name) || empty($phone) || empty($email) || empty($message)) {
        $error_message = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $error_message = 'Please enter a valid phone number.';
    } else {
        // Prepare and execute the insert query
        $stmt = $conn->prepare("INSERT INTO inquiries (name, phone, email, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $name, $phone, $email, $message);
        
        if ($stmt->execute()) {
            $success_message = 'Your message has been sent successfully! We will get back to you soon.';
            // Clear form data
            $name = $phone = $email = $message = '';
        } else {
            $error_message = 'An error occurred while sending your message. Please try again later.';
        }
        
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - FreshPress Laundry Services</title>
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
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Contact Us</h1>
                <p class="text-gray-600 mt-2">Have questions? We'd love to hear from you.</p>
            </div>

            <?php if ($success_message): ?>
                <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded mb-4">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-4">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <form method="POST" action="" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" name="name" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                                   placeholder="Enter your name"
                                   value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="tel" name="phone" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                                   placeholder="Enter your phone number"
                                   value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                               placeholder="Enter your email"
                               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea name="message" required rows="4" 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" 
                                  placeholder="Enter your message"><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition">
                            Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white shadow-sm mt-8">
        <div class="container mx-auto px-4 py-6">
            <p class="text-center text-gray-600">© <?php echo date('Y'); ?> FreshPress Laundry Services. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 