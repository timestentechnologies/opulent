-- Financial Management Tables for Opulent Laundry
-- Run this SQL to create the necessary tables

-- --------------------------------------------------------

--
-- Table structure for table `payments`
-- Tracks all payments received from customers and payments to suppliers
--

CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_type` enum('customer_payment','supplier_payment','refund','other') NOT NULL DEFAULT 'customer_payment',
  `customer_id` int(11) DEFAULT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','mpesa','bank_transfer','cheque','card','other') NOT NULL DEFAULT 'cash',
  `transaction_reference` varchar(100) DEFAULT NULL,
  `mpesa_code` varchar(20) DEFAULT NULL,
  `mpesa_number` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `payment_date` date NOT NULL,
  `recorded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `order_id` (`order_id`),
  KEY `invoice_id` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
-- Tracks all business expenses
--

CREATE TABLE IF NOT EXISTS `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expense_category` enum('rider_fee','salary','rent','utilities','supplies','equipment','maintenance','fuel','marketing','insurance','taxes','other') NOT NULL DEFAULT 'other',
  `employee_id` int(11) DEFAULT NULL,
  `description` varchar(500) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `payment_method` enum('cash','mpesa','bank_transfer','cheque','card') NOT NULL DEFAULT 'cash',
  `reference_number` varchar(100) DEFAULT NULL,
  `receipt_number` varchar(100) DEFAULT NULL,
  `vendor_name` varchar(255) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
-- Tracks invoices sent to customers
--

CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `balance_due` decimal(10,2) NOT NULL,
  `status` enum('draft','sent','paid','partial','overdue','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `customer_id` (`customer_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
-- Line items for invoices
--

CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `description` varchar(500) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_statements`
-- Tracks customer account statements (debits and credits)
--

CREATE TABLE IF NOT EXISTS `customer_statements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `transaction_type` enum('invoice','payment','credit','debit','refund') NOT NULL,
  `reference_id` int(11) NOT NULL,
  `reference_type` varchar(50) NOT NULL,
  `description` varchar(500) NOT NULL,
  `debit_amount` decimal(10,2) DEFAULT 0.00,
  `credit_amount` decimal(10,2) DEFAULT 0.00,
  `running_balance` decimal(10,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `transaction_date` (`transaction_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `owner_statements`
-- Tracks owner/partner account entries (money in and out)
--

CREATE TABLE IF NOT EXISTS `owner_statements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_type` enum('capital_injection','drawings','loan_given','loan_received','profit_share','expense_reimbursement','salary','dividend','other_in','other_out') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `entry_direction` enum('in','out') NOT NULL,
  `description` varchar(500) NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `recorded_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `transaction_date` (`transaction_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees` (for salary tracking)
--

CREATE TABLE IF NOT EXISTS `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_code` varchar(20) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(100) NOT NULL,
  `department` enum('operations','riders','admin','management','other') DEFAULT 'other',
  `salary` decimal(10,2) DEFAULT 0.00,
  `employment_type` enum('full_time','part_time','contract','casual') DEFAULT 'full_time',
  `start_date` date NOT NULL,
  `status` enum('active','inactive','on_leave','terminated') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_code` (`employee_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
-- Track suppliers for payments
--

CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(255) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `service_type` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample suppliers
INSERT INTO `suppliers` (`supplier_name`, `contact_person`, `service_type`, `status`) VALUES
('Detergent Supplies Ltd', 'John Doe', 'Cleaning Supplies', 'active'),
('Packaging Solutions', 'Jane Smith', 'Packaging Materials', 'active'),
('Equipment Maintenance Co', 'Mike Johnson', 'Equipment Repair', 'active');

-- Insert sample employees
INSERT INTO `employees` (`employee_code`, `first_name`, `last_name`, `position`, `department`, `salary`, `start_date`, `status`) VALUES
('EMP001', 'John', 'Kamau', 'Rider', 'riders', 25000.00, '2024-01-15', 'active'),
('EMP002', 'Mary', 'Wanjiku', 'Cleaner', 'operations', 20000.00, '2024-02-01', 'active'),
('EMP003', 'Peter', 'Ochieng', 'Supervisor', 'management', 35000.00, '2023-11-20', 'active');
