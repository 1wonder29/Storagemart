-- Add Cancelled status to ticket tables
ALTER TABLE `tbltickets`
MODIFY COLUMN `status` enum(
    'Pending','In Progress','On Hold','Resolved','Closed',
    'Reopened','Unresolved','Decline','Approve','Cancelled'
) DEFAULT 'Pending';

ALTER TABLE `tblticket_history`
MODIFY COLUMN `old_status` enum(
    'Pending','In Progress','On Hold','Resolved','Closed',
    'Reopened','Unresolved','Cancelled'
) DEFAULT NULL,
MODIFY COLUMN `new_status` enum(
    'Pending','In Progress','On Hold','Resolved','Closed',
    'Reopened','Unresolved','Cancelled'
) DEFAULT NULL;
