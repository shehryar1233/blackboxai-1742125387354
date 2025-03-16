<?php
/**
 * Smart Restaurant System Download Script
 * Creates a ZIP archive of all project files
 */

// Set headers for ZIP download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="smart-restaurant.zip"');
header('Content-Length: ' . filesize($zipFile));
header('Pragma: no-cache');

// Create ZIP archive
$zip = new ZipArchive();
$zipFile = 'smart-restaurant.zip';

if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    // Function to add files recursively
    function addFilesToZip($dir, $zip, $basePath = '') {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $filePath = $dir . '/' . $file;
                $relativePath = $basePath ? $basePath . '/' . $file : $file;
                
                if (is_dir($filePath)) {
                    $zip->addEmptyDir($relativePath);
                    addFilesToZip($filePath, $zip, $relativePath);
                } else {
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }
    }

    // Add project directories
    $directories = [
        'frontend',
        'backend',
        'uploads',
        'logs',
        'cache'
    ];

    foreach ($directories as $dir) {
        if (is_dir($dir)) {
            $zip->addEmptyDir($dir);
            addFilesToZip($dir, $zip, $dir);
        }
    }

    // Add root files
    $rootFiles = [
        'README.md',
        'server.py',
        'install.php',
        'setup.sh',
        'start.sh',
        'test.php',
        '.gitignore'
    ];

    foreach ($rootFiles as $file) {
        if (file_exists($file)) {
            $zip->addFile($file);
        }
    }

    $zip->close();

    // Output ZIP file
    readfile($zipFile);
    unlink($zipFile); // Delete the temporary file
} else {
    echo "Failed to create ZIP archive";
}
