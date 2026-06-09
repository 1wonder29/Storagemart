-- Add Cancelled status to ticket tables
ALTER TABLE `tbltickets`
MODIFY COLUMN `status` enum(
    'Pending','In Progress','On Hold','Resolved','Closed',
    'Reopened','Unresolved','Decline','Approve','Cancelled'
) DEFAULT 'Pending';

ALTER TABLE `tblticket_history`
MODIFY COLUMN `action_type` enum(
    'Approved','Created','Assigned','Updated','Resolved','Reopened',
    'Closed','On Hold','Unresolved','Cancelled'
) DEFAULT 'Updated',
MODIFY COLUMN `old_status` enum(
    'Pending','In Progress','On Hold','Resolved','Closed',
    'Reopened','Unresolved','Cancelled'
) DEFAULT NULL,
MODIFY COLUMN `new_status` enum(
    'Pending','In Progress','On Hold','Resolved','Closed',
    'Reopened','Unresolved','Cancelled'
) DEFAULT NULL;

-- Backfill action_type for rows created before action_type supported Cancelled
UPDATE `tblticket_history`
SET `action_type` = 'Cancelled'
WHERE `new_status` = 'Cancelled'
  AND (`action_type` IS NULL OR `action_type` = '' OR `action_type` = 'Updated');
