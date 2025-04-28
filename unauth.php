<?php
// Unauthorized Access Page for Cafeteria Project
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access | Cafeteria Management System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }
        
        .unauth-container {
            text-align: center;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
        }
        
        .unauth-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #dc3545;
            margin-bottom: 15px;
        }
        
        p {
            font-size: 18px;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        
        .cafeteria-image {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 25px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="unauth-container">
        <!-- Cafeteria image - replace with your actual image path -->
        <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" 
             alt="Cafeteria" class="cafeteria-image">
        
        <div class="unauth-icon">🚫</div>
        <h1>Unauthorized Access</h1>
        <p>You don't have permission to access this page of the Cafeteria Management System.</p>
        <p>Please contact the system administrator if you believe this is an error.</p>
        
        <a href="login.php" class="btn">Return to Login Page</a>
    </div>
</body>
</html>