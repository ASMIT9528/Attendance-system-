<?php
session_start();
require 'config.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = trim($_POST['role']);
    $errors = [];

    // Validation
    if (!$name) $errors[] = "Name required";
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email";
    if (strlen($password)<6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    if (!in_array($role,['admin','teacher','student'])) $errors[]="Select a valid role";

    // Check existing email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows>0) $errors[]="Email already registered";

    // Insert user
    if (empty($errors)) {
        $hashed = password_hash($password,PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users(name,email,password,role) VALUES(?,?,?,?)");
        $stmt->bind_param("ssss",$name,$email,$hashed,$role);
        if ($stmt->execute()) {
            // Auto-login after registration
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;

            // Redirect to respective dashboard
            if ($role === 'admin') header("Location: admin/dashboard.php");
            if ($role === 'teacher') header("Location: teacher/dashboard.php");
            if ($role === 'student') header("Location: student/dashboard.php");
            exit;
        } else $errors[]="Something went wrong. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Register</title>
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
        .register-box {
            background: #fff;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0px 10px 25px rgba(0,0,0,0.3);
            text-align: center;
            width: 350px;
        }
        .register-box h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .register-box input,
        .register-box select {
            width: 100%;
            padding: 12px;
            margin: 5px 0 15px;
            border: none;
            border-radius: 8px;
            background: #f1f1f1;
            font-size: 14px;
        }
        .register-box button {
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
        .register-box button:hover {
            background: linear-gradient(135deg, #dd2476, #ff512f);
            transform: scale(1.05);
        }
        .register-box .login-link {
            margin-top: 15px;
            font-size: 14px;
        }
        .register-box .login-link a {
            color: #2575fc;
            text-decoration: none;
            font-weight: bold;
        }
        .register-box .login-link a:hover {
            text-decoration: underline;
        }
        .errors {
            color: red;
            font-size: 13px;
            margin-bottom: 10px;
            text-align: left;
        }
    </style>
</head>
<body>
<div class="register-box">
    <h2>Register</h2>
    <?php
    if(!empty($errors)) {
        echo '<div class="errors">';
        foreach($errors as $e) echo "• " . htmlspecialchars($e) . "<br>";
        echo '</div>';
    }
    ?>
    <form method="post">
        <input type="text" name="name" placeholder="Full Name" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
        <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="admin">Admin</option>
            <option value="teacher">Teacher</option>
            <option value="student">Student</option>
        </select><br>
        <button type="submit">Register</button>
    </form>
    <div class="login-link">
        Already have an account? <a href="index.php">Login here</a>
    </div>
</div>
</body>
</html>
