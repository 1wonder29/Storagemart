-- ========================================
-- Migration: Add Condition Upon Return to tbluniform_assignment
-- Date: 2026-05-25
-- Purpose: Store the condition when uniform is returned
-- ========================================

-- Add condition_upon_return column to tbluniform_assignment
ALTER TABLE `tbluniform_assignment` 
ADD COLUMN `condition_upon_return` varchar(100) DEFAULT NULL COMMENT 'Condition upon return: GOOD, FAIR, USED, DAMAGED, LOST',
ADD COLUMN `return_remarks` text DEFAULT NULL COMMENT 'Remarks about the return';
