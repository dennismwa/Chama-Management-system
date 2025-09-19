-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 19, 2025 at 02:12 PM
-- Server version: 8.0.42
-- PHP Version: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vxjtgclw_Chama Group`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`vxjtgclw`@`localhost` PROCEDURE `CalculateLoanSchedule` (IN `loan_id` INT, IN `principal` DECIMAL(15,2), IN `interest_rate` DECIMAL(5,2), IN `tenure_months` INT, IN `start_date` DATE)   BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE monthly_payment DECIMAL(15,2);
    DECLARE monthly_interest_rate DECIMAL(10,6);
    DECLARE current_balance DECIMAL(15,2);
    DECLARE interest_amount DECIMAL(15,2);
    DECLARE principal_amount DECIMAL(15,2);
    DECLARE due_date DATE;
    
    -- Calculate monthly payment using reducing balance method
    SET monthly_interest_rate = interest_rate / 100 / 12;
    SET monthly_payment = principal * (monthly_interest_rate * POWER(1 + monthly_interest_rate, tenure_months)) / 
                         (POWER(1 + monthly_interest_rate, tenure_months) - 1);
    
    SET current_balance = principal;
    
    -- Clear existing schedule
    DELETE FROM `loan_repayment_schedule` WHERE `loan_id` = loan_id;
    
    -- Generate schedule
    WHILE i <= tenure_months DO
        SET due_date = DATE_ADD(start_date, INTERVAL i MONTH);
        SET interest_amount = current_balance * monthly_interest_rate;
        SET principal_amount = monthly_payment - interest_amount;
        SET current_balance = current_balance - principal_amount;
        
        -- Adjust for last payment
        IF i = tenure_months THEN
            SET principal_amount = principal_amount + current_balance;
            SET current_balance = 0;
            SET monthly_payment = principal_amount + interest_amount;
        END IF;
        
        INSERT INTO `loan_repayment_schedule` 
        (`loan_id`, `installment_number`, `due_date`, `principal_amount`, `interest_amount`, `total_amount`, `balance_after_payment`)
        VALUES 
        (loan_id, i, due_date, principal_amount, interest_amount, monthly_payment, current_balance);
        
        SET i = i + 1;
    END WHILE;
END$$

CREATE DEFINER=`vxjtgclw`@`localhost` PROCEDURE `ProcessLoanPayment` (IN `loan_id` INT, IN `payment_amount` DECIMAL(15,2), IN `payment_date` DATE, IN `processed_by` INT)   BEGIN
    DECLARE current_balance DECIMAL(15,2);
    DECLARE remaining_amount DECIMAL(15,2);
    DECLARE installment_id INT;
    DECLARE installment_due DECIMAL(15,2);
    DECLARE installment_paid DECIMAL(15,2);
    DECLARE done INT DEFAULT FALSE;
    
    -- Cursor for unpaid installments
    DECLARE installment_cursor CURSOR FOR 
        SELECT id, total_amount, amount_paid 
        FROM loan_repayment_schedule 
        WHERE loan_id = loan_id AND status IN ('Pending', 'Overdue', 'Partial')
        ORDER BY installment_number;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    SET remaining_amount = payment_amount;
    
    -- Open cursor and process payments
    OPEN installment_cursor;
    
    payment_loop: LOOP
        FETCH installment_cursor INTO installment_id, installment_due, installment_paid;
        
        IF done OR remaining_amount <= 0 THEN
            LEAVE payment_loop;
        END IF;
        
        SET current_balance = installment_due - installment_paid;
        
        IF remaining_amount >= current_balance THEN
            -- Full payment of this installment
            UPDATE loan_repayment_schedule 
            SET amount_paid = installment_due, 
                payment_date = payment_date,
                status = 'Paid'
            WHERE id = installment_id;
            
            SET remaining_amount = remaining_amount - current_balance;
        ELSE
            -- Partial payment
            UPDATE loan_repayment_schedule 
            SET amount_paid = installment_paid + remaining_amount,
                payment_date = payment_date,
                status = 'Partial'
            WHERE id = installment_id;
            
            SET remaining_amount = 0;
        END IF;
    END LOOP;
    
    CLOSE installment_cursor;
    
    -- Update loan balance
    UPDATE loans 
    SET balance = balance - (payment_amount - remaining_amount),
        principal_paid = principal_paid + (payment_amount - remaining_amount),
        last_payment_date = payment_date,
        payments_made = payments_made + 1
    WHERE id = loan_id;
    
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int NOT NULL,
  `chama_group_id` int NOT NULL,
  `account_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_type` enum('Asset','Liability','Equity','Income','Expense') COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_account_id` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_system_account` tinyint(1) DEFAULT '0',
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `chama_group_id`, `account_code`, `account_name`, `account_type`, `parent_account_id`, `description`, `is_system_account`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, '1000', 'Cash Account', 'Asset', NULL, 'Main cash account for the chama', 1, 'Active', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(2, 1, '1001', 'Member Savings', 'Asset', NULL, 'Member savings accounts', 1, 'Active', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(3, 1, '1002', 'Loans Receivable', 'Asset', NULL, 'Outstanding loans to members', 1, 'Active', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(4, 1, '1003', 'Interest Receivable', 'Asset', NULL, 'Accrued interest on loans', 1, 'Active', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(5, 1, '2000', 'Member Deposits', 'Liability', NULL, 'Member deposit liabilities', 1, 'Active', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(6, 1, '3000', 'Retained Earnings', 'Equity', NULL, 'Accumulated profits/losses', 1, 'Active', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(7, 1, '4000', 'Interest Income', 'Income', NULL, 'Interest earned from loans', 1, 'Active', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(8, 1, '4001', 'Membership Fees', 'Income', NULL, 'Membership registration fees', 1, 'Active', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(9, 1, '4002', 'Penalty Income', 'Income', NULL, 'Late payment penalties', 1, 'Active', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(10, 1, '5000', 'Operating Expenses', 'Expense', NULL, 'General operating expenses', 1, 'Active', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(11, 1, '5001', 'Bank Charges', 'Expense', NULL, 'Bank transaction charges', 1, 'Active', '2025-09-11 16:00:28', '2025-09-11 16:00:28');

-- --------------------------------------------------------

--
-- Table structure for table `audit_trail`
--

CREATE TABLE `audit_trail` (
  `id` int NOT NULL,
  `chama_group_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `record_id` int DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chama_groups`
--

CREATE TABLE `chama_groups` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `registration_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `meeting_day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meeting_frequency` enum('Weekly','Monthly','Quarterly') COLLATE utf8mb4_unicode_ci DEFAULT 'Monthly',
  `currency` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT 'KES',
  `min_savings_amount` decimal(15,2) DEFAULT '0.00',
  `loan_interest_rate` decimal(5,2) DEFAULT '2.50',
  `late_payment_penalty` decimal(5,2) DEFAULT '5.00',
  `status` enum('Active','Inactive','Suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chama_groups`
--

INSERT INTO `chama_groups` (`id`, `name`, `description`, `registration_number`, `phone`, `email`, `address`, `meeting_day`, `meeting_frequency`, `currency`, `min_savings_amount`, `loan_interest_rate`, `late_payment_penalty`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Demo Chama Group', 'Default demonstration chama group for testing and setup', 'REG001', '+254700000000', 'demo@chamagroup.com', 'Nairobi, Kenya', 'Friday', 'Monthly', 'KES', 500.00, 2.50, 5.00, 'Active', '2025-09-11 16:00:28', '2025-09-11 16:00:28');

-- --------------------------------------------------------

--
-- Table structure for table `error_logs`
--

CREATE TABLE `error_logs` (
  `id` int NOT NULL,
  `error_level` enum('DEBUG','INFO','WARNING','ERROR','CRITICAL') COLLATE utf8mb4_unicode_ci DEFAULT 'ERROR',
  `error_message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `error_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `line_number` int DEFAULT NULL,
  `stack_trace` text COLLATE utf8mb4_unicode_ci,
  `user_id` int DEFAULT NULL,
  `chama_group_id` int DEFAULT NULL,
  `request_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_method` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `session_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additional_data` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `financial_summary`
-- (See below for the actual view)
--
CREATE TABLE `financial_summary` (
`chama_group_id` int
,`chama_name` varchar(100)
,`total_assets` decimal(37,2)
,`total_liabilities` decimal(37,2)
,`total_loans_outstanding` decimal(37,2)
,`total_member_savings` decimal(37,2)
,`total_target_funds` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` int NOT NULL,
  `loan_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `application_id` int NOT NULL,
  `member_id` int NOT NULL,
  `loan_product_id` int NOT NULL,
  `principal_amount` decimal(15,2) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `tenure_months` int NOT NULL,
  `monthly_payment` decimal(15,2) NOT NULL,
  `total_payable` decimal(15,2) NOT NULL,
  `balance` decimal(15,2) NOT NULL,
  `interest_paid` decimal(15,2) DEFAULT '0.00',
  `principal_paid` decimal(15,2) DEFAULT '0.00',
  `penalties_paid` decimal(15,2) DEFAULT '0.00',
  `disbursement_date` date NOT NULL,
  `expected_completion_date` date NOT NULL,
  `actual_completion_date` date DEFAULT NULL,
  `next_payment_date` date NOT NULL,
  `last_payment_date` date DEFAULT NULL,
  `payments_made` int DEFAULT '0',
  `missed_payments` int DEFAULT '0',
  `status` enum('Active','Completed','Defaulted','Written Off') COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_applications`
--

CREATE TABLE `loan_applications` (
  `id` int NOT NULL,
  `application_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `member_id` int NOT NULL,
  `loan_product_id` int NOT NULL,
  `amount_requested` decimal(15,2) NOT NULL,
  `amount_approved` decimal(15,2) DEFAULT NULL,
  `tenure_months` int NOT NULL,
  `purpose` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `collateral_description` text COLLATE utf8mb4_unicode_ci,
  `collateral_value` decimal(15,2) DEFAULT NULL,
  `monthly_income` decimal(15,2) DEFAULT NULL,
  `other_loans` text COLLATE utf8mb4_unicode_ci,
  `application_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` int DEFAULT NULL,
  `review_date` timestamp NULL DEFAULT NULL,
  `review_notes` text COLLATE utf8mb4_unicode_ci,
  `approved_by` int DEFAULT NULL,
  `approval_date` timestamp NULL DEFAULT NULL,
  `disbursement_date` timestamp NULL DEFAULT NULL,
  `status` enum('Pending','Under Review','Approved','Rejected','Disbursed','Cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_guarantors`
--

CREATE TABLE `loan_guarantors` (
  `id` int NOT NULL,
  `loan_application_id` int NOT NULL,
  `guarantor_member_id` int NOT NULL,
  `guaranteed_amount` decimal(15,2) NOT NULL,
  `guarantor_savings_balance` decimal(15,2) DEFAULT NULL,
  `approval_status` enum('Pending','Approved','Declined') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `approval_date` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_products`
--

CREATE TABLE `loan_products` (
  `id` int NOT NULL,
  `chama_group_id` int NOT NULL,
  `product_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `min_amount` decimal(15,2) NOT NULL,
  `max_amount` decimal(15,2) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `interest_type` enum('Fixed','Reducing Balance') COLLATE utf8mb4_unicode_ci DEFAULT 'Reducing Balance',
  `max_tenure_months` int NOT NULL,
  `processing_fee_percentage` decimal(5,2) DEFAULT '0.00',
  `late_payment_penalty` decimal(5,2) DEFAULT '5.00',
  `collateral_required` tinyint(1) DEFAULT '0',
  `guarantors_required` int DEFAULT '0',
  `status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `loan_products`
--

INSERT INTO `loan_products` (`id`, `chama_group_id`, `product_name`, `description`, `min_amount`, `max_amount`, `interest_rate`, `interest_type`, `max_tenure_months`, `processing_fee_percentage`, `late_payment_penalty`, `collateral_required`, `guarantors_required`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Standard Loan', 'Standard loan product for members', 1000.00, 100000.00, 2.50, 'Reducing Balance', 12, 2.00, 5.00, 0, 2, 'Active', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(2, 1, 'Emergency Loan', 'Quick emergency loan for urgent needs', 500.00, 20000.00, 3.00, 'Fixed', 6, 1.00, 10.00, 0, 1, 'Active', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(3, 1, 'Business Loan', 'Loan for business investments', 5000.00, 500000.00, 2.00, 'Reducing Balance', 24, 2.50, 5.00, 1, 3, 'Active', '2025-09-11 16:00:28', '2025-09-11 16:00:28');

-- --------------------------------------------------------

--
-- Table structure for table `loan_repayment_schedule`
--

CREATE TABLE `loan_repayment_schedule` (
  `id` int NOT NULL,
  `loan_id` int NOT NULL,
  `installment_number` int NOT NULL,
  `due_date` date NOT NULL,
  `principal_amount` decimal(15,2) NOT NULL,
  `interest_amount` decimal(15,2) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `amount_paid` decimal(15,2) DEFAULT '0.00',
  `balance_after_payment` decimal(15,2) NOT NULL,
  `payment_date` date DEFAULT NULL,
  `days_overdue` int DEFAULT '0',
  `penalty_amount` decimal(15,2) DEFAULT '0.00',
  `status` enum('Pending','Paid','Overdue','Partial') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int NOT NULL,
  `chama_group_id` int NOT NULL,
  `member_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `occupation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `next_of_kin` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `relationship_to_kin` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `membership_date` date NOT NULL,
  `membership_fee_paid` decimal(15,2) DEFAULT '0.00',
  `status` enum('Active','Inactive','Suspended','Expelled') COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `member_savings`
--

CREATE TABLE `member_savings` (
  `id` int NOT NULL,
  `member_id` int NOT NULL,
  `account_id` int NOT NULL,
  `balance` decimal(15,2) DEFAULT '0.00',
  `total_deposits` decimal(15,2) DEFAULT '0.00',
  `total_withdrawals` decimal(15,2) DEFAULT '0.00',
  `interest_earned` decimal(15,2) DEFAULT '0.00',
  `last_transaction_date` timestamp NULL DEFAULT NULL,
  `status` enum('Active','Inactive','Frozen') COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `member_summary`
-- (See below for the actual view)
--
CREATE TABLE `member_summary` (
`id` int
,`chama_group_id` int
,`member_number` varchar(20)
,`full_name` varchar(100)
,`phone` varchar(20)
,`status` enum('Active','Inactive','Suspended','Expelled')
,`membership_date` date
,`savings_balance` decimal(15,2)
,`active_loans` bigint
,`total_loan_balance` decimal(37,2)
,`target_contributions` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `chama_group_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `member_id` int DEFAULT NULL,
  `notification_type` enum('Email','SMS','Push','System') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` enum('Low','Normal','High','Critical') COLLATE utf8mb4_unicode_ci DEFAULT 'Normal',
  `status` enum('Pending','Sent','Failed','Cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `attempts` int DEFAULT '0',
  `max_attempts` int DEFAULT '3',
  `sent_at` timestamp NULL DEFAULT NULL,
  `failed_reason` text COLLATE utf8mb4_unicode_ci,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_templates`
--

CREATE TABLE `notification_templates` (
  `id` int NOT NULL,
  `chama_group_id` int DEFAULT NULL,
  `template_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_type` enum('Email','SMS','Push','System') COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `variables` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification_templates`
--

INSERT INTO `notification_templates` (`id`, `chama_group_id`, `template_name`, `template_type`, `subject`, `content`, `variables`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Welcome Member', 'Email', 'Welcome to {{chama_name}}', 'Dear {{member_name}},\n\nWelcome to {{chama_name}}! Your membership has been approved.\n\nMember Number: {{member_number}}\nMembership Date: {{membership_date}}\n\nBest regards,\n{{chama_name}} Management', '[\"chama_name\", \"member_name\", \"member_number\", \"membership_date\"]', 1, '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(2, 1, 'Loan Approved', 'SMS', 'Loan Approved', 'Dear {{member_name}}, your loan application for KES {{loan_amount}} has been approved. Disbursement will be processed within 24 hours.', '[\"member_name\", \"loan_amount\"]', 1, '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(3, 1, 'Payment Reminder', 'SMS', 'Payment Due', 'Dear {{member_name}}, your loan payment of KES {{amount}} is due on {{due_date}}. Please make payment to avoid penalties.', '[\"member_name\", \"amount\", \"due_date\"]', 1, '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(4, 1, 'Payment Confirmation', 'SMS', 'Payment Received', 'Dear {{member_name}}, we have received your payment of KES {{amount}} on {{payment_date}}. Thank you!', '[\"member_name\", \"amount\", \"payment_date\"]', 1, '2025-09-11 16:00:28', '2025-09-11 16:00:28');

-- --------------------------------------------------------

--
-- Table structure for table `payment_callbacks`
--

CREATE TABLE `payment_callbacks` (
  `id` int NOT NULL,
  `payment_request_id` int DEFAULT NULL,
  `gateway_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `callback_type` enum('Success','Failure','Pending','Cancelled') COLLATE utf8mb4_unicode_ci NOT NULL,
  `gateway_transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `currency` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_desc` text COLLATE utf8mb4_unicode_ci,
  `raw_callback` json DEFAULT NULL,
  `processed` tinyint(1) DEFAULT '0',
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_gateways`
--

CREATE TABLE `payment_gateways` (
  `id` int NOT NULL,
  `chama_group_id` int NOT NULL,
  `gateway_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gateway_type` enum('M-Pesa','Stripe','PayPal','Bank','Other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `configuration` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_sandbox` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_requests`
--

CREATE TABLE `payment_requests` (
  `id` int NOT NULL,
  `request_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `chama_group_id` int NOT NULL,
  `member_id` int DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT 'KES',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_type` enum('Savings','Loan Repayment','Target Contribution','Membership Fee','Other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `related_id` int DEFAULT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `callback_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `return_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `status` enum('Pending','Processing','Completed','Failed','Expired','Cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `failure_reason` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_reports`
--

CREATE TABLE `saved_reports` (
  `id` int NOT NULL,
  `chama_group_id` int NOT NULL,
  `report_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `report_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `parameters` json DEFAULT NULL,
  `schedule` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipients` json DEFAULT NULL,
  `last_generated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `setting_group` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `description`, `created_at`, `updated_at`) VALUES
(1, 'app_name', 'Chama Management Platform', 'general', 'Application name', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(2, 'app_version', '1.0.0', 'general', 'Application version', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(3, 'timezone', 'Africa/Nairobi', 'general', 'Default timezone', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(4, 'currency', 'KES', 'general', 'Default currency', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(5, 'date_format', 'Y-m-d', 'general', 'Default date format', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(6, 'time_format', 'H:i:s', 'general', 'Default time format', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(7, 'pagination_limit', '20', 'general', 'Records per page', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(8, 'session_timeout', '3600', 'security', 'Session timeout in seconds', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(9, 'max_login_attempts', '5', 'security', 'Maximum login attempts', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(10, 'password_min_length', '8', 'security', 'Minimum password length', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(11, 'backup_frequency', 'daily', 'maintenance', 'Database backup frequency', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(12, 'maintenance_mode', '0', 'maintenance', 'Maintenance mode status', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(13, 'email_driver', 'smtp', 'email', 'Email driver', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(14, 'sms_driver', 'africastalking', 'sms', 'SMS driver', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(15, 'mpesa_environment', 'sandbox', 'payments', 'M-Pesa environment', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(16, 'interest_calculation_method', 'reducing_balance', 'loans', 'Default interest calculation method', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(17, 'loan_processing_fee', '2.5', 'loans', 'Default loan processing fee percentage', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(18, 'late_payment_penalty', '5.0', 'loans', 'Default late payment penalty percentage', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(19, 'member_savings_account', '1001', 'accounts', 'Default member savings account code', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(20, 'loan_receivable_account', '1002', 'accounts', 'Default loan receivable account code', '2025-09-11 16:00:28', '2025-09-11 16:00:28'),
(21, 'cash_account', '1000', 'accounts', 'Default cash account code', '2025-09-11 16:00:28', '2025-09-11 16:00:28');

-- --------------------------------------------------------

--
-- Table structure for table `targets`
--

CREATE TABLE `targets` (
  `id` int NOT NULL,
  `chama_group_id` int NOT NULL,
  `target_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `target_amount` decimal(15,2) NOT NULL,
  `current_amount` decimal(15,2) DEFAULT '0.00',
  `contribution_frequency` enum('Daily','Weekly','Monthly','One-time') COLLATE utf8mb4_unicode_ci DEFAULT 'Monthly',
  `min_contribution` decimal(15,2) DEFAULT '0.00',
  `start_date` date NOT NULL,
  `target_date` date NOT NULL,
  `actual_completion_date` date DEFAULT NULL,
  `interest_rate` decimal(5,2) DEFAULT '0.00',
  `allow_partial_withdrawal` tinyint(1) DEFAULT '0',
  `withdrawal_penalty` decimal(5,2) DEFAULT '0.00',
  `status` enum('Active','Completed','Paused','Cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `target_contributions`
--

CREATE TABLE `target_contributions` (
  `id` int NOT NULL,
  `target_id` int NOT NULL,
  `member_id` int NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `contribution_date` date NOT NULL,
  `payment_method` enum('Cash','M-Pesa','Bank Transfer','Cheque') COLLATE utf8mb4_unicode_ci DEFAULT 'Cash',
  `transaction_id` int DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `chama_group_id` int NOT NULL,
  `transaction_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_type` enum('Deposit','Withdrawal','Transfer','Loan Disbursement','Loan Repayment','Interest Payment','Fee','Penalty','Dividend') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `reference_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` enum('Cash','M-Pesa','Bank Transfer','Cheque','Card') COLLATE utf8mb4_unicode_ci DEFAULT 'Cash',
  `payment_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_account_id` int DEFAULT NULL,
  `to_account_id` int DEFAULT NULL,
  `member_id` int DEFAULT NULL,
  `loan_id` int DEFAULT NULL,
  `target_id` int DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_by` int DEFAULT NULL,
  `approved_by` int DEFAULT NULL,
  `approval_date` timestamp NULL DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Cancelled','Completed') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `receipt_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `transactions`
--
DELIMITER $$
CREATE TRIGGER `update_member_savings_balance` AFTER INSERT ON `transactions` FOR EACH ROW BEGIN
    IF NEW.transaction_type IN ('Deposit', 'Withdrawal') AND NEW.member_id IS NOT NULL THEN
        UPDATE member_savings ms
        JOIN members m ON ms.member_id = m.id
        SET ms.balance = CASE 
            WHEN NEW.transaction_type = 'Deposit' THEN ms.balance + NEW.amount
            WHEN NEW.transaction_type = 'Withdrawal' THEN ms.balance - NEW.amount
            ELSE ms.balance
        END,
        ms.total_deposits = CASE 
            WHEN NEW.transaction_type = 'Deposit' THEN ms.total_deposits + NEW.amount
            ELSE ms.total_deposits
        END,
        ms.total_withdrawals = CASE 
            WHEN NEW.transaction_type = 'Withdrawal' THEN ms.total_withdrawals + NEW.amount
            ELSE ms.total_withdrawals
        END,
        ms.last_transaction_date = NEW.transaction_date
        WHERE ms.member_id = NEW.member_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `chama_group_id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('Super Admin','Admin','Manager','Treasurer','Secretary') COLLATE utf8mb4_unicode_ci DEFAULT 'Admin',
  `permissions` json DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int DEFAULT '0',
  `account_locked_until` timestamp NULL DEFAULT NULL,
  `password_reset_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `two_factor_secret` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT '0',
  `status` enum('Active','Inactive','Locked') COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `chama_group_id`, `username`, `email`, `password_hash`, `full_name`, `phone`, `role`, `permissions`, `avatar`, `last_login`, `login_attempts`, `account_locked_until`, `password_reset_token`, `password_reset_expires`, `two_factor_secret`, `two_factor_enabled`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'admin', 'admin@chamagroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', '+254700000000', 'Super Admin', '[\"all\"]', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, 'Active', '2025-09-11 16:00:28', '2025-09-11 16:00:28');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `audit_trail_users_update` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    INSERT INTO audit_trail (chama_group_id, user_id, action, table_name, record_id, old_values, new_values)
    VALUES (NEW.chama_group_id, NEW.id, 'UPDATE', 'users', NEW.id, 
            JSON_OBJECT('username', OLD.username, 'email', OLD.email, 'role', OLD.role, 'status', OLD.status),
            JSON_OBJECT('username', NEW.username, 'email', NEW.email, 'role', NEW.role, 'status', NEW.status));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_logs`
--

CREATE TABLE `user_activity_logs` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `chama_group_id` int DEFAULT NULL,
  `activity_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `session_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_account_code` (`chama_group_id`,`account_code`),
  ADD KEY `fk_accounts_chama_group` (`chama_group_id`),
  ADD KEY `fk_accounts_parent` (`parent_account_id`),
  ADD KEY `idx_account_type` (`account_type`);

--
-- Indexes for table `audit_trail`
--
ALTER TABLE `audit_trail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_audit_chama_group` (`chama_group_id`),
  ADD KEY `fk_audit_user` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_table_name` (`table_name`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `chama_groups`
--
ALTER TABLE `chama_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`name`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `error_logs`
--
ALTER TABLE `error_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_error_logs_user` (`user_id`),
  ADD KEY `fk_error_logs_chama_group` (`chama_group_id`),
  ADD KEY `idx_error_level` (`error_level`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_loan_number` (`loan_number`),
  ADD KEY `fk_loans_application` (`application_id`),
  ADD KEY `fk_loans_member` (`member_id`),
  ADD KEY `fk_loans_product` (`loan_product_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_next_payment_date` (`next_payment_date`),
  ADD KEY `idx_loans_member_status` (`member_id`,`status`);

--
-- Indexes for table `loan_applications`
--
ALTER TABLE `loan_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_application_number` (`application_number`),
  ADD KEY `fk_loan_applications_member` (`member_id`),
  ADD KEY `fk_loan_applications_product` (`loan_product_id`),
  ADD KEY `fk_loan_applications_reviewed_by` (`reviewed_by`),
  ADD KEY `fk_loan_applications_approved_by` (`approved_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_application_date` (`application_date`);

--
-- Indexes for table `loan_guarantors`
--
ALTER TABLE `loan_guarantors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_guarantor_application` (`loan_application_id`,`guarantor_member_id`),
  ADD KEY `fk_guarantors_application` (`loan_application_id`),
  ADD KEY `fk_guarantors_member` (`guarantor_member_id`),
  ADD KEY `idx_approval_status` (`approval_status`);

--
-- Indexes for table `loan_products`
--
ALTER TABLE `loan_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_loan_products_chama_group` (`chama_group_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `loan_repayment_schedule`
--
ALTER TABLE `loan_repayment_schedule`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_loan_installment` (`loan_id`,`installment_number`),
  ADD KEY `fk_repayment_schedule_loan` (`loan_id`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_loan_schedule_due_status` (`due_date`,`status`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_member_number` (`chama_group_id`,`member_number`),
  ADD UNIQUE KEY `unique_phone` (`chama_group_id`,`phone`),
  ADD KEY `fk_members_chama_group` (`chama_group_id`),
  ADD KEY `fk_members_created_by` (`created_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_membership_date` (`membership_date`);
ALTER TABLE `members` ADD FULLTEXT KEY `full_name` (`full_name`,`phone`,`email`);

--
-- Indexes for table `member_savings`
--
ALTER TABLE `member_savings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_member_account` (`member_id`,`account_id`),
  ADD KEY `fk_savings_member` (`member_id`),
  ADD KEY `fk_savings_account` (`account_id`),
  ADD KEY `idx_balance` (`balance`),
  ADD KEY `idx_member_savings_member_balance` (`member_id`,`balance`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifications_chama_group` (`chama_group_id`),
  ADD KEY `fk_notifications_user` (`user_id`),
  ADD KEY `fk_notifications_member` (`member_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_notification_type` (`notification_type`),
  ADD KEY `idx_scheduled_at` (`scheduled_at`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notification_templates_chama_group` (`chama_group_id`),
  ADD KEY `idx_template_type` (`template_type`);

--
-- Indexes for table `payment_callbacks`
--
ALTER TABLE `payment_callbacks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payment_callbacks_request` (`payment_request_id`),
  ADD KEY `idx_gateway_transaction_id` (`gateway_transaction_id`),
  ADD KEY `idx_processed` (`processed`);

--
-- Indexes for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_gateway_chama` (`chama_group_id`,`gateway_name`),
  ADD KEY `fk_payment_gateways_chama_group` (`chama_group_id`),
  ADD KEY `idx_gateway_type` (`gateway_type`);

--
-- Indexes for table `payment_requests`
--
ALTER TABLE `payment_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_request_number` (`request_number`),
  ADD KEY `fk_payment_requests_chama_group` (`chama_group_id`),
  ADD KEY `fk_payment_requests_member` (`member_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_type` (`payment_type`),
  ADD KEY `idx_payment_requests_status_created` (`status`,`created_at`);

--
-- Indexes for table `saved_reports`
--
ALTER TABLE `saved_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_saved_reports_chama_group` (`chama_group_id`),
  ADD KEY `fk_saved_reports_created_by` (`created_by`),
  ADD KEY `idx_report_type` (`report_type`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_setting` (`setting_key`);

--
-- Indexes for table `targets`
--
ALTER TABLE `targets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_targets_chama_group` (`chama_group_id`),
  ADD KEY `fk_targets_created_by` (`created_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_target_date` (`target_date`),
  ADD KEY `idx_targets_group_status_date` (`chama_group_id`,`status`,`target_date`);

--
-- Indexes for table `target_contributions`
--
ALTER TABLE `target_contributions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_target_contributions_target` (`target_id`),
  ADD KEY `fk_target_contributions_member` (`member_id`),
  ADD KEY `fk_target_contributions_transaction` (`transaction_id`),
  ADD KEY `idx_contribution_date` (`contribution_date`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_transaction_number` (`chama_group_id`,`transaction_number`),
  ADD KEY `fk_transactions_chama_group` (`chama_group_id`),
  ADD KEY `fk_transactions_from_account` (`from_account_id`),
  ADD KEY `fk_transactions_to_account` (`to_account_id`),
  ADD KEY `fk_transactions_member` (`member_id`),
  ADD KEY `fk_transactions_loan` (`loan_id`),
  ADD KEY `fk_transactions_processed_by` (`processed_by`),
  ADD KEY `fk_transactions_approved_by` (`approved_by`),
  ADD KEY `idx_transaction_type` (`transaction_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_transaction_date` (`transaction_date`),
  ADD KEY `idx_payment_method` (`payment_method`),
  ADD KEY `idx_transactions_date_amount` (`transaction_date`,`amount`);
ALTER TABLE `transactions` ADD FULLTEXT KEY `description` (`description`,`reference_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_username` (`username`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `fk_users_chama_group` (`chama_group_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_activity_logs_user` (`user_id`),
  ADD KEY `fk_activity_logs_chama_group` (`chama_group_id`),
  ADD KEY `idx_activity_type` (`activity_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `audit_trail`
--
ALTER TABLE `audit_trail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chama_groups`
--
ALTER TABLE `chama_groups`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `error_logs`
--
ALTER TABLE `error_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_applications`
--
ALTER TABLE `loan_applications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_guarantors`
--
ALTER TABLE `loan_guarantors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_products`
--
ALTER TABLE `loan_products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `loan_repayment_schedule`
--
ALTER TABLE `loan_repayment_schedule`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `member_savings`
--
ALTER TABLE `member_savings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payment_callbacks`
--
ALTER TABLE `payment_callbacks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_requests`
--
ALTER TABLE `payment_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saved_reports`
--
ALTER TABLE `saved_reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `targets`
--
ALTER TABLE `targets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `target_contributions`
--
ALTER TABLE `target_contributions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure for view `financial_summary`
--
DROP TABLE IF EXISTS `financial_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`vxjtgclw`@`localhost` SQL SECURITY DEFINER VIEW `financial_summary`  AS SELECT `cg`.`id` AS `chama_group_id`, `cg`.`name` AS `chama_name`, coalesce(sum((case when (`a`.`account_type` = 'Asset') then `ms`.`balance` else 0 end)),0) AS `total_assets`, coalesce(sum((case when (`a`.`account_type` = 'Liability') then `ms`.`balance` else 0 end)),0) AS `total_liabilities`, coalesce(`loans_summary`.`total_loans_outstanding`,0) AS `total_loans_outstanding`, coalesce(`savings_summary`.`total_member_savings`,0) AS `total_member_savings`, coalesce(`targets_summary`.`total_target_funds`,0) AS `total_target_funds` FROM (((((`chama_groups` `cg` left join `accounts` `a` on((`cg`.`id` = `a`.`chama_group_id`))) left join `member_savings` `ms` on((`a`.`id` = `ms`.`account_id`))) left join (select `cg`.`id` AS `chama_group_id`,sum(`l`.`balance`) AS `total_loans_outstanding` from ((`chama_groups` `cg` join `members` `m` on((`cg`.`id` = `m`.`chama_group_id`))) join `loans` `l` on(((`m`.`id` = `l`.`member_id`) and (`l`.`status` = 'Active')))) group by `cg`.`id`) `loans_summary` on((`cg`.`id` = `loans_summary`.`chama_group_id`))) left join (select `cg`.`id` AS `chama_group_id`,sum(`ms`.`balance`) AS `total_member_savings` from ((`chama_groups` `cg` join `members` `m` on((`cg`.`id` = `m`.`chama_group_id`))) join `member_savings` `ms` on((`m`.`id` = `ms`.`member_id`))) group by `cg`.`id`) `savings_summary` on((`cg`.`id` = `savings_summary`.`chama_group_id`))) left join (select `targets`.`chama_group_id` AS `chama_group_id`,sum(`targets`.`current_amount`) AS `total_target_funds` from `targets` where (`targets`.`status` = 'Active') group by `targets`.`chama_group_id`) `targets_summary` on((`cg`.`id` = `targets_summary`.`chama_group_id`))) GROUP BY `cg`.`id`, `cg`.`name` ;

-- --------------------------------------------------------

--
-- Structure for view `member_summary`
--
DROP TABLE IF EXISTS `member_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`vxjtgclw`@`localhost` SQL SECURITY DEFINER VIEW `member_summary`  AS SELECT `m`.`id` AS `id`, `m`.`chama_group_id` AS `chama_group_id`, `m`.`member_number` AS `member_number`, `m`.`full_name` AS `full_name`, `m`.`phone` AS `phone`, `m`.`status` AS `status`, `m`.`membership_date` AS `membership_date`, coalesce(`ms`.`balance`,0) AS `savings_balance`, coalesce(`loan_summary`.`active_loans`,0) AS `active_loans`, coalesce(`loan_summary`.`total_loan_balance`,0) AS `total_loan_balance`, coalesce(`target_summary`.`target_contributions`,0) AS `target_contributions` FROM (((`members` `m` left join `member_savings` `ms` on((`m`.`id` = `ms`.`member_id`))) left join (select `loans`.`member_id` AS `member_id`,count(0) AS `active_loans`,sum(`loans`.`balance`) AS `total_loan_balance` from `loans` where (`loans`.`status` = 'Active') group by `loans`.`member_id`) `loan_summary` on((`m`.`id` = `loan_summary`.`member_id`))) left join (select `target_contributions`.`member_id` AS `member_id`,sum(`target_contributions`.`amount`) AS `target_contributions` from `target_contributions` group by `target_contributions`.`member_id`) `target_summary` on((`m`.`id` = `target_summary`.`member_id`))) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `fk_accounts_chama_group` FOREIGN KEY (`chama_group_id`) REFERENCES `chama_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_accounts_parent` FOREIGN KEY (`parent_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `audit_trail`
--
ALTER TABLE `audit_trail`
  ADD CONSTRAINT `fk_audit_chama_group` FOREIGN KEY (`chama_group_id`) REFERENCES `chama_groups` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `error_logs`
--
ALTER TABLE `error_logs`
  ADD CONSTRAINT `fk_error_logs_chama_group` FOREIGN KEY (`chama_group_id`) REFERENCES `chama_groups` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_error_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `fk_loans_application` FOREIGN KEY (`application_id`) REFERENCES `loan_applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_loans_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_loans_product` FOREIGN KEY (`loan_product_id`) REFERENCES `loan_products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_applications`
--
ALTER TABLE `loan_applications`
  ADD CONSTRAINT `fk_loan_applications_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_loan_applications_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_loan_applications_product` FOREIGN KEY (`loan_product_id`) REFERENCES `loan_products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_loan_applications_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `loan_guarantors`
--
ALTER TABLE `loan_guarantors`
  ADD CONSTRAINT `fk_guarantors_application` FOREIGN KEY (`loan_application_id`) REFERENCES `loan_applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_guarantors_member` FOREIGN KEY (`guarantor_member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_products`
--
ALTER TABLE `loan_products`
  ADD CONSTRAINT `fk_loan_products_chama_group` FOREIGN KEY (`chama_group_id`) REFERENCES `chama_groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_repayment_schedule`
--
ALTER TABLE `loan_repayment_schedule`
  ADD CONSTRAINT `fk_repayment_schedule_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `fk_members_chama_group` FOREIGN KEY (`chama_group_id`) REFERENCES `chama_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_members_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `member_savings`
--
ALTER TABLE `member_savings`
  ADD CONSTRAINT `fk_savings_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_savings_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_chama_group` FOREIGN KEY (`chama_group_id`) REFERENCES `chama_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notifications_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD CONSTRAINT `fk_notification_templates_chama_group` FOREIGN KEY (`chama_group_id`) REFERENCES `chama_groups` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payment_callbacks`
--
ALTER TABLE `payment_callbacks`
  ADD CONSTRAINT `fk_payment_callbacks_request` FOREIGN KEY (`payment_request_id`) REFERENCES `payment_requests` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  ADD CONSTRAINT `fk_payment_gateways_chama_group` FOREIGN KEY (`chama_group_id`) REFERENCES `chama_groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_requests`
--
ALTER TABLE `payment_requests`
  ADD CONSTRAINT `fk_payment_requests_chama_group` FOREIGN KEY (`chama_group_id`) REFERENCES `chama_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payment_requests_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `saved_reports`
--
ALTER TABLE `saved_reports`
  ADD CONSTRAINT `fk_saved_reports_chama_group` FOREIGN KEY (`chama_group_id`) REFERENCES `chama_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_saved_reports_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `targets`
--
ALTER TABLE `targets`
  ADD CONSTRAINT `fk_targets_chama_group` FOREIGN KEY (`chama_group_id`) REFERENCES `chama_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_targets_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `target_contributions`
--
ALTER TABLE `target_contributions`
  ADD CONSTRAINT `fk_target_contributions_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_target_contributions_target` FOREIGN KEY (`target_id`) REFERENCES `targets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_target_contributions_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transactions_chama_group` FOREIGN KEY (`chama_group_id`) REFERENCES `chama_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_transactions_from_account` FOREIGN KEY (`from_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transactions_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_transactions_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transactions_to_account` FOREIGN KEY (`to_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_chama_group` FOREIGN KEY (`chama_group_id`) REFERENCES `chama_groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD CONSTRAINT `fk_activity_logs_chama_group` FOREIGN KEY (`chama_group_id`) REFERENCES `chama_groups` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
