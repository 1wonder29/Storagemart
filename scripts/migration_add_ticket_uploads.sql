-- Migration: Add Ticket Uploads Table
-- Purpose: Track signed technical reports uploaded by employees
-- Date: 2026-04-29

CREATE TABLE IF NOT EXISTS `tblticket_uploads` (
  `upload_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `uploaded_by` INT NOT NULL COMMENT 'employee_id of the uploader',
  `original_filename` VARCHAR(255) NOT NULL COMMENT 'Original filename as uploaded',
  `stored_filename` VARCHAR(255) NOT NULL COMMENT 'Sanitized filename stored on disk',
  `file_size` INT NOT NULL COMMENT 'File size in bytes',
  `file_type` VARCHAR(50) NOT NULL COMMENT 'MIME type of file',
  `date_uploaded` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `is_active` TINYINT DEFAULT 1,
  FOREIGN KEY (`ticket_id`) REFERENCES `tbltickets`(`ticket_id`) ON DELETE CASCADE,
  FOREIGN KEY (`uploaded_by`) REFERENCES `tblemployee`(`employee_id`) ON DELETE RESTRICT,
  INDEX `idx_ticket_id` (`ticket_id`),
  INDEX `idx_uploaded_by` (`uploaded_by`),
  INDEX `idx_date_uploaded` (`date_uploaded`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for faster querying
CREATE INDEX IF NOT EXISTS `idx_ticket_active` ON `tblticket_uploads` (`ticket_id`, `is_active`);
