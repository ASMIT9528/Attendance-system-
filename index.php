<?php
session_start();
if (isset($_SESSION['role'])) {
    // Redirect based on role
    switch($_SESSION['role']) {
        case 'admin': header("Location: admin/dashboard.php"); break;
        case 'teacher': header("Location: teacher/dashboard.php"); break;
        case 'student': header("Location: student/dashboard.php"); break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-box {
            background: #fff;
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0px 10px 25px rgba(0,0,0,0.3);
            width: 350px;
            text-align: center;
        }
        .login-box h2 {
            margin-bottom: 25px;
            color: #333;
            font-size: 24px;
        }
        .login-box input,
        .login-box select {
            width: 100%;
            padding: 12px;
            margin: 8px 0 18px;
            border: none;
            border-radius: 8px;
            background: #f1f1f1;
            font-size: 14px;
        }
        .login-box input:focus,
        .login-box select:focus {
            outline: none;
            background: #e0e0e0;
        }
        .login-box button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #ff512f, #dd2476);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        .login-box button:hover {
            background: linear-gradient(135deg, #dd2476, #ff512f);
            transform: scale(1.05);
        }
        .login-box .register-link {
            margin-top: 15px;
            font-size: 14px;
        }
        .login-box .register-link a {
            color: #2575fc;
            text-decoration: none;
            font-weight: bold;
        }
        .login-box .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>
        <form method="post" action="login.php">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" required>
                <option value="">-- Select Role --</option>
                <option value="admin">Admin</option>
                <option value="teacher">Teacher</option>
                <option value="student">Student</option>
            </select>
            <button type="submit">Login</button>
        </form>
        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>
