<?php
// Test file to verify server is working

header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        h1 {
            color: #333;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            overflow: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>PHP Server Test</h1>
        <p class="success">PHP server is working correctly!</p>
        
        <h2>Server Information:</h2>
        <pre>
PHP Version: <?php echo phpversion(); ?>

Server Software: <?php echo $_SERVER['SERVER_SOFTWARE']; ?>

Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?>

Request URI: <?php echo $_SERVER['REQUEST_URI']; ?>

Script Name: <?php echo $_SERVER['SCRIPT_NAME']; ?>
        </pre>
        
        <h2>Links to test:</h2>
        <ul>
            <li><a href="/">Home (AngularJS app)</a></li>
            <li><a href="/products">Products page (AngularJS route)</a></li>
            <li><a href="/test.html">Static HTML test</a></li>
        </ul>
    </div>
</body>
</html> 