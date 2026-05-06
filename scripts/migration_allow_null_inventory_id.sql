-- Migration: Allow NULL inventory_id for general tickets (not asset-specific)
-- This allows employees to file general tickets without selecting a specific asset

ALTER TABLE `tbltickets` 
MODIFY COLUMN `inventory_id` int(11) NULL;

-- Verify the change
DESC `tbltickets`;
