-- Rename On Hold to Pending in active ticket workflow.

UPDATE `tbltickets`
SET `status` = 'Pending'
WHERE `status` = 'On Hold';

UPDATE `tblticket_history`
SET `old_status` = 'Pending'
WHERE `old_status` = 'On Hold';

UPDATE `tblticket_history`
SET `new_status` = 'Pending'
WHERE `new_status` = 'On Hold';
