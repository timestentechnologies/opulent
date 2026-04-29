<?php
session_start();
require_once('admin/connect.php');

// Check if user is logged in and is admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inquiry_id = $_POST['inquiry_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $response = $_POST['response'] ?? '';
    $updated_at = date('Y-m-d H:i:s');

    if (empty($inquiry_id) || empty($status)) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields'
        ]);
        exit;
    }

    try {
        // Update the inquiry
        $stmt = $conn->prepare("UPDATE inquiries SET status = ?, response = ?, updated_at = ? WHERE id = ?");
        $stmt->bind_param("sssi", $status, $response, $updated_at, $inquiry_id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Inquiry updated successfully'
            ]);
        } else {
            throw new Exception("Error updating inquiry");
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while updating the inquiry'
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 