-- Full-access flag: an ADMIN with is_superuser = 1 also gets the HR & Inventory,
-- IT, Operations, and Department Head pages inside the Admin sidebar.
-- Their role stays ADMIN; nothing changes for anyone else.

ALTER TABLE tblaccounts ADD COLUMN is_superuser TINYINT(1) NOT NULL DEFAULT 0;

-- Ma'am Janette Sumagaysay (General Manager)
UPDATE tblaccounts SET is_superuser = 1 WHERE account_id = 2200616 AND usertype = 'ADMIN';

-- Check: should list exactly one row (Janette, ADMIN, 1)
SELECT a.account_id, a.username, a.usertype, a.is_superuser, e.firstname, e.lastname
FROM tblaccounts a
LEFT JOIN tblemployee e ON e.account_id = a.account_id
WHERE a.is_superuser = 1;
