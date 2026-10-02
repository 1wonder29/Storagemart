-- Adds the P1 "Critical" ticket priority and registers the OM (Operations Manager) role.
-- Safe to run more than once. Back up the database first.

-- 1) Allow 'Critical' (P1) as a ticket priority. Without this, saving a Critical ticket fails.
ALTER TABLE tbltickets
  MODIFY priority ENUM('Low','Medium','High','Critical') DEFAULT 'Low';

-- 2) Role registry rows. The app decides access from tblaccounts.usertype, so these rows are
--    informational; if tblroles does not exist on your server you can skip this part.
INSERT INTO tblroles (role_code, role_name, description, permissions)
SELECT 'OM', 'Operations Manager',
       'Operations monitoring; branch reassignment stays with the Operations Head',
       '["read", "create", "update", "create_tickets"]'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tblroles WHERE role_code = 'OM');

UPDATE tblroles SET role_name = 'Operations Head' WHERE role_code = 'HOM';
