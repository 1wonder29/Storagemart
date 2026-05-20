-- Migration: Add OM (Operation Manager) Role Support
-- Date: 2026-05-13
-- Description: Adds support for OM role to handle employee assignments in AOM

-- ============================================
-- Insert OM role into tblroles
-- Purpose: Define the Operation Manager role
-- ============================================
INSERT INTO `tblroles` (`role_code`, `role_name`, `description`, `permissions`) VALUES
('OM', 'Operation Manager', 'Manage employee assignments to AOMs', '["read", "create", "update", "assign_employees", "manage_aom_assignments"]')
ON DUPLICATE KEY UPDATE 
  `role_name` = VALUES(`role_name`),
  `description` = VALUES(`description`),
  `permissions` = VALUES(`permissions`);

-- ============================================
-- Table: tblom_employee_assignments
-- Purpose: Track employee assignments to AOMs by OMs
-- ============================================
CREATE TABLE IF NOT EXISTS `tblom_employee_assignments` (
  `assignment_id` int(11) NOT NULL AUTO_INCREMENT,
  `om_employee_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `aom_id` int(11) NOT NULL,
  `assignment_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT 1,
  `notes` text,
  `assigned_by` int(11),
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`assignment_id`),
  UNIQUE KEY `unique_employee_aom_assignment` (`employee_id`, `aom_id`),
  KEY `om_employee_id` (`om_employee_id`),
  KEY `employee_id` (`employee_id`),
  KEY `aom_id` (`aom_id`),
  FOREIGN KEY (`om_employee_id`) REFERENCES `tblemployee`(`employee_id`) ON DELETE CASCADE,
  FOREIGN KEY (`employee_id`) REFERENCES `tblemployee`(`employee_id`) ON DELETE CASCADE,
  FOREIGN KEY (`aom_id`) REFERENCES `tblemployee`(`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Create view: vw_om_assignments
-- Purpose: Quick query of employee assignments managed by OMs
-- ============================================
CREATE OR REPLACE VIEW `vw_om_assignments` AS
SELECT 
  oea.assignment_id,
  oea.om_employee_id,
  om.firstname as om_firstname,
  om.lastname as om_lastname,
  om.email as om_email,
  oea.employee_id,
  emp.firstname as employee_firstname,
  emp.lastname as employee_lastname,
  emp.email as employee_email,
  emp.position,
  emp.department,
  oea.aom_id,
  aom.firstname as aom_firstname,
  aom.lastname as aom_lastname,
  aom.email as aom_email,
  oea.is_active,
  oea.assignment_date,
  oea.notes
FROM `tblom_employee_assignments` oea
JOIN `tblemployee` om ON oea.om_employee_id = om.employee_id
JOIN `tblemployee` emp ON oea.employee_id = emp.employee_id
JOIN `tblemployee` aom ON oea.aom_id = aom.employee_id
WHERE oea.is_active = 1;

-- ============================================
-- Create index for better performance
-- ============================================
CREATE INDEX idx_om_employee_id ON `tblom_employee_assignments`(`om_employee_id`);
CREATE INDEX idx_om_employee_active ON `tblom_employee_assignments`(`om_employee_id`, `is_active`);
