-- Seed Data: Add Sample OM (Operation Manager) Roles
-- Date: 2026-05-13
-- Description: Creates test OM accounts and sample assignments

-- ============================================
-- Add OM users to tblaccounts
-- ============================================

-- Note: These are example IDs. Adjust account_id values as needed
-- First, let's get the next available account ID and insert OM accounts

-- Create OM accounts (adjust IDs as needed for your database)
INSERT INTO `tblaccounts` (`account_id`, `username`, `password`, `usertype`, `status`, `createdby`, `datecreated`)
SELECT 
    MAX(account_id) + 1,
    'om_manager1',
    '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36DusJea', -- password: password123
    'OM',
    'ACTIVE',
    'admin',
    NOW()
FROM tblaccounts
ON DUPLICATE KEY UPDATE username = username;

INSERT INTO `tblaccounts` (`username`, `password`, `usertype`, `status`, `createdby`, `datecreated`)
VALUES 
('om_manager2', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36DusJea', 'OM', 'ACTIVE', 'admin', NOW()),
('om_manager3', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36DusJea', 'OM', 'ACTIVE', 'admin', NOW());

-- ============================================
-- Add OM employee records (if employee table is separate)
-- Uncomment if needed:
-- ============================================
-- INSERT INTO `tblemployee` (`firstname`, `lastname`, `email`, `position`, `department`, `usertype`)
-- VALUES 
-- ('Maria', 'Santos', 'maria.santos@storagemart.com', 'Operation Manager', 'Operations', 'OM'),
-- ('Carlos', 'Reyes', 'carlos.reyes@storagemart.com', 'Operation Manager', 'Operations', 'OM'),
-- ('Ana', 'Cruz', 'ana.cruz@storagemart.com', 'Operation Manager', 'Operations', 'OM');

-- ============================================
-- Sample employee assignments to AOMs
-- Note: Adjust employee_id, aom_id based on actual data
-- ============================================
-- Example assignments (uncomment and adjust IDs as needed):
-- INSERT INTO `tblom_employee_assignments` 
-- (`om_employee_id`, `employee_id`, `aom_id`, `notes`, `assigned_by`)
-- VALUES 
-- (1, 5, 10, 'Assigned for branch operations', 1),
-- (1, 6, 10, 'Assigned for branch operations', 1),
-- (2, 7, 11, 'New assignment for Q2', 2),
-- (2, 8, 11, 'Replacement assignment', 2);

-- ============================================
-- Verification queries
-- ============================================
-- Verify OM role exists in tblroles
SELECT * FROM tblroles WHERE role_code = 'OM';

-- Verify OM accounts created
SELECT account_id, username, usertype, status FROM tblaccounts WHERE usertype = 'OM' ORDER BY account_id DESC LIMIT 3;

-- Verify tables created
SHOW TABLES LIKE '%om_%';
