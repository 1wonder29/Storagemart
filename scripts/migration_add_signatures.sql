-- Migration: Add employee signature storage
-- Date: 2026-04-22
-- Purpose: Store employee signatures for technical reports

CREATE TABLE IF NOT EXISTS `tblemployee_signatures` (
  `signature_id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `signature_path` varchar(500) NOT NULL COMMENT 'Path to signature image',
  `signature_filename` varchar(255) NOT NULL,
  `uploaded_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`signature_id`),
  FOREIGN KEY (`employee_id`) REFERENCES `tblemployee`(`employee_id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_employee_sig` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Stores employee signature images for document signing';
