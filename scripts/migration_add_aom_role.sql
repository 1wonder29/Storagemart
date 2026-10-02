-- Migration: Add AOM (Area Operation Manager) Role Support
-- Date: 2026-05-12
-- Description: Adds support for AOM role with branch assignments

-- ============================================
-- Table: tblroles
-- Purpose: Define available roles in the system
-- ============================================
CREATE TABLE IF NOT EXISTS `tblroles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_code` varchar(50) NOT NULL UNIQUE,
  `role_name` varchar(100) NOT NULL,
  `description` text,
  `permissions` json,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`),
  KEY `role_code` (`role_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default roles
INSERT INTO `tblroles` (`role_code`, `role_name`, `description`, `permissions`) VALUES
('ADMIN', 'Administrator', 'Full system access', '["create", "read", "update", "delete", "approve", "assign"]'),
('EMPLOYEE', 'Employee', 'Standard employee access', '["read", "create_own", "update_own"]'),
('HEAD', 'Department Head', 'Department management and oversight', '["read", "create", "update", "approve"]'),
('HR', 'Human Resources', 'HR management and employee administration', '["read", "create", "update", "delete"]'),
('IT', 'IT Support', 'IT ticket management', '["read", "assign", "update", "resolve"]'),
('AOM', 'Area Operation Manager', 'Branch-specific operations management', '["read", "create", "update", "manage_employees", "create_tickets"]');

-- ============================================
-- Table: tblbranch_assignments
-- Purpose: Define which branches are assigned to AOMs
-- ============================================
CREATE TABLE IF NOT EXISTS `tblbranch_assignments` (
  `assignment_id` int(11) NOT NULL AUTO_INCREMENT,
  `aom_employee_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `assignment_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT 1,
  `assigned_by` int(11),
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`assignment_id`),
  UNIQUE KEY `unique_aom_branch` (`aom_employee_id`, `branch_id`),
  KEY `aom_employee_id` (`aom_employee_id`),
  KEY `branch_id` (`branch_id`),
  FOREIGN KEY (`aom_employee_id`) REFERENCES `tblemployee`(`employee_id`) ON DELETE CASCADE,
  FOREIGN KEY (`branch_id`) REFERENCES `tblbranch`(`branch_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Alter tblaccounts: Add role_id column
-- Purpose: Link accounts to roles
-- ============================================
ALTER TABLE `tblaccounts` 
ADD COLUMN IF NOT EXISTS `role_id` int(11),
ADD KEY `role_id` (`role_id`),
ADD CONSTRAINT `fk_account_role` 
  FOREIGN KEY (`role_id`) REFERENCES `tblroles`(`role_id`) ON DELETE SET NULL;

-- ============================================
-- Alter tblemployee: Add role_id column
-- Purpose: Store employee role information
-- ============================================
ALTER TABLE `tblemployee` 
ADD COLUMN IF NOT EXISTS `role_id` int(11),
ADD KEY `employee_role_id` (`role_id`),
ADD CONSTRAINT `fk_employee_role` 
  FOREIGN KEY (`role_id`) REFERENCES `tblroles`(`role_id`) ON DELETE SET NULL;

-- ============================================
-- Update tbltickets: Add created_by_role column
-- Purpose: Track which role created the ticket
-- ============================================
ALTER TABLE `tbltickets`
ADD COLUMN IF NOT EXISTS `created_by_role` varchar(50) COMMENT 'Role of ticket creator (e.g., EMPLOYEE, AOM, HEAD)',
ADD COLUMN IF NOT EXISTS `aom_id` int(11) COMMENT 'AOM who created/manages this ticket',
ADD KEY `aom_id` (`aom_id`);

-- ============================================
-- Update tblbranch: Add contact and status columns
-- Purpose: Enhance branch information
-- ============================================
ALTER TABLE `tblbranch`
ADD COLUMN IF NOT EXISTS `manager_id` int(11) COMMENT 'Primary AOM or manager for this branch',
ADD COLUMN IF NOT EXISTS `contact_person` varchar(100),
ADD COLUMN IF NOT EXISTS `contact_email` varchar(100),
ADD COLUMN IF NOT EXISTS `contact_phone` varchar(20),
ADD COLUMN IF NOT EXISTS `status` enum('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
ADD COLUMN IF NOT EXISTS `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ============================================
-- Create view: vw_aom_branches
-- Purpose: Quick query of AOM assignments
-- ============================================
CREATE OR REPLACE VIEW `vw_aom_branches` AS
SELECT 
  ba.assignment_id,
  ba.aom_employee_id,
  e.firstname,
  e.lastname,
  e.email,
  ba.branch_id,
  b.branchCode,
  b.branchName,
  b.branchAddress,
  ba.is_active,
  ba.assignment_date,
  COUNT(DISTINCT emp.employee_id) as employee_count
FROM `tblbranch_assignments` ba
JOIN `tblemployee` e ON ba.aom_employee_id = e.employee_id
JOIN `tblbranch` b ON ba.branch_id = b.branch_id
LEFT JOIN `tblemployee` emp ON emp.branch_id = b.branch_id
WHERE ba.is_active = 1
GROUP BY ba.assignment_id, ba.aom_employee_id, ba.branch_id;

-- ============================================
-- Create index for better performance
-- ============================================
CREATE INDEX idx_account_usertype ON `tblaccounts`(`usertype`);
CREATE INDEX idx_employee_branch ON `tblemployee`(`branch_id`);
CREATE INDEX idx_ticket_status ON `tbltickets`(`status`);
CREATE INDEX idx_ticket_branch ON `tbltickets`(`branch_id`);
