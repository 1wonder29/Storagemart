-- Migration: Add Subject and Due Date to tickets
-- Purpose: New Support Ticket form captures a short subject and an optional due date.
-- Must be applied to every environment (local + production) BEFORE deploying the matching code.

ALTER TABLE `tbltickets`
  ADD COLUMN `subject` VARCHAR(255) NULL AFTER `ticket_number`,
  ADD COLUMN `due_date` DATE NULL AFTER `priority`;
