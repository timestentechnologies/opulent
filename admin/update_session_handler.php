<?php
// Get all PHP files in the current directory and subdirectories
function getAllPhpFiles($dir) {
    $files = array();
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    
    return $files;
}

// Files to skip
$skip_files = array(
    'session_config.php',
    'session_handler.php',
    'connect.php',
    'update_session_handler.php',
    'check_errors.php'
);

// Get all PHP files
$files = getAllPhpFiles('.');

// Process each file
foreach ($files as $file) {
    $filename = basename($file);
    
    // Skip certain files
    if (in_array($filename, $skip_files)) {
        continue;
    }
    
    // Read file content
    $content = file_get_contents($file);
    
    // Skip if file already includes session_handler.php
    if (strpos($content, 'session_handler.php') !== false) {
        continue;
    }
    
    // Remove any existing session_start calls
    $content = preg_replace('/session_start\(\);/', '', $content);
    
    // Add session handler include at the start of the file
    $new_content = "<?php\nrequire_once('session_handler.php');\n?>\n" . $content;
    
    // Save the file
    file_put_contents($file, $new_content);
    
    echo "Updated: $file\n";
}

echo "Done updating files.\n";
?> 