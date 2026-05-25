<?php
/**
 * Run Migration: Add condition_upon_return to tbluniform_assignment
 */

header('Content-Type: text/html; charset=utf-8');

// Load configuration
require_once __DIR__ . '/config/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Read migration file
    $migrationFile = __DIR__ . '/scripts/migration_add_condition_upon_return.sql';
    $sql = file_get_contents($migrationFile);

    if (!$sql) {
        throw new Exception('Failed to read migration file');
    }

    // Execute migration statements
    $pdo->exec($sql);

    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Migration Result</title>
        <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
        <link href="assets/css/storagemart.css" rel="stylesheet">
        <style>
            body { padding: 20px; background-color: #f8f9fa; }
            .container { max-width: 600px; margin: 0 auto; }
            .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
            .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
            .details { background: white; padding: 20px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="alert alert-success">
                <h4><i class="fas fa-check-circle"></i> Migration Completed Successfully!</h4>
            </div>
            
            <div class="details">
                <h5>Changes Applied:</h5>
                <ul>
                    <li>Added <code>condition_upon_return</code> column to tbluniform_assignment</li>
                    <li>Added <code>return_remarks</code> column to tbluniform_assignment</li>
                </ul>
                
                <h5 style="margin-top: 20px;">What This Enables:</h5>
                <ul>
                    <li>Uniform return conditions are now stored in the assignment record</li>
                    <li>Return remarks are captured and stored</li>
                    <li>Damage count and other conditions are properly tracked</li>
                </ul>
                
                <a href="' . htmlspecialchars(BASE_URL) . '/hr/uniforms" class="btn btn-primary" style="margin-top: 20px;">
                    <i class="fas fa-arrow-left"></i> Go to Uniforms
                </a>
            </div>
        </div>
    </body>
    </html>';

} catch (Exception $e) {
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Migration Error</title>
        <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
        <link href="assets/css/storagemart.css" rel="stylesheet">
        <style>
            body { padding: 20px; background-color: #f8f9fa; }
            .container { max-width: 600px; margin: 0 auto; }
            .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
            .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
            .details { background: white; padding: 20px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-family: monospace; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="alert alert-danger">
                <h4><i class="fas fa-exclamation-circle"></i> Migration Failed</h4>
            </div>
            
            <div class="details">
                <p><strong>Error:</strong></p>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <p style="margin-top: 20px; color: #666;">
                    The column might already exist. Try refreshing the HR Uniforms page to verify.
                </p>
                
                <a href="' . htmlspecialchars(BASE_URL) . '/hr/uniforms" class="btn btn-primary" style="margin-top: 20px; display: inline-block;">
                    <i class="fas fa-arrow-left"></i> Go to Uniforms
                </a>
            </div>
        </div>
    </body>
    </html>';
}
