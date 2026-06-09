-- Migration: Rename OM (Operation Manager) to HOM (Head Of Operation)
-- Date: 2026-06-05
-- Description: Updates all OM references to HOM throughout the database schema

-- ============================================
-- Update Role in tblroles
-- ============================================
UPDATE `tblroles` 
SET `role_code` = 'HOM', 
    `role_name` = 'Head Of Operation',
    `description` = 'Manage employee assignments to AOMs',
    `permissions` = '["read", "create", "update", "assign_employees", "manage_aom_assignments"]'
WHERE `role_code` = 'OM';

-- ============================================
-- Update accounts usertype from OM to HOM
-- ============================================
UPDATE `tblaccounts`
SET `usertype` = 'HOM'
WHERE `usertype` = 'OM';

-- ============================================
-- Rename table: tblom_employee_assignments → tblhom_employee_assignments
-- ============================================
RENAME TABLE `tblom_employee_assignments` TO `tblhom_employee_assignments`;

-- ============================================
-- Update column names in the new table
-- ============================================
ALTER TABLE `tblhom_employee_assignments`
CHANGE COLUMN `om_employee_id` `hom_employee_id` INT(11) NOT NULL,
CHANGE COLUMN `assignment_date` `assignment_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
CHANGE COLUMN `created_at` `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
CHANGE COLUMN `updated_at` `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ============================================
-- Drop old indexes and create new ones with updated names
-- ============================================
ALTER TABLE `tblhom_employee_assignments`
DROP KEY `om_employee_id`,
DROP KEY `unique_employee_aom_assignment`;

ALTER TABLE `tblhom_employee_assignments`
ADD UNIQUE KEY `unique_employee_aom_assignment` (`employee_id`, `aom_id`),
ADD KEY `hom_employee_id` (`hom_employee_id`);

-- ============================================
-- Update foreign key constraints
-- ============================================
ALTER TABLE `tblhom_employee_assignments`
DROP FOREIGN KEY `tblom_employee_assignments_ibfk_1`;

ALTER TABLE `tblhom_employee_assignments`
ADD CONSTRAINT `tblhom_employee_assignments_ibfk_1` 
FOREIGN KEY (`hom_employee_id`) REFERENCES `tblemployee`(`employee_id`) ON DELETE CASCADE;

-- ============================================
-- Drop old views and recreate with new names
-- ============================================
DROP VIEW IF EXISTS `vw_om_assignments`;

CREATE OR REPLACE VIEW `vw_hom_assignments` AS
SELECT 
  oea.assignment_id,
  oea.hom_employee_id,
  hom.firstname as hom_firstname,
  hom.lastname as hom_lastname,
  hom.email as hom_email,
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
FROM `tblhom_employee_assignments` oea
JOIN `tblemployee` hom ON oea.hom_employee_id = hom.employee_id
JOIN `tblemployee` emp ON oea.employee_id = emp.employee_id
JOIN `tblemployee` aom ON oea.aom_id = aom.employee_id
WHERE oea.is_active = 1;

-- ============================================
-- Drop old indexes
-- ============================================
DROP INDEX IF EXISTS `idx_om_employee_id` ON `tblhom_employee_assignments`;
DROP INDEX IF EXISTS `idx_om_employee_active` ON `tblhom_employee_assignments`;

-- ============================================
-- Create new indexes with updated names
-- ============================================
CREATE INDEX `idx_hom_employee_id` ON `tblhom_employee_assignments`(`hom_employee_id`);
CREATE INDEX `idx_hom_employee_active` ON `tblhom_employee_assignments`(`hom_employee_id`, `is_active`);
