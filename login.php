<?php
session_start();
require 'config.php'; // expects $conn (mysqli)

// If already logged in, redirect
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin')   header("Location: admin/dashboard.php");
    if ($_SESSION['role'] === 'teacher') header("Location: teacher/dashboard.php");
    if ($_SESSION['role'] === 'student') header("Location: student/dashboard.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';

    if ($email && $password && $role) {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($user = $res->fetch_assoc()) {
            if ($password === $user['password'] || password_verify($password, $user['password'])) {
                if ($user['role'] !== $role) {
                    $error = "❌ Role mismatch!";
                } else {
                    $_SESSION['id']   = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];

                    if ($role === 'admin')   header("Location: admin/dashboard.php");
                    if ($role === 'teacher') header("Location: teacher/dashboard.php");
                    if ($role === 'student') header("Location: student/dashboard.php");
                    exit;
                }
            } else {
                $error = "❌ Invalid password!";
            }
        } else {
            $error = "❌ User not found!";
        }
    } else {
        $error = "⚠ Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: "Segoe UI", Arial, sans-serif;
      background: linear-gradient(135deg,#667eea,#764ba2);
      margin:0; padding:0;
      display:flex; justify-content:center; align-items:center;
      height:100vh;
    }
    .login-box {
      background:#fff;
      padding:30px 35px;
      border-radius:15px;
      box-shadow:0 10px 25px rgba(0,0,0,0.2);
      width:100%; max-width:380px;
      animation:fadeIn 0.6s ease-in-out;
    }
    h2 { text-align:center; margin:0 0 15px; color:#333; }
    label { display:block; margin-top:10px; font-weight:bold; color:#444; }
    input, select {
      width:100%; padding:10px;
      margin-top:6px;
      border:1px solid #ccc;
      border-radius:8px;
      font-size:14px;
    }
    button {
      width:100%; margin-top:15px;
      padding:12px;
      border:none; border-radius:8px;
      background:linear-gradient(135deg,#667eea,#764ba2);
      color:#fff; font-size:16px; font-weight:bold;
      cursor:pointer; transition:0.3s;
    }
    button:hover { transform:scale(1.05); }
    .msg {
      margin-top:12px;
      padding:10px;
      border-radius:8px;
      text-align:center;
      font-size:14px;
    }
    .error { background:#ffe0e0; color:#b22222; }
    @keyframes fadeIn {
      from { opacity:0; transform:translateY(-10px); }
      to { opacity:1; transform:translateY(0); }
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>🔑 Login</h2>
    <?php if ($error): ?>
      <div class="msg error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
      <label>Email</label>
      <input type="email" name="email" required>
      <label>Password</label>
      <input type="password" name="password" required>
      <label>Role</label>
      <select name="role" required>
        <option value="">-- Select Role --</option>
        <option value="admin">Admin</option>
        <option value="teacher">Teacher</option>
        <option value="student">Student</option>
      </select>
      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
