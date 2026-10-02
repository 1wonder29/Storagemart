-- ========================================
-- Migration: Add Rating and Technical Report to Tickets
-- Date: 2026-05-14
-- Purpose: Add fields to store user ratings and technical report paths for resolved tickets
-- ========================================

-- Add rating and technical_report_path fields to tblticket
ALTER TABLE `tblticket`
ADD COLUMN `rating` int(1) DEFAULT NULL COMMENT 'User rating for ticket resolution (1-5)',
ADD COLUMN `technical_report_path` varchar(255) DEFAULT NULL COMMENT 'Path to the technical report';