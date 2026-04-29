<?php
// Set content type to text/html
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Server Diagnostics</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2 { color: #333; }
        .test-section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .test-btn { padding: 8px 15px; background: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .test-btn:hover { background: #0052a3; }
        #testResult { margin-top: 15px; }
        table { border-collapse: collapse; width: 100%; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>PHP Server Diagnostics</h1>
    
    <div class="test-section">
        <h2>1. Test JSON Output</h2>
        <p>This test checks if the server can output valid JSON:</p>
        <button class="test-btn" onclick="testJson()">Test JSON Output</button>
        <div id="jsonTestResult"></div>
    </div>
    
    <div class="test-section">
        <h2>2. Test Form Submission</h2>
        <p>This test simulates a subscription payment submission:</p>
        <form id="testForm">
            <table>
                <tr>
                    <th>Field</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>Subscription ID</td>
                    <td><input type="text" name="subscription_id" value="1"></td>
                </tr>
                <tr>
                    <td>Payment Method</td>
                    <td>
                        <select name="payment_method">
                            <option value="mpesa_manual">M-Pesa Manual</option>
                            <option value="mpesa">M-Pesa</option>
                            <option value="direct">Direct</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Phone Number</td>
                    <td><input type="text" name="phone_number" value="254712345678"></td>
                </tr>
                <tr>
                    <td>M-Pesa Code</td>
                    <td><input type="text" name="mpesa_code" value="ABCDEF1234"></td>
                </tr>
            </table>
            <br>
            <button type="button" class="test-btn" onclick="testSubmit()">Test Submission</button>
        </form>
        <div id="submitTestResult"></div>
    </div>
    
    <div class="test-section">
        <h2>3. PHP Information</h2>
        <p>Basic PHP configuration information:</p>
        <table>
            <tr>
                <th>Setting</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>PHP Version</td>
                <td><?php echo phpversion(); ?></td>
            </tr>
            <tr>
                <td>display_errors</td>
                <td><?php echo ini_get('display_errors') ? 'On' : 'Off'; ?></td>
            </tr>
            <tr>
                <td>output_buffering</td>
                <td><?php echo ini_get('output_buffering'); ?></td>
            </tr>
            <tr>
                <td>Session Status</td>
                <td><?php 
                    switch(session_status()) {
                        case PHP_SESSION_DISABLED: echo "Sessions disabled"; break;
                        case PHP_SESSION_NONE: echo "Sessions enabled but none active"; break;
                        case PHP_SESSION_ACTIVE: echo "Session active"; break;
                    }
                ?></td>
            </tr>
        </table>
    </div>
    
    <script>
        function testJson() {
            const resultDiv = document.getElementById('jsonTestResult');
            resultDiv.innerHTML = '<p>Testing...</p>';
            
            fetch('test_json_response.php')
                .then(response => {
                    resultDiv.innerHTML += `<p>Response status: ${response.status} ${response.statusText}</p>`;
                    return response.text();
                })
                .then(text => {
                    resultDiv.innerHTML += `<p>Raw response:</p><pre>${escapeHtml(text)}</pre>`;
                    
                    try {
                        // Try to parse as JSON
                        const data = JSON.parse(text);
                        resultDiv.innerHTML += `<p class="success">✓ Valid JSON response</p>`;
                        resultDiv.innerHTML += `<p>Parsed data:</p><pre>${JSON.stringify(data, null, 2)}</pre>`;
                    } catch (e) {
                        resultDiv.innerHTML += `<p class="error">✗ Invalid JSON: ${e.message}</p>`;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML += `<p class="error">Error: ${error.message}</p>`;
                });
        }
        
        function testSubmit() {
            const resultDiv = document.getElementById('submitTestResult');
            resultDiv.innerHTML = '<p>Testing submission...</p>';
            
            const form = document.getElementById('testForm');
            const formData = new FormData(form);
            
            // Log what we're sending
            let formDataLog = 'Sending form data:\n';
            for (let [key, value] of formData.entries()) {
                formDataLog += `${key}: ${value}\n`;
            }
            resultDiv.innerHTML += `<pre>${formDataLog}</pre>`;
            
            // Test each endpoint
            const endpoints = [
                'test_subscription_payment.php',
                'fixed_subscription_payment.php',
                'process_subscription_payment.php'
            ];
            
            Promise.all(endpoints.map(endpoint => {
                return fetch(endpoint, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    return response.text().then(text => {
                        return {
                            endpoint,
                            status: response.status,
                            contentType: response.headers.get('content-type'),
                            text
                        };
                    });
                })
                .catch(error => {
                    return {
                        endpoint,
                        error: error.message
                    };
                });
            }))
            .then(results => {
                results.forEach(result => {
                    resultDiv.innerHTML += `<h3>Endpoint: ${result.endpoint}</h3>`;
                    
                    if (result.error) {
                        resultDiv.innerHTML += `<p class="error">Error: ${result.error}</p>`;
                        return;
                    }
                    
                    resultDiv.innerHTML += `<p>Status: ${result.status}</p>`;
                    resultDiv.innerHTML += `<p>Content-Type: ${result.contentType || 'Not set'}</p>`;
                    resultDiv.innerHTML += `<p>Raw response:</p><pre>${escapeHtml(result.text)}</pre>`;
                    
                    try {
                        // Try to parse as JSON
                        const data = JSON.parse(result.text);
                        resultDiv.innerHTML += `<p class="success">✓ Valid JSON response</p>`;
                    } catch (e) {
                        resultDiv.innerHTML += `<p class="error">✗ Invalid JSON: ${e.message}</p>`;
                    }
                });
            });
        }
        
        function escapeHtml(text) {
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</body>
</html> 