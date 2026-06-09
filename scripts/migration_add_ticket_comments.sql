-- Ticket comments for communication between employee, IT, and admin
CREATE TABLE IF NOT EXISTS `tblticket_comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `author_role` varchar(50) NOT NULL,
  `author_name` varchar(150) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`comment_id`),
  KEY `idx_ticket_comments_ticket` (`ticket_id`),
  KEY `idx_ticket_comments_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
