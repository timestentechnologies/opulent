<?php
// Disable error output to browser
ini_set('display_errors', 0);
error_reporting(0);

// Start output buffering
ob_start();

// Clear any previous output
if (ob_get_level()) {
    ob_clean();
}

// Set JSON header
header('Content-Type: application/json');

// Create simple response
$response = [
    'success' => true,
    'message' => 'Test JSON response successful',
    'timestamp' => time()
];

// Output JSON
echo json_encode($response);

// End and flush the output buffer
ob_end_flush();
?> 