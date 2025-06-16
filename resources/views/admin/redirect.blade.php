<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .container {
            text-align: center;
            padding: 2rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 80%;
        }
        h1 {
            color: #4a5568;
        }
        .spinner {
            margin: 20px auto;
            width: 40px;
            height: 40px;
            border: 4px solid rgba(0,0,0,0.1);
            border-radius: 50%;
            border-top-color: #3182ce;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Redirecting</h1>
        <p>{{ $message }}</p>
        <div class="spinner"></div>
        <p>You will be redirected in a moment...</p>
    </div>

    <script>
        // Store admin login status in session storage
        sessionStorage.setItem('admin_login_redirect', 'true');
        
        // Add debug info to console
        console.log('Admin login successful');
        console.log('Setting redirect flag in sessionStorage');
        
        // Redirect after a short delay
        setTimeout(function() {
            window.location.href = "{{ $redirect_url }}";
        }, 1500);
    </script>
</body>
</html> 