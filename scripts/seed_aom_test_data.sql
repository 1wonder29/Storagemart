-- Seed Data Script for AOM Role Implementation
-- Date: 2026-05-12
-- Purpose: Initialize AOM roles and sample assignments

-- ============================================
-- 1. Insert sample roles (if not already exist)
-- ============================================
INSERT IGNORE INTO `tblroles` (`role_code`, `role_name`, `description`, `permissions`) VALUES
('ADMIN', 'Administrator', 'Full system access', '["create", "read", "update", "delete", "approve", "assign"]'),
('EMPLOYEE', 'Employee', 'Standard employee access', '["read", "create_own", "update_own"]'),
('HEAD', 'Department Head', 'Department management and oversight', '["read", "create", "update", "approve"]'),
('HR', 'Human Resources', 'HR management and employee administration', '["read", "create", "update", "delete"]'),
('IT', 'IT Support', 'IT ticket management', '["read", "assign", "update", "resolve"]'),
('AOM', 'Area Operation Manager', 'Branch-specific operations management', '["read", "create", "update", "manage_employees", "create_tickets"]');

-- ============================================
-- 2. Sample AOM Assignments
-- ============================================
-- Assuming these employees exist in tblemployee:
-- Employee ID: 230005133 (Julie An Tangunan) - position: Area Operations Manager
-- Employee ID: 230005338 (John Karl Jose) - position: Area Operations Manager
-- Employee ID: 230006059 (Jermalyn Revuelta) - position: Area Operations Manager

-- Assign AOM to multiple branches
INSERT IGNORE INTO `tblbranch_assignments` (
    `aom_employee_id`, 
    `branch_id`, 
    `assignment_date`, 
    `is_active`, 
    `assigned_by`
) VALUES
-- Julie Tangunan manages Yakal (17) and Fairview (15)
(230005133, 17, NOW(), 1, 2200426),
(230005133, 15, NOW(), 1, 2200426),

-- John Karl Jose manages Delta (11) and Katipunan (14)
(230005338, 11, NOW(), 1, 2200426),
(230005338, 14, NOW(), 1, 2200426),

-- Jermalyn Revuelta manages Eran (13) and Sucat (6)
(230006059, 13, NOW(), 1, 2200426),
(230006059, 6, NOW(), 1, 2200426);

-- ============================================
-- 3. Update branch manager information
-- ============================================
UPDATE `tblbranch` 
SET `manager_id` = 230005133
WHERE `branch_id` IN (15, 17);

UPDATE `tblbranch` 
SET `manager_id` = 230005338
WHERE `branch_id` IN (11, 14);

UPDATE `tblbranch` 
SET `manager_id` = 230006059
WHERE `branch_id` IN (6, 13);

-- ============================================
-- 4. Update accounts with AOM role (optional)
-- ============================================
-- If you want to set these employees' accounts to AOM role type
UPDATE `tblaccounts` 
SET `usertype` = 'AOM'
WHERE `account_id` IN (
    SELECT `account_id` FROM `tblemployee` 
    WHERE `employee_id` IN (230005133, 230005338, 230006059)
);

-- ============================================
-- 5. Verification queries to check setup
-- ============================================
-- View all AOM assignments
-- SELECT 
--     e.firstname, e.lastname, e.email, b.branchName, ba.assignment_date
-- FROM tblbranch_assignments ba
-- JOIN tblemployee e ON ba.aom_employee_id = e.employee_id
-- JOIN tblbranch b ON ba.branch_id = b.branch_id
-- WHERE ba.is_active = 1;

-- ============================================
-- 6. Sample Tickets Created by AOMs
-- ============================================
-- These will be auto-generated when AOMs create tickets through the UI
-- Sample query to view tickets created by AOMs:
-- SELECT 
--     t.ticket_number, e.firstname, e.lastname, b.branchName, t.status, t.date_filed
-- FROM tbltickets t
-- JOIN tblemployee e ON t.aom_id = e.employee_id
-- JOIN tblbranch b ON t.branch_id = b.branch_id
-- WHERE t.created_by_role = 'AOM'
-- ORDER BY t.date_filed DESC;
