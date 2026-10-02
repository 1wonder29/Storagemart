-- Add cause column to tbltickets table
ALTER TABLE `tbltickets` 
ADD COLUMN `cause` VARCHAR(255) NULL AFTER `category`;

-- Create index for faster queries
CREATE INDEX idx_ticket_cause ON `tbltickets`(`cause`);
