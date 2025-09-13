<?php
session_start();
require_once '../config.php'; // expects $conn (mysqli)

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Fetch current admin info
$adminId = $_SESSION['id'] ?? 0;
$admin = null;
$adminSql = "SELECT id, name, email FROM users WHERE id=? AND role='admin'";
$stmt = $conn->prepare($adminSql);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    $admin = $res->fetch_assoc();
}

// Fetch school settings safely
$school = null;
$schoolResult = $conn->query("SELECT * FROM settings LIMIT 1");
if ($schoolResult && $schoolResult->num_rows > 0) {
    $school = $schoolResult->fetch_assoc();
}

// Handle form submissions
$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_admin'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if ($name && $email) {
            if ($password) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $upd = $conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=? AND role='admin'");
                $upd->bind_param("sssi", $name, $email, $hash, $adminId);
            } else {
                $upd = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=? AND role='admin'");
                $upd->bind_param("ssi", $name, $email, $adminId);
            }
            if ($upd->execute()) $success = "✅ Admin profile updated!";
            else $error = "❌ Error updating admin profile.";
        }
    }

    if (isset($_POST['update_school'])) {
        $school_name = trim($_POST['school_name']);
        $address = trim($_POST['address']);
        $contact = trim($_POST['contact']);
        if ($school) {
            $upd = $conn->prepare("UPDATE settings SET school_name=?, address=?, contact=? WHERE id=?");
            $upd->bind_param("sssi", $school_name, $address, $contact, $school['id']);
        } else {
            $upd = $conn->prepare("INSERT INTO settings (school_name, address, contact) VALUES (?,?,?)");
            $upd->bind_param("sss", $school_name, $address, $contact);
        }
        if ($upd->execute()) $success = "✅ School settings updated!";
        else $error = "❌ Error updating school settings.";
    }
    header("Location: settings.php?msg=" . urlencode($success ?: $error));
    exit;
}

// messages
$msg = $_GET['msg'] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Settings</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body{
      font-family: "Segoe UI", Arial, sans-serif;
      background: linear-gradient(135deg,#667eea,#764ba2);
      margin:0; padding:28px;
      color:#333;
    }
    .wrap{max-width:900px;margin:0 auto;}
    .card{
      background:#fff;
      padding:22px;
      border-radius:14px;
      margin-bottom:20px;
      box-shadow:0 8px 22px rgba(0,0,0,0.15);
      animation:fadeIn 0.6s ease-in-out;
    }
    h1{margin:0 0 16px 0;color:#444;}
    h2{margin:0 0 12px 0;color:#555;}
    label{display:block;margin:10px 0 6px;}
    input,textarea{
      width:100%;
      padding:10px;
      border-radius:8px;
      border:1px solid #ccc;
      font-size:14px;
    }
    button{
      margin-top:12px;
      padding:12px 16px;
      border:none;
      border-radius:10px;
      background:linear-gradient(135deg,#36d1dc,#5b86e5);
      color:#fff;
      font-weight:600;
      cursor:pointer;
      transition:0.3s;
    }
    button:hover{transform:scale(1.05);}
    .msg{padding:10px;margin-bottom:12px;border-radius:8px;}
    .success{background:#e0ffe6;color:#228b22;}
    .error{background:#ffe0e0;color:#b22222;}
    a.back{display:inline-block;margin-top:12px;text-decoration:none;font-weight:600;color:#444;}
    @keyframes fadeIn{from{opacity:0;transform:translateY(-12px);}to{opacity:1;transform:translateY(0);}}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>⚙ Settings</h1>
      <?php if($msg): ?>
        <div class="msg <?= strpos($msg,'✅')!==false?'success':'error' ?>"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>
    </div>

    <div class="card">
      <h2>👤 Admin Profile</h2>
      <form method="post">
        <input type="hidden" name="update_admin" value="1">
        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($admin['name'] ?? '') ?>" required>
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required>
        <label>New Password (leave blank if unchanged)</label>
        <input type="password" name="password">
        <button type="submit">💾 Save Changes</button>
      </form>
    </div>

    <div class="card">
      <h2>🏫 School Info</h2>
      <form method="post">
        <input type="hidden" name="update_school" value="1">
        <label>School Name</label>
        <input type="text" name="school_name" value="<?= htmlspecialchars($school['school_name'] ?? '') ?>" required>
        <label>Address</label>
        <textarea name="address" rows="3"><?= htmlspecialchars($school['address'] ?? '') ?></textarea>
        <label>Contact</label>
        <input type="text" name="contact" value="<?= htmlspecialchars($school['contact'] ?? '') ?>">
        <button type="submit">💾 Update School</button>
      </form>
    </div>

    <a href="dashboard.php" class="back">⬅ Back to Dashboard</a>
  </div>
</body>
</html>
