<?php
require '../vendor/autoload.php'; // Load Composer's autoloader

use PhpOffice\PhpWord\PhpWord;

// Create new Word document
$phpWord = new PhpWord();
$section = $phpWord->addSection();
$section->addText('🎉 PHPWord is working perfectly!');
$section->addText('You can now generate Word files dynamically using PHP.');

// Save as a Word file
$file = 'phpword_test.docx';
$phpWord->save($file, 'Word2007');

// Notify success
echo "✅ Word file generated successfully: <strong>$file</strong>";
?>
