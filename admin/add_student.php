<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// ✅ Only Admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require_once '../config.php'; // expects $conn (mysqli)

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $roll_no  = trim($_POST['roll_no'] ?? '');
    $class    = trim($_POST['class'] ?? '');
    $section  = trim($_POST['section'] ?? '');
    $contact  = trim($_POST['contact'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // ✅ Validate input
    if ($name && $roll_no && $class && $section && $email && $password) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Start transaction
        $conn->begin_transaction();

        try {
            // 1️⃣ Insert into users table
            $stmt1 = $conn->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?, 'student')");
            if (!$stmt1) throw new Exception($conn->error);
            $stmt1->bind_param("sss", $name, $email, $hashed_password);
            if (!$stmt1->execute()) throw new Exception($stmt1->error);

            $user_id = $stmt1->insert_id; // get generated user ID
            $stmt1->close();

            // 2️⃣ Insert into students table
            $stmt2 = $conn->prepare("INSERT INTO students (name, user_id, roll_no, class, section, contact) VALUES (?,?,?,?,?,?)");
            if (!$stmt2) throw new Exception($conn->error);
            $stmt2->bind_param("sissss", $name, $user_id, $roll_no, $class, $section, $contact);
            if (!$stmt2->execute()) throw new Exception($stmt2->error);
            $stmt2->close();

            $conn->commit();
            $msg = "✅ Student added successfully. Email: $email";

        } catch (Exception $e) {
            $conn->rollback();
            $msg = "❌ Error: " . $e->getMessage();
        }

    } else {
        $msg = "❌ Please fill all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: "Segoe UI", Arial, sans-serif; background: linear-gradient(135deg,#43cea2,#185a9d); margin:0; padding:0; min-height:100vh; display:flex; justify-content:center; align-items:center; }
        .form-container { background:#fff; padding:30px 40px; border-radius:15px; box-shadow:0 10px 25px rgba(0,0,0,0.25); width:100%; max-width:400px; }
        h2 { text-align:center; color:#333; margin-bottom:15px; }
        .msg { padding:10px; border-radius:8px; margin-bottom:15px; font-size:14px; text-align:center; }
        .msg.success { background:#e0ffe0; color:#2e7d32; }
        .msg.error { background:#ffe0e0; color:#d8000c; }
        label { display:block; margin:8px 0 4px; font-weight:bold; color:#444; }
        input { width:100%; padding:10px; border-radius:8px; border:1px solid #ccc; margin-bottom:12px; font-size:14px; background:#f9f9f9; }
        input:focus { border-color:#185a9d; background:#eef7ff; outline:none; }
        button { width:100%; padding:12px; background:linear-gradient(135deg,#185a9d,#43cea2); border:none; border-radius:8px; color:#fff; font-size:16px; cursor:pointer; font-weight:bold; }
        button:hover { background:linear-gradient(135deg,#43cea2,#185a9d); transform:scale(1.05); }
        .back-link { display:block; text-align:center; margin-top:15px; color:#333; text-decoration:none; font-weight:bold; }
        .back-link:hover { color:#185a9d; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>➕ Add Student</h2>
        <?php if ($msg): ?>
            <div class="msg <?= (strpos($msg,'✅')!==false)?'success':'error' ?>">
                <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <label>Name</label>
            <input type="text" name="name" required>
            <label>Roll No</label>
            <input type="text" name="roll_no" required>
            <label>Class</label>
            <input type="text" name="class" required>
            <label>Section</label>
            <input type="text" name="section" required>
            <label>Contact</label>
            <input type="text" name="contact">
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Password</label>
            <input type="text" name="password" required>
            <button type="submit">Add Student</button>
        </form>
        <a href="dashboard.php" class="back-link">⬅ Back to Dashboard</a>
    </div>
</body>
</html>



