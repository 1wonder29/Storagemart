-- Migration: Add PDF tracking table for resolved tickets
-- Date: 2026-04-20
-- Purpose: Store references to generated PDFs for resolved tickets

-- Create tblticket_pdfs table if it doesn't exist
CREATE TABLE IF NOT EXISTS `tblticket_pdfs` (
  `pdf_id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `pdf_filename` varchar(255) NOT NULL COMMENT 'Filename of generated PDF on server',
  `pdf_path` varchar(500) NOT NULL COMMENT 'Full relative path to PDF file',
  `generated_by` int(11) NOT NULL COMMENT 'user_id of who triggered the generate',
  `role` varchar(50) DEFAULT NULL COMMENT 'Role of user who triggered generation (IT, ADMIN, HEAD)',
  `date_generated` datetime DEFAULT CURRENT_TIMESTAMP,
  `file_size` int(11) DEFAULT NULL COMMENT 'PDF file size in bytes',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'Flag to mark if PDF still exists on server',
  PRIMARY KEY (`pdf_id`),
  KEY `ticket_id_idx` (`ticket_id`),
  KEY `generated_by_idx` (`generated_by`),
  FOREIGN KEY (`ticket_id`) REFERENCES `tbltickets`(`ticket_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Tracks generated PDF documents for resolved tickets';

-- Create index for quick lookups of latest PDF per ticket
CREATE INDEX IF NOT EXISTS `idx_ticket_latest_pdf` ON `tblticket_pdfs` (`ticket_id`, `date_generated`);
