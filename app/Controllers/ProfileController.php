<?php

/**
 * ProfileController
 * 
 * Handles user profile operations including signature uploads
 */
class ProfileController
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Handle signature upload
     * 
     * @return void
     */
    public function uploadSignature()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validate authentication
        if (empty($_SESSION['account_id'])) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized - Please log in']);
            exit;
        }

        // Get the employee_id from tblemployee using account_id
        try {
            $employeeSql = "SELECT employee_id FROM tblemployee WHERE account_id = ?";
            $employeeStmt = $this->pdo->prepare($employeeSql);
            $employeeStmt->execute([$_SESSION['account_id']]);
            $employeeData = $employeeStmt->fetch(PDO::FETCH_ASSOC);

            if (!$employeeData) {
                http_response_code(400);
                echo json_encode(['message' => 'Employee record not found']);
                exit;
            }

            $employeeId = $employeeData['employee_id'];
        } catch (PDOException $e) {
            http_response_code(500);
            error_log("Employee lookup error: " . $e->getMessage());
            echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
            exit;
        }

        // Validate file upload
        if (empty($_FILES['signature'])) {
            http_response_code(400);
            echo json_encode(['message' => 'No file provided']);
            exit;
        }

        $file = $_FILES['signature'];
        $allowedTypes = ['image/jpeg', 'image/png'];
        
        // Validate file type
        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid file type. Only PNG and JPG allowed.']);
            exit;
        }

        // Validate file size (5MB limit)
        if ($file['size'] > 5 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['message' => 'File too large. Maximum size is 5MB.']);
            exit;
        }

        // Create upload directory if it doesn't exist
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/signatures/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                http_response_code(500);
                echo json_encode(['message' => 'Failed to create upload directory']);
                exit;
            }
        }

        // Generate unique filename
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'sig_' . $employeeId . '_' . time() . '.' . $ext;
        $filepath = $uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to save file']);
            exit;
        }

        // Save to database
        try {
            // Check if signature already exists for this employee
            $checkSql = "SELECT signature_id FROM tblemployee_signatures WHERE employee_id = ?";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([$employeeId]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Delete old file
                $oldSql = "SELECT signature_path FROM tblemployee_signatures WHERE employee_id = ?";
                $oldStmt = $this->pdo->prepare($oldSql);
                $oldStmt->execute([$employeeId]);
                $oldFile = $oldStmt->fetch(PDO::FETCH_ASSOC);
                if ($oldFile) {
                    @unlink($_SERVER['DOCUMENT_ROOT'] . $oldFile['signature_path']);
                }

                // Update existing record
                $sql = "UPDATE tblemployee_signatures 
                        SET signature_path = ?, signature_filename = ?, uploaded_date = NOW(), is_active = 1
                        WHERE employee_id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(['/assets/signatures/' . $filename, $filename, $employeeId]);
            } else {
                // Insert new record
                $sql = "INSERT INTO tblemployee_signatures (employee_id, signature_path, signature_filename, is_active)
                        VALUES (?, ?, ?, 1)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$employeeId, '/assets/signatures/' . $filename, $filename]);
            }
            
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Signature uploaded successfully']);
        } catch (PDOException $e) {
            // Delete the uploaded file if database operation fails
            @unlink($filepath);
            
            http_response_code(500);
            error_log("Signature upload error: " . $e->getMessage());
            echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    /**
     * Get employee signature image
     * 
     * @param int $employeeId Employee ID
     * @return array|false Signature data or false
     */
    public function getSignature($employeeId)
    {
        try {
            $sql = "SELECT signature_path, signature_filename FROM tblemployee_signatures 
                    WHERE employee_id = ? AND is_active = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$employeeId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get signature error: " . $e->getMessage());
            return false;
        }
    }
}
?>
