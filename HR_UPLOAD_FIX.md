# HR Ticket Upload Error - Fix Guide

## Problem
You're getting the error **"An error occurred during upload"** when trying to upload a technical report for an HR ticket.

## Root Cause
The database table `tblticket` is missing the required column `technical_report_path`. This column needs to be added by running a migration script.

## Solution

### Option 1: Run the Migration Script (Recommended)

1. **Open your browser** and navigate to one of these URLs (depending on your setup):
   - `http://localhost/be/Storagemart/public/index.php`
   - Or your configured base URL

2. **Create a simple runner** by opening a terminal in the project directory and creating a temporary PHP file, or alternatively:

3. **Use the automated script** by executing this in your terminal (from the project root):
   ```bash
   php scripts/run_migration_ticket_rating_report.php
   ```

   If that doesn't work, you can manually run the SQL:

### Option 2: Manual SQL Execution

1. Open your database management tool (phpMyAdmin, MySQL Workbench, etc.)
2. Select the Storage Mart database
3. Run this SQL query:

```sql
ALTER TABLE `tblticket`
ADD COLUMN `rating` int(1) DEFAULT NULL COMMENT 'User rating for ticket resolution (1-5)',
ADD COLUMN `technical_report_path` varchar(255) DEFAULT NULL COMMENT 'Path to the technical report';
```

### Option 3: Web-based Diagnostic

1. Open your browser and go to: `http://your-domain/be/Storagemart/public/diagnose_upload.php`
2. This will show you the current database status
3. Follow the recommendations shown

## Verify the Fix

After running the migration:

1. Go back to your HR tickets
2. Find a **Resolved** ticket
3. Click the **Upload** button
4. Select a file (PDF, DOCX, JPG, or PNG - max 10MB)
5. Click **Upload Report**

✓ You should see a success message

## Troubleshooting

### Still getting an error?

Check the browser console (F12 → Console tab) for more details. Common issues:

- **"File size exceeds 10MB limit"** - Use a smaller file
- **"Invalid file type"** - Use PDF, DOCX, DOC, JPG, or PNG
- **"Only resolved tickets can have reports uploaded"** - The ticket status must be exactly "Resolved"
- **"No file uploaded or upload error occurred"** - Try selecting a file again

### Check error logs

Look for error details in:
- Browser Developer Tools (F12 → Console and Network tabs)
- Server error log: `c:\xampp\logs\php_error_log` (if using XAMPP)

## What Changed

- **Fixed**: Database error handling now provides better diagnostics
- **Added**: Migration runner script at `scripts/run_migration_ticket_rating_report.php`
- **Added**: Diagnostic tool at `public/diagnose_upload.php`
- **Improved**: Error logging in HR controller includes stack traces

## Files Modified

- `app/Controllers/hr/HrTicketController.php` - Enhanced error handling
- `scripts/run_migration_ticket_rating_report.php` - NEW - Migration runner
- `public/diagnose_upload.php` - NEW - Diagnostic tool
