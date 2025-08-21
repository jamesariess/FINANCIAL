<?php
// Get slug from URL (e.g., "adjustment")
$slug = isset($_GET['slug']) ? basename($_GET['slug']) : '';

// Base path to pages folder
$pagesPath = __DIR__ . '/pages/';

// Search all subfolders in pages/
$found = false;
foreach (glob($pagesPath . '*', GLOB_ONLYDIR) as $folder) {
    $filePath = $folder . '/' . $slug . '.php';
    if (file_exists($filePath)) {
        include $filePath;
        $found = true;
        break;
    }
}

// If not found, show 404
if (!$found) {
    http_response_code(404);
    echo "Page not found.";
}
