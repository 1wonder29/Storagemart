<?php

require_once __DIR__ . '/../../../config/config.php';

class TicketUploadModel {
    
    private $db;
    
    public function __construct($database = null) {
        global $pdo;
        $this->db = $database ?? $pdo;
    }
    
    /**
     * Record a ticket upload in the database
     * 
     * @param int $ticketId
     * @param int $employeeId
     * @param string $originalFilename
     * @param string $storedFilename
     * @param int $fileSize
     * @param string $fileType
     * 
     * @return int|false Upload ID on success, false on failure
     */
    public function recordUpload($ticketId, $employeeId, $originalFilename, $storedFilename, $fileSize, $fileType) {
        try {
            $sql = "
                INSERT INTO tblticket_uploads 
                    (ticket_id, uploaded_by, original_filename, stored_filename, file_size, file_type, date_uploaded)
                VALUES 
                    (?, ?, ?, ?, ?, ?, NOW())
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                (int)$ticketId,
                (int)$employeeId,
                $originalFilename,
                $storedFilename,
                (int)$fileSize,
                $fileType
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("TicketUploadModel::recordUpload - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all uploads for a specific ticket
     * 
     * @param int $ticketId
     * 
     * @return array List of uploads
     */
    public function getTicketUploads($ticketId) {
        try {
            $sql = "
                SELECT 
                    u.upload_id,
                    u.ticket_id,
                    u.original_filename,
                    u.stored_filename,
                    u.file_size,
                    u.file_type,
                    u.date_uploaded,
                    e.firstname,
                    e.lastname
                FROM tblticket_uploads u
                JOIN tblemployee e ON u.uploaded_by = e.employee_id
                WHERE u.ticket_id = ? AND u.is_active = 1
                ORDER BY u.date_uploaded DESC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$ticketId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("TicketUploadModel::getTicketUploads - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get a specific upload by ID
     * 
     * @param int $uploadId
     * 
     * @return array|false Upload data or false if not found
     */
    public function getUploadById($uploadId) {
        try {
            $sql = "
                SELECT 
                    upload_id, ticket_id, uploaded_by, original_filename, 
                    stored_filename, file_size, file_type, date_uploaded
                FROM tblticket_uploads
                WHERE upload_id = ? AND is_active = 1
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$uploadId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        } catch (PDOException $e) {
            error_log("TicketUploadModel::getUploadById - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if an employee has uploaded for a specific ticket
     * 
     * @param int $ticketId
     * @param int $employeeId
     * 
     * @return bool
     */
    public function hasUploaded($ticketId, $employeeId) {
        try {
            $sql = "
                SELECT COUNT(*) as count 
                FROM tblticket_uploads
                WHERE ticket_id = ? AND uploaded_by = ? AND is_active = 1
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$ticketId, (int)$employeeId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("TicketUploadModel::hasUploaded - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Soft-delete an upload record
     * 
     * @param int $uploadId
     * 
     * @return bool
     */
    public function deleteUpload($uploadId) {
        try {
            $sql = "UPDATE tblticket_uploads SET is_active = 0 WHERE upload_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([(int)$uploadId]);
        } catch (PDOException $e) {
            error_log("TicketUploadModel::deleteUpload - " . $e->getMessage());
            return false;
        }
    }
}
?>
