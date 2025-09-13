<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logging Out...</title>
    <meta http-equiv="refresh" content="2;url=index.php">
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background: linear-gradient(135deg, #ff758c, #ff7eb3);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .logout-box {
            background: #fff;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0px 8px 25px rgba(0,0,0,0.2);
            text-align: center;
            animation: fadeIn 0.6s ease-in-out;
        }
        h2 {
            color: #333;
        }
        p {
            color: #555;
            font-size: 14px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="logout-box">
        <h2>👋 You have been logged out</h2>
        <p>Redirecting to login page...</p>
    </div>
</body>
</html>
