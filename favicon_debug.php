<?php
// Debug favicon loading issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

$favicon_path = 'images/favicon.png';
$absolute_path = __DIR__ . '/' . $favicon_path;

echo "<h2>Favicon Debug Information</h2>";

echo "<h3>File Information:</h3>";
echo "Path: $favicon_path<br>";
echo "Absolute path: $absolute_path<br>";
echo "File exists: " . (file_exists($absolute_path) ? 'Yes' : 'No') . "<br>";
echo "File readable: " . (is_readable($absolute_path) ? 'Yes' : 'No') . "<br>";

if (file_exists($absolute_path)) {
    $file_info = getimagesize($absolute_path);
    echo "Image size: " . $file_info[0] . "x" . $file_info[1] . "<br>";
    echo "Image type: " . $file_info['mime'] . "<br>";
    echo "File size: " . filesize($absolute_path) . " bytes<br>";
    echo "Last modified: " . date('Y-m-d H:i:s', filemtime($absolute_path)) . "<br>";
    
    // Generate cache busting version
    $version = filemtime($absolute_path);
    echo "Cache busting URL: images/favicon.png?v=$version<br>";
}

echo "<h3>Direct Image Test:</h3>";
echo "<img src='$favicon_path' alt='Favicon' style='border: 1px solid #ccc;'>";
echo "<img src='$favicon_path?v=$version' alt='Favicon with cache bust' style='border: 1px solid #ccc;'>";

echo "<h3>HTTP Headers Test:</h3>";
echo "<p>Check browser developer tools (F12) > Network tab for favicon requests</p>";
echo "<p>Look for any 404 errors or failed requests for favicon.png</p>";

echo "<h3>Troubleshooting Steps:</h3>";
echo "<ol>";
echo "<li>Clear browser cache completely (Ctrl+Shift+Del)</li>";
echo "<li>Try hard refresh (Ctrl+F5 or Cmd+Shift+R)</li>";
echo "<li>Check browser developer tools for favicon loading errors</li>";
echo "<li>Try accessing favicon directly: /images/favicon.png</li>";
echo "<li>Verify server is serving PNG files correctly</li>";
echo "</ol>";

// Test different favicon formats
echo "<h3>Alternative Favicon Formats:</h3>";
echo "<p>Sometimes browsers prefer different formats. Consider adding:</p>";
echo "<ul>";
echo "<li>ICO format: favicon.ico</li>";
echo "<li>SVG format: favicon.svg</li>";
echo "<li>Apple touch icon: apple-touch-icon.png</li>";
echo "</ul>";
?>
