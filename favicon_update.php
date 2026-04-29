<?php
/**
 * Comprehensive Favicon Update Script
 * Run this script on your live server to fix favicon issues across all pages
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type for HTML output
header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favicon Update Script</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #17a2b8; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .file-list { max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .progress { width: 100%; background: #f0f0f0; border-radius: 5px; overflow: hidden; margin: 10px 0; }
        .progress-bar { height: 20px; background: #28a745; transition: width 0.3s; }
    </style>
</head>
<body>
    <h1>🚀 Comprehensive Favicon Update Script</h1>
    
    <div class="info">
        <strong>This script will:</strong><br>
        1. Create favicon.ico from existing PNG<br>
        2. Update all PHP files with comprehensive favicon links<br>
        3. Add cache busting for better performance<br>
        4. Test favicon functionality<br>
        5. Provide detailed status report
    </div>

    <?php
    if (isset($_POST['run_update'])) {
        echo "<h2>🔄 Running Favicon Update...</h2>";
        
        $results = [
            'favicon_ico_created' => false,
            'files_updated' => 0,
            'files_failed' => 0,
            'errors' => [],
            'success' => []
        ];
        
        // Step 1: Create favicon.ico
        echo "<div class='progress'><div class='progress-bar' style='width: 20%'></div></div>";
        echo "<h3>Step 1: Creating favicon.ico</h3>";
        
        $favicon_png = __DIR__ . '/images/favicon.png';
        $favicon_ico = __DIR__ . '/favicon.ico';
        
        if (file_exists($favicon_png)) {
            if (copy($favicon_png, $favicon_ico)) {
                $results['favicon_ico_created'] = true;
                echo "<div class='success'>✅ favicon.ico created successfully</div>";
                $results['success'][] = "favicon.ico created from PNG";
            } else {
                echo "<div class='error'>❌ Failed to create favicon.ico</div>";
                $results['errors'][] = "Failed to copy favicon.png to favicon.ico";
            }
        } else {
            echo "<div class='error'>❌ Source favicon.png not found</div>";
            $results['errors'][] = "Source favicon.png not found at images/favicon.png";
        }
        
        // Step 2: Define favicon HTML template
        echo "<div class='progress'><div class='progress-bar' style='width: 40%'></div></div>";
        echo "<h3>Step 2: Preparing favicon HTML template</h3>";
        
        $favicon_template = <<<HTML
<!-- Favicon with comprehensive browser support -->
<link rel="icon" type="image/x-icon" href="favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
<link rel="icon" type="image/png" sizes="16x16" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
<link rel="icon" type="image/png" sizes="192x192" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
<link rel="shortcut icon" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
<link rel="apple-touch-icon" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
<meta name="theme-color" content="#0B5FB0">
HTML;
        
        echo "<div class='success'>✅ Favicon HTML template prepared</div>";
        
        // Step 3: Find all PHP files to update
        echo "<div class='progress'><div class='progress-bar' style='width: 60%'></div></div>";
        echo "<h3>Step 3: Finding PHP files to update</h3>";
        
        $php_files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $php_files[] = $file->getPathname();
            }
        }
        
        // Exclude certain directories
        $exclude_patterns = ['admin', 'old', 'DB', 'Documentation', 'frontend', 'includes'];
        $php_files = array_filter($php_files, function($file) use ($exclude_patterns) {
            foreach ($exclude_patterns as $pattern) {
                if (strpos($file, '/' . $pattern . '/') !== false) {
                    return false;
                }
            }
            return true;
        });
        
        echo "<div class='info'>Found " . count($php_files) . " PHP files to check</div>";
        
        // Step 4: Update files
        echo "<div class='progress'><div class='progress-bar' style='width: 80%'></div></div>";
        echo "<h3>Step 4: Updating PHP files</h3>";
        
        foreach ($php_files as $file) {
            $content = file_get_contents($file);
            
            // Check if file has HTML head section
            if (preg_match('/<head[^>]*>/i', $content) && preg_match('/<title[^>]*>/i', $content)) {
                
                // Remove existing favicon links
                $content = preg_replace('/<!-- Favicon[^>]*-->.*?(?=<\/head>)/s', '', $content);
                $content = preg_replace('/<link[^>]*favicon[^>]*>/i', '', $content);
                $content = preg_replace('/<link[^>]*icon[^>]*favicon[^>]*>/i', '', $content);
                
                // Add new favicon links after title tag
                $content = preg_replace('/(<title[^>]*>.*?<\/title>)/i', "$1\n" . $favicon_template, $content);
                
                if (file_put_contents($file, $content)) {
                    $results['files_updated']++;
                    echo "<div class='success'>✅ Updated: " . basename($file) . "</div>";
                    $results['success'][] = "Updated: " . basename($file);
                } else {
                    $results['files_failed']++;
                    echo "<div class='error'>❌ Failed to update: " . basename($file) . "</div>";
                    $results['errors'][] = "Failed to update: " . basename($file);
                }
            }
        }
        
        // Step 5: Test favicon
        echo "<div class='progress'><div class='progress-bar' style='width: 100%'></div></div>";
        echo "<h3>Step 5: Testing favicon functionality</h3>";
        
        $favicon_test_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/favicon_test.php';
        $favicon_debug_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/favicon_debug.php';
        
        echo "<div class='info'>";
        echo "🔗 Test pages created:<br>";
        echo "<a href='$favicon_test_url' target='_blank'>$favicon_test_url</a><br>";
        echo "<a href='$favicon_debug_url' target='_blank'>$favicon_debug_url</a><br>";
        echo "</div>";
        
        // Final results
        echo "<h2>📊 Update Results</h2>";
        echo "<div class='success'>";
        echo "✅ favicon.ico created: " . ($results['favicon_ico_created'] ? 'Yes' : 'No') . "<br>";
        echo "✅ Files updated: " . $results['files_updated'] . "<br>";
        echo "❌ Files failed: " . $results['files_failed'] . "<br>";
        echo "</div>";
        
        if (!empty($results['success'])) {
            echo "<h3>✅ Success Details:</h3>";
            echo "<div class='file-list'>";
            foreach ($results['success'] as $success) {
                echo "<div>• $success</div>";
            }
            echo "</div>";
        }
        
        if (!empty($results['errors'])) {
            echo "<h3>❌ Errors:</h3>";
            echo "<div class='file-list'>";
            foreach ($results['errors'] as $error) {
                echo "<div>• $error</div>";
            }
            echo "</div>";
        }
        
        echo "<div class='warning'>";
        echo "<strong>Next Steps:</strong><br>";
        echo "1. Clear your browser cache (Ctrl+Shift+Del)<br>";
        echo "2. Hard refresh your pages (Ctrl+F5)<br>";
        echo "3. Test the favicon on different pages<br>";
        echo "4. Check the test pages above for debugging<br>";
        echo "</div>";
        
    } else {
        ?>
        <div class="warning">
            <strong>⚠️ Important:</strong> This script will modify multiple PHP files in your project. 
            Make sure you have a backup before proceeding.
        </div>
        
        <form method="post" onsubmit="return confirm('Are you sure you want to run the favicon update? This will modify multiple files.')">
            <button type="submit" name="run_update" value="1">
                🚀 Run Favicon Update
            </button>
        </form>
        
        <h2>📋 What this script does:</h2>
        
        <h3>1. Creates favicon.ico</h3>
        <p>Copies your existing favicon.png to favicon.ico for better browser compatibility.</p>
        
        <h3>2. Updates PHP Files</h3>
        <p>Finds all PHP files with HTML head sections and adds comprehensive favicon links:</p>
        <pre><?= htmlspecialchars($favicon_template ?? '') ?></pre>
        
        <h3>3. Features Added:</h3>
        <ul>
            <li>Traditional favicon.ico support</li>
            <li>PNG favicon in multiple sizes (16x16, 32x32, 192x192)</li>
            <li>Apple touch icon support</li>
            <li>Cache busting with filemtime()</li>
            <li>Theme color for mobile browsers</li>
        </ul>
        
        <h3>4. Files That Will Be Updated:</h3>
        <div class="file-list">
        <?php
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
        $exclude_patterns = ['admin', 'old', 'DB', 'Documentation', 'frontend', 'includes'];
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filepath = $file->getPathname();
                $should_include = true;
                
                foreach ($exclude_patterns as $pattern) {
                    if (strpos($filepath, '/' . $pattern . '/') !== false) {
                        $should_include = false;
                        break;
                    }
                }
                
                if ($should_include) {
                    $content = file_get_contents($filepath);
                    if (preg_match('/<head[^>]*>/i', $content) && preg_match('/<title[^>]*>/i', $content)) {
                        echo "• " . basename($filepath) . "<br>";
                    }
                }
            }
        }
        ?>
        </div>
        
        <?php
    }
    ?>
    
    <hr>
    <div class="info">
        <strong>Manual Testing:</strong><br>
        After running the update, test these URLs:<br>
        • <code>/favicon_test.php</code> - Basic favicon test<br>
        • <code>/favicon_debug.php</code> - Detailed debugging<br>
        • <code>/images/favicon.png</code> - Direct favicon access<br>
        • <code>/favicon.ico</code> - Direct ICO access
    </div>
</body>
</html>
