<?php
/**
 * Test script to run the email processor from the command line
 * 
 * Usage: php test_email_processor.php
 */

echo "Starting email processor test...\n\n";

// Include the standalone processor
include 'process_pending_emails_standalone.php';

echo "\nEmail processor test completed.\n";
?> 