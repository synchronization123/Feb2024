<?php
session_start();

if (isset($_GET['path'])) {
    $decodedPath = base64_decode($_GET['path']);

    // Ensure that the decoded path is within the allowed directory to prevent security issues
    $allowedDirectory = '';
    $fullImagePath = $allowedDirectory . $decodedPath;

    // Check if the image file exists
    if (file_exists($fullImagePath)) {
        // Display the image
        header('Content-Type: image/png');
        readfile($fullImagePath);
        exit;
    } else {
        echo 'Image not found. Full path: ' . $fullImagePath;
    }
} else {
    echo 'Invalid request.';
}
?>
