-- Retire Reopened and Unresolved as active ticket statuses.
-- Enum values are kept for historical rows in tblticket_history.

UPDATE `tbltickets`
SET `status` = 'In Progress'
WHERE `status` = 'Reopened'
  AND `assigned_to` IS NOT NULL
  AND `assigned_to` > 0;

UPDATE `tbltickets`
SET `status` = 'Open'
WHERE `status` = 'Reopened';

UPDATE `tbltickets`
SET `status` = 'On Hold'
WHERE `status` = 'Unresolved';
