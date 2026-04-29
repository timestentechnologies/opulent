# M-Pesa Integration for PHP Applications

This package provides a complete M-Pesa STK Push integration for PHP applications. It includes both admin and customer-side implementations.

## Features

- M-Pesa STK Push integration
- Admin configuration interface
- Secure credential management
- Payment status tracking
- Callback handling
- Error logging
- Sandbox and Live modes

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- cURL extension
- M-Pesa API credentials (Consumer Key, Consumer Secret, Passkey, Shortcode)

## Installation

1. Copy the `mpesa_integration` directory to your project
2. Import the database schema:
   ```sql
   CREATE TABLE IF NOT EXISTS payment_settings (
       id INT AUTO_INCREMENT PRIMARY KEY,
       setting_name VARCHAR(100) NOT NULL,
       value TEXT,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
       UNIQUE KEY unique_setting (setting_name)
   );
   ```

3. Update your database connection details in `admin/connect.php`

## Configuration

1. Access the admin panel
2. Navigate to Payment Configuration
3. Enter your M-Pesa credentials:
   - Consumer Key
   - Consumer Secret
   - Pass Key
   - Business Short Code
4. Select mode (Sandbox/Live)
5. Save settings

## Usage

### Customer Side

1. Include the M-Pesa class:
   ```php
   require_once('includes/mpesa_stk_push.php');
   ```

2. Initialize payment:
   ```php
   $mpesa = new MpesaSTKPush(
       $consumer_key,
       $consumer_secret,
       $passkey,
       $shortcode,
       $mode
   );

   $response = $mpesa->initiateSTKPush(
       $phone_number,
       $amount,
       $account_reference,
       $transaction_desc
   );
   ```

### Callback Handling

1. Configure your M-Pesa callback URL to point to `mpesa_callback.php`
2. The callback handler will:
   - Process payment status
   - Update order status
   - Log transaction details

## Security

- All sensitive credentials are stored securely
- Input validation and sanitization
- Error logging
- Secure callback handling

## Error Handling

The integration includes comprehensive error handling:
- Invalid phone numbers
- API errors
- Network issues
- Invalid responses

## Support

For support, please contact:
- Email: support@example.com
- Phone: +254 XXX XXX XXX

## License

This project is licensed under the MIT License - see the LICENSE file for details. 