<?php

// Load Composer autoloader for PhpOffice
require_once __DIR__ . '/../../public/assets/vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

/**
 * PdfGeneratorService
 * 
 * Handles automatic PDF/Document generation for resolved tickets
 * Generates technical reports based on ticket resolution data
 */
class PdfGeneratorService
{
    /**
     * Database connection link
     */
    private $db;
    
    /**
     * Path to templates directory
     */
    private $templatePath;
    
    /**
     * Path to output directory for generated documents
     */
    private $outputPath;
    
    /**
     * Constructor
     * 
     * @param PDO $database PDO database connection
     */
    public function __construct($database = null)
    {
        global $pdo;
        $this->db = $database ?? $pdo;
        $this->templatePath = $_SERVER['DOCUMENT_ROOT'] . '/assets/generatePDF';
        $this->outputPath = $_SERVER['DOCUMENT_ROOT'] . '/assets/tickets/pdfs';
        
        // Ensure output directory exists
        if (!is_dir($this->outputPath)) {
            mkdir($this->outputPath, 0755, true);
        }
    }
    
    /**
     * Generate Resolution PDF for a ticket
     * 
     * Automatically generates a technical report document when a ticket is resolved
     * The document contains ticket details, technical work performed, and resolution info
     * 
     * @param int $ticketId The ID of the ticket to generate PDF for
     * @param int $userId The ID of the user triggering the generation
     * @param string $userRole The role of the user (IT, ADMIN, HEAD)
     * 
     * @return array|false Returns array with 'success', 'filename', 'path', 'file_size' on success
     *                     Returns false on failure
     */
    public function generateResolutionPdf($ticketId, $userId, $userRole = 'IT')
    {
        try {
            // Fetch ticket and technical data
            $ticketData = $this->getTicketData($ticketId);
            
            if (!$ticketData) {
                $this->logError("No ticket data found for ticket_id: {$ticketId}");
                return false;
            }
            
            // Validate template exists
            $templateFile = $this->templatePath . '/template_technical.docx';
            if (!file_exists($templateFile)) {
                $this->logError("Template file not found: {$templateFile}");
                return false;
            }
            
            // Process template with data
            $template = new TemplateProcessor($templateFile);
            $this->populateTemplate($template, $ticketData);
            
            // Generate filename
            $timestamp = date('YmdHis');
            $sanitizedTicketNumber = preg_replace('/[^A-Za-z0-9_-]/', '', $ticketData['ticket_number']);
            $filename = "ticket_resolution_{$sanitizedTicketNumber}_{$timestamp}.docx";
            $filepath = $this->outputPath . '/' . $filename;
            
            // Save document
            $template->saveAs($filepath);
            
            // Verify file was created
            if (!file_exists($filepath)) {
                $this->logError("Failed to save document: {$filepath}");
                return false;
            }
            
            $fileSize = filesize($filepath);
            
            return [
                'success' => true,
                'filename' => $filename,
                'path' => '/assets/tickets/pdfs/' . $filename,
                'full_path' => $filepath,
                'file_size' => $fileSize
            ];
            
        } catch (\Exception $e) {
            $this->logError("Exception in generateResolutionPdf: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Fetch complete ticket and technical data
     * 
     * @param int $ticketId Ticket ID to fetch
     * @return array|false Ticket data or false if not found
     */
    private function getTicketData($ticketId)
    {
        $sql = "
            SELECT 
                t.ticket_id,
                t.ticket_number,
                t.concern_details,
                t.priority,
                t.date_filed,
                t.status,
                t.remarks,
                e.firstname AS emp_firstname,
                e.middlename AS emp_middlename,
                e.lastname AS emp_lastname,
                e.position AS emp_position,
                b.branchName,
                tt.technical_purpose,
                tt.action_taken,
                tt.result,
                tt.remarks AS technical_remarks,
                tt.date_performed,
                it.firstname AS it_firstname,
                it.lastname AS it_lastname
            FROM tbltickets t
            JOIN tblemployee e ON t.employee_id = e.employee_id
            JOIN tblbranch b ON e.branch_id = b.branch_id
            LEFT JOIN (
                SELECT ticket_id, MAX(date_performed) AS max_date
                FROM tblticket_technical
                GROUP BY ticket_id
            ) x ON x.ticket_id = t.ticket_id
            LEFT JOIN tblticket_technical tt
                ON tt.ticket_id = x.ticket_id
               AND tt.date_performed = x.max_date
            LEFT JOIN tblemployee it ON tt.performed_by = it.employee_id
            WHERE t.ticket_id = ?
        ";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ticketId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ?: false;
        } catch (PDOException $e) {
            $this->logError("Database error in getTicketData: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Populate template with ticket data
     * 
     * @param TemplateProcessor $template Template processor instance
     * @param array $data Ticket data
     * @return void
     */
    private function populateTemplate($template, $data)
    {
        $fullname = trim($data['emp_firstname'] . ' ' . $data['emp_lastname']);
        $performedby = !empty($data['it_firstname']) ? trim($data['it_firstname'] . ' ' . $data['it_lastname']) : 'N/A';
        $date_filed = date('F d, Y', strtotime($data['date_filed']));
        $date_performed = !empty($data['date_performed']) ? date('F d, Y', strtotime($data['date_performed'])) : 'N/A';
        
        $template->setValue('date_filed', htmlspecialchars($date_filed));
        $template->setValue('fullname', htmlspecialchars($fullname));
        $template->setValue('branchName', htmlspecialchars($data['branchName']));
        $template->setValue('performedby', htmlspecialchars($performedby));
        $template->setValue('technical_purpose', htmlspecialchars($data['technical_purpose'] ?? 'N/A'));
        $template->setValue('concern_details', htmlspecialchars($data['concern_details']));
        $template->setValue('action_taken', htmlspecialchars($data['action_taken'] ?? 'N/A'));
        $template->setValue('result', htmlspecialchars($data['result'] ?? 'N/A'));
        $template->setValue('date_performed', htmlspecialchars($date_performed));
    }
    
    /**
     * Log error for debugging
     * 
     * @param string $message Error message
     * @return void
     */
    private function logError($message)
    {
        // Log to PHP's error log (check php.ini for error_log location)
        error_log("PdfGeneratorService: {$message}");
        
        // Also try to log to a file in the app logs directory
        $logDir = $_SERVER['DOCUMENT_ROOT'] . '/../app/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/pdf_generation.log';
        $timestamp = date('Y-m-d H:i:s');
        @error_log("[{$timestamp}] {$message}\n", 3, $logFile);
    }
}
?>
