-- ========================================
-- Migration: Add Returns Tracking to Uniforms
-- Date: 2026-05-14
-- Purpose: Track returned uniforms separately before restocking
-- ========================================

-- Add quantity_returned and quantity_damaged fields to tbluniform_inventory
ALTER TABLE `tbluniform_inventory` 
ADD COLUMN `quantity_returned` int(11) NOT NULL DEFAULT 0 COMMENT 'Uniforms pending inspection after return',
ADD COLUMN `quantity_damaged` int(11) NOT NULL DEFAULT 0 COMMENT 'Uniforms marked as damaged during return',
ADD COLUMN `quantity_lost` int(11) NOT NULL DEFAULT 0 COMMENT 'Uniforms marked as lost';

-- Create a returns tracking table for detailed returns
CREATE TABLE IF NOT EXISTS `tbluniform_returns` (
  `return_id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `uniform_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `quantity_returned` int(11) NOT NULL DEFAULT 1,
  `condition_upon_return` varchar(100) DEFAULT NULL COMMENT 'GOOD, FAIR, USED, DAMAGED, LOST',
  `remarks` text,
  `date_returned` date NOT NULL,
  `processed_by` varchar(50) NOT NULL,
  `return_status` enum('PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'PENDING' COMMENT 'PENDING=waiting inspection, APPROVED=ready to restock, REJECTED=not accepted',
  `processed_at` datetime DEFAULT NULL,
  `approved_by` varchar(50) DEFAULT NULL,
  `createdby` varchar(50) NOT NULL,
  `datecreated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`return_id`),
  KEY `idx_assignment_id` (`assignment_id`),
  KEY `idx_uniform_id` (`uniform_id`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_return_status` (`return_status`),
  KEY `idx_date_returned` (`date_returned`),
  CONSTRAINT `fk_returns_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `tbluniform_assignment` (`assignment_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_returns_uniform` FOREIGN KEY (`uniform_id`) REFERENCES `tbluniform_inventory` (`uniform_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_returns_employee` FOREIGN KEY (`employee_id`) REFERENCES `tblemployee` (`employee_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
