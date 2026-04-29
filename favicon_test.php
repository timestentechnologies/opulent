<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favicon Test</title>
    <link rel="icon" type="image/png" sizes="32x32" href="images/favicon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon.png">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        img { max-width: 100px; height: auto; }
    </style>
</head>
<body>
    <h1>Favicon Test Page</h1>
    
    <div class="test-section">
        <h2>Direct Favicon Image Test</h2>
        <p>If you can see the image below, the favicon file is accessible:</p>
        <img src="images/favicon.png" alt="Favicon" onerror="this.nextElementSibling.style.display='block'; this.style.display='none'">
        <div style="display:none; color:red;">Error loading favicon image!</div>
    </div>
    
    <div class="test-section">
        <h2>Browser Tab Test</h2>
        <p>Check the browser tab - you should see the favicon icon there.</p>
        <p>If not, try clearing browser cache and refreshing.</p>
    </div>
    
    <div class="test-section">
        <h2>Troubleshooting Steps</h2>
        <ol>
            <li>Clear browser cache (Ctrl+F5 or Cmd+Shift+R)</li>
            <li>Check browser developer tools (F12) for favicon loading errors</li>
            <li>Verify the favicon appears in the browser tab</li>
            <li>Try accessing the favicon directly: /images/favicon.png</li>
        </ol>
    </div>
</body>
</html>
