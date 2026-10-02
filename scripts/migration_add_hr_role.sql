-- ========================================
-- Migration: Add HR Role and Tables
-- Date: 2026-04-30
-- Purpose: Add HR role support for employee accountability tracking
-- ========================================

-- ========================================
-- 1. Create tbluniform_inventory table
-- ========================================
CREATE TABLE IF NOT EXISTS `tbluniform_inventory` (
  `uniform_id` int(11) NOT NULL AUTO_INCREMENT,
  `uniform_type` varchar(100) NOT NULL COMMENT 'e.g., Polo Shirt, ID Badge, Hat, etc.',
  `size` varchar(20) NOT NULL COMMENT 'e.g., S, M, L, XL',
  `color` varchar(50) NOT NULL,
  `quantity_in_stock` int(11) NOT NULL DEFAULT 0,
  `cost_per_unit` decimal(10, 2) DEFAULT NULL,
  `supplier` varchar(150) DEFAULT NULL,
  `reorder_level` int(11) DEFAULT 5,
  `status` enum('ACTIVE','DISCONTINUED') NOT NULL DEFAULT 'ACTIVE',
  `createdby` varchar(50) NOT NULL,
  `datecreated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(50) DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uniform_id`),
  KEY `idx_status` (`status`),
  KEY `idx_uniform_type` (`uniform_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ========================================
-- 2. Create tbluniform_assignment table
-- ========================================
CREATE TABLE IF NOT EXISTS `tbluniform_assignment` (
  `assignment_id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `uniform_id` int(11) NOT NULL,
  `date_issued` date NOT NULL,
  `date_returned` date DEFAULT NULL,
  `quantity_issued` int(11) NOT NULL DEFAULT 1,
  `condition_upon_issue` varchar(100) DEFAULT NULL COMMENT 'e.g., New, Good, Fair',
  `condition_upon_return` varchar(100) DEFAULT NULL,
  `remarks` text,
  `createdby` varchar(50) NOT NULL,
  `datecreated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`assignment_id`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_uniform_id` (`uniform_id`),
  KEY `idx_date_issued` (`date_issued`),
  CONSTRAINT `fk_uniform_assignment_employee` FOREIGN KEY (`employee_id`) REFERENCES `tblemployee` (`employee_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_uniform_assignment_uniform` FOREIGN KEY (`uniform_id`) REFERENCES `tbluniform_inventory` (`uniform_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ========================================
-- 3. Create tblfir_logs table (optional audit trail)
-- ========================================
CREATE TABLE IF NOT EXISTS `tblfir_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(100) NOT NULL COMMENT 'VIEWED_EMPLOYEE, DOWNLOADED_FORM, ADDED_UNIFORM, EDITED_UNIFORM, DELETED_UNIFORM',
  `employee_id` int(11) DEFAULT NULL,
  `uniform_id` int(11) DEFAULT NULL,
  `performed_by` int(11) NOT NULL COMMENT 'account_id of HR user',
  `performed_role` varchar(20) NOT NULL DEFAULT 'HR',
  `details` text,
  `date_logged` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_performed_by` (`performed_by`),
  KEY `idx_date_logged` (`date_logged`),
  KEY `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ========================================
-- 4. Add test HR account (optional)
-- ========================================
-- Uncomment and adjust as needed:
-- INSERT INTO `tblaccounts` (`account_id`, `username`, `password`, `usertype`, `status`, `createdby`, `datecreated`)
-- VALUES (2200573, 'hr_user', '$2y$10$...', 'HR', 'ACTIVE', 'admin', NOW());

-- ========================================
-- 5. Sample uniform inventory data (optional)
-- ========================================
-- Uncomment to add sample data:
-- INSERT INTO `tbluniform_inventory` (`uniform_type`, `size`, `color`, `quantity_in_stock`, `cost_per_unit`, `supplier`, `reorder_level`, `status`, `createdby`, `datecreated`)
-- VALUES 
-- ('Polo Shirt', 'M', 'Blue', 50, 12.50, 'Supplier A', 10, 'ACTIVE', 'admin', NOW()),
-- ('Polo Shirt', 'L', 'Blue', 35, 12.50, 'Supplier A', 10, 'ACTIVE', 'admin', NOW()),
-- ('ID Badge', 'One Size', 'White/Blue', 100, 2.00, 'Supplier B', 20, 'ACTIVE', 'admin', NOW()),
-- ('Cap', 'One Size', 'Blue', 25, 5.00, 'Supplier C', 10, 'ACTIVE', 'admin', NOW());
