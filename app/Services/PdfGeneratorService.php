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
        
        // Determine document root path (3 levels up from Services/PdfGeneratorService.php to project root)
        $projectRoot = dirname(dirname(dirname(__FILE__)));
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? $projectRoot . '/public';
        
        $this->templatePath = $documentRoot . '/assets/generatePDF';
        $this->outputPath = $documentRoot . '/assets/tickets/pdfs';
        
        // Ensure output directory exists
        if (!is_dir($this->outputPath)) {
            @mkdir($this->outputPath, 0755, true);
        }
    }
    
    /**
     * Generate Technical Record (DOCX) for a ticket - On-demand version
     * 
     * Generates a technical report document for employees to download
     * The document contains full ticket details, technical work performed, and resolution info
     * Generated on-the-fly (not stored in database)
     * 
     * @param int $ticketId The ID of the ticket to generate DOCX for
     * @param int $employeeId The ID of the employee requesting the record
     * 
     * @return array|false Returns array with 'success', 'filename', 'path', 'file_size' on success
     *                     Returns false on failure
     */
    public function generateTechnicalRecordDocx($ticketId, $employeeId)
    {
        try {
            // Fetch ticket and technical data
            $ticketData = $this->getTicketData($ticketId);
            
            if (!$ticketData) {
                $this->logError("No ticket data found for ticket_id: {$ticketId}");
                return false;
            }
            
            // Verify ticket belongs to the employee
            if ((int)$ticketData['employee_id'] !== (int)$employeeId) {
                $this->logError("Employee {$employeeId} attempted to access ticket {$ticketId} belonging to employee {$ticketData['employee_id']}");
                return false;
            }
            
            // Verify ticket is resolved
            if (strtolower($ticketData['status']) !== 'resolved') {
                $this->logError("Cannot generate record for unresolved ticket: {$ticketId}");
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
            
            // Generate filename with timestamp for freshness
            $timestamp = date('YmdHis');
            $sanitizedTicketNumber = preg_replace('/[^A-Za-z0-9_-]/', '', $ticketData['ticket_number']);
            $filename = "technical_record_{$sanitizedTicketNumber}_{$timestamp}.docx";
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
                'filepath' => $filepath,
                'file_size' => $fileSize
            ];
            
        } catch (\Exception $e) {
            $this->logError("Exception in generateTechnicalRecordDocx: " . $e->getMessage());
            return false;
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
                t.employee_id,
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

    /**
     * Generate Accountability Form for HR
     * 
     * Generates a DOCX accountability form using the template
     * Shows employee details, assigned IT assets, and uniforms
     * 
     * @param array $employee Employee information
     * @param array $assets List of assigned assets
     * @param array $uniforms List of assigned uniforms
     * @return void Outputs DOCX file as download
     */
    public function generateAccountabilityForm($employee, $assets, $uniforms)
    {
        try {
            // Path to accountability template
            $templateFile = $this->templatePath . '/template_accountability.docx';
            
            if (!file_exists($templateFile)) {
                throw new \Exception("Template file not found: {$templateFile}");
            }
            
            // Create template processor
            $template = new TemplateProcessor($templateFile);
            
            // Prepare employee data
            $fullname = trim($employee['firstname'] . ' ' . ($employee['middlename'] ? $employee['middlename'] . ' ' : '') . $employee['lastname']);
            $employee_id = $employee['employee_id'];
            $department = $employee['department'] ?? 'N/A';
            $position = $employee['position'] ?? 'N/A';
            $date_issued = date('F d, Y');
            
            // Fill in employee information
            $template->setValue('name', htmlspecialchars($fullname));
            $template->setValue('employee_id', htmlspecialchars($employee_id));
            $template->setValue('department', htmlspecialchars($department));
            $template->setValue('position', htmlspecialchars($position));
            $template->setValue('date_issued', htmlspecialchars($date_issued));
            
            // Fill in IT Assets
            if (!empty($assets)) {
                $template->cloneRow('itemInfo', count($assets));
                $i = 1;
                foreach ($assets as $asset) {
                    $template->setValue("itemInfo#{$i}", htmlspecialchars($asset['itemInfo'] ?? 'N/A'));
                    $template->setValue("assetCode#{$i}", htmlspecialchars($asset['assetCode'] ?? 'N/A'));
                    $template->setValue("assetNumber#{$i}", htmlspecialchars($asset['assetNumber'] ?? 'N/A'));
                    $template->setValue("serialNumber#{$i}", htmlspecialchars($asset['serialNumber'] ?? 'N/A'));
                    $i++;
                }
            } else {
                // Handle no assets case
                $template->setValue('itemInfo', 'No assets assigned');
                $template->setValue('assetCode', '');
                $template->setValue('assetNumber', '');
                $template->setValue('serialNumber', '');
            }
            
            // Fill in Uniforms
            if (!empty($uniforms)) {
                $template->cloneRow('uniform_type', count($uniforms));
                $j = 1;
                foreach ($uniforms as $uniform) {
                    $template->setValue("uniform_type#{$j}", htmlspecialchars($uniform['uniform_type'] ?? 'N/A'));
                    $template->setValue("size#{$j}", htmlspecialchars($uniform['size'] ?? 'N/A'));
                    $template->setValue("color#{$j}", htmlspecialchars($uniform['color'] ?? 'N/A'));
                    $template->setValue("quantity_issued#{$j}", htmlspecialchars($uniform['quantity_issued'] ?? '0'));
                    $j++;
                }
            } else {
                // Handle no uniforms case
                $template->setValue('uniform_type', 'No uniforms assigned');
                $template->setValue('size', '');
                $template->setValue('color', '');
                $template->setValue('quantity_issued', '');
            }
            
            // Generate filename
            $filename = 'accountability_form_' . $employee_id . '_' . date('YmdHis') . '.docx';
            $filepath = $this->outputPath . '/' . $filename;
            
            // Save document
            $template->saveAs($filepath);
            
            if (!file_exists($filepath)) {
                throw new \Exception("Failed to save document: {$filepath}");
            }
            
            // Send as download
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=" . basename($filepath));
            header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
            header("Content-Length: " . filesize($filepath));
            readfile($filepath);
            
            // Clean up temporary file after sending
            unlink($filepath);
            exit;
            
        } catch (\Exception $e) {
            $this->logError("Error in generateAccountabilityForm: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
