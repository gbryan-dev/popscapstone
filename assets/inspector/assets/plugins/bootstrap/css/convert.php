<?php
require 'vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;

$inputFile = "template.docx"; // Path to your Word template
$outputFile = "output.html";  // Where HTML will be saved

// Load the Word document
$phpWord = IOFactory::load($inputFile);

// Save as HTML
$writer = IOFactory::createWriter($phpWord, 'HTML');
$writer->save($outputFile);

echo "✅ Conversion complete! HTML saved to: $outputFile";
