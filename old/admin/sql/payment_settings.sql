-- Create payment settings table
CREATE TABLE IF NOT EXISTS payment_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(100) NOT NULL,
    value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_setting (setting_name)
);

-- Insert default payment settings
INSERT INTO payment_settings (setting_name, value) VALUES
('paypal_client_id', ''),
('paypal_secret', ''),
('paypal_mode', 'sandbox'),
('stripe_publishable_key', ''),
('stripe_secret_key', ''),
('stripe_mode', 'test')
ON DUPLICATE KEY UPDATE value = VALUES(value); 