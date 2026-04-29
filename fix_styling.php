<?php
/**
 * Quick fix for styling issues caused by favicon update script
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Styling Fix Script</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #17a2b8; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        button:hover { background: #0056b3; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔧 Styling Fix Script</h1>
    
    <div class="error">
        <strong>⚠️ Problem Detected:</strong> The favicon update script may have accidentally removed CSS/JS links from your pages, causing them to appear unstyled.
    </div>

    <?php
    if (isset($_POST['fix_styling'])) {
        echo "<h2>🔄 Fixing styling issues...</h2>";
        
        // Define the correct head template for index.php
        $index_head_template = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Opulent Laundry Services</title>
<!-- Favicon with comprehensive browser support -->
<link rel="icon" type="image/x-icon" href="favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
<link rel="icon" type="image/png" sizes="16x16" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
<link rel="icon" type="image/png" sizes="192x192" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
<link rel="shortcut icon" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
<link rel="apple-touch-icon" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
<meta name="theme-color" content="#0B5FB0">
<script src="https://cdn.tailwindcss.com/3.4.16"></script>
<script>
tailwind.config={
    theme:{
        extend:{
            colors:{
                primary:'#0B5FB0',
                secondary:'#10B981'
            },
            borderRadius:{
                'none':'0px',
                'sm':'4px',
                DEFAULT:'8px',
                'md':'12px',
                'lg':'16px',
                'xl':'20px',
                '2xl':'24px',
                '3xl':'32px',
                'full':'9999px',
                'button':'8px'
            }
        }
    }
}
</script>
HTML;

        // Fix index.php
        $index_file = __DIR__ . '/index.php';
        if (file_exists($index_file)) {
            $content = file_get_contents($index_file);
            
            // Find the PHP closing tag and replace everything after it with correct head
            if (preg_match('/(\?>\s*)/', $content, $matches, PREG_OFFSET_CAPTURE)) {
                $php_end_pos = $matches[1][1] + strlen($matches[1][0]);
                $php_part = substr($content, 0, $php_end_pos);
                
                // Find the rest of the content after the head section
                if (preg_match('/<\/script>\s*(.*)/s', $content, $body_matches)) {
                    $body_content = $body_matches[1];
                    
                    // Reconstruct the file with correct head
                    $new_content = $php_part . "\n" . $index_head_template . "\n</script>\n" . $body_content;
                    
                    if (file_put_contents($index_file, $new_content)) {
                        echo "<div class='success'>✅ index.php fixed successfully</div>";
                    } else {
                        echo "<div class='error'>❌ Failed to fix index.php</div>";
                    }
                }
            }
        }
        
        // Fix other common files with standard head template
        $standard_head_template = <<<HTML
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{TITLE}</title>
    <!-- Favicon with comprehensive browser support -->
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
    <link rel="shortcut icon" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
    <link rel="apple-touch-icon" href="images/favicon.png?v=<?php echo filemtime('images/favicon.png'); ?>">
    <meta name="theme-color" content="#0B5FB0">
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
HTML;

        $files_to_fix = [
            'dashboard.php' => '{TITLE} Dashboard - Opulent Laundry Services',
            'aboutus.php' => 'About Us - Opulent Laundry',
            'services.php' => 'Opulent Laundry Services',
            'contact.php' => 'Contact Us - FreshPress Laundry Services',
            'login.php' => 'Login - Opulent Laundry Services',
            'sign_up.php' => 'Sign Up - Opulent Laundry Services',
            'pricing.php' => 'Pricing - Opulent Laundry Services',
            'profile.php' => 'Profile - FreshPress Laundry Services',
            'faq.php' => 'FAQ - FreshPress Laundry Services'
        ];
        
        foreach ($files_to_fix as $file => $title) {
            $filepath = __DIR__ . '/' . $file;
            if (file_exists($filepath)) {
                $content = file_get_contents($filepath);
                
                // Replace head section
                $head_template = str_replace('{TITLE}', $title, $standard_head_template);
                
                // Find and replace the head section
                if (preg_match('/(<head>.*?<\/head>)/s', $content)) {
                    $content = preg_replace('/<head>.*?<\/head>/s', $head_template, $content);
                    
                    if (file_put_contents($filepath, $content)) {
                        echo "<div class='success'>✅ $file fixed successfully</div>";
                    } else {
                        echo "<div class='error'>❌ Failed to fix $file</div>";
                    }
                }
            }
        }
        
        echo "<div class='info'>";
        echo "<strong>Next Steps:</strong><br>";
        echo "1. Clear your browser cache (Ctrl+Shift+Del)<br>";
        echo "2. Hard refresh your pages (Ctrl+F5)<br>";
        echo "3. Check if styling is restored<br>";
        echo "4. Verify favicon is working<br>";
        echo "</div>";
        
    } else {
        ?>
        <div class="info">
            <strong>This script will:</strong><br>
            1. Restore Tailwind CSS links to all pages<br>
            2. Fix any missing JavaScript<br>
            3. Keep the favicon improvements<br>
            4. Restore proper styling to your pages
        </div>
        
        <form method="post">
            <button type="submit" name="fix_styling" value="1">
                🔧 Fix Styling Issues
            </button>
        </form>
        
        <h2>📋 Files that will be fixed:</h2>
        <ul>
            <li>index.php - Main homepage</li>
            <li>dashboard.php - Customer dashboard</li>
            <li>aboutus.php - About page</li>
            <li>services.php - Services page</li>
            <li>contact.php - Contact page</li>
            <li>login.php - Login page</li>
            <li>sign_up.php - Sign up page</li>
            <li>pricing.php - Pricing page</li>
            <li>profile.php - Profile page</li>
            <li>faq.php - FAQ page</li>
        </ul>
        
        <?php
    }
    ?>
    
    <div class="info">
        <strong>Manual Check:</strong> After running this fix, visit your homepage to verify that:<br>
        • Styling is restored (buttons, colors, layout)<br>
        • Favicon appears in browser tab<br>
        • All interactive elements work properly
    </div>
</body>
</html>
