-- Newly filed tickets use Open; IT assignment moves them to In Progress.
ALTER TABLE `tbltickets`
MODIFY COLUMN `status` enum(
    'Open','Pending','In Progress','On Hold','Resolved','Closed',
    'Reopened','Unresolved','Decline','Approve','Cancelled'
) DEFAULT 'Open';

ALTER TABLE `tblticket_history`
MODIFY COLUMN `old_status` enum(
    'Open','Pending','In Progress','On Hold','Resolved','Closed',
    'Reopened','Unresolved','Cancelled'
) DEFAULT NULL,
MODIFY COLUMN `new_status` enum(
    'Open','Pending','In Progress','On Hold','Resolved','Closed',
    'Reopened','Unresolved','Cancelled'
) DEFAULT NULL;

-- Assigned tickets that were still Pending should be In Progress.
UPDATE `tbltickets`
SET `status` = 'In Progress'
WHERE `status` = 'Pending'
  AND `assigned_to` IS NOT NULL
  AND `assigned_to` > 0;

-- Unassigned newly filed tickets become Open.
UPDATE `tbltickets`
SET `status` = 'Open'
WHERE `status` = 'Pending';

UPDATE `tblticket_history`
SET `old_status` = 'Open'
WHERE `old_status` = 'Pending';

UPDATE `tblticket_history`
SET `new_status` = 'Open'
WHERE `new_status` = 'Pending'
  AND `action_type` = 'Created';
