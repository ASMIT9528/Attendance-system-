<?php
session_start();
require_once '../config.php'; // expects $conn (mysqli)

// Redirect if not logged in
if (!isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit;
}

$role = $_SESSION['role'];
$userId = $_SESSION['id'];
$name   = $_SESSION['name'];

$msg = "";

// Query depends on role
if ($role === 'student') {
    // Find student_id linked to this user
    $stuStmt = $conn->prepare("SELECT id FROM students WHERE user_id=?");
    $stuStmt->bind_param("i", $userId);
    $stuStmt->execute();
    $stuRes = $stuStmt->get_result();
    if ($stuRes && $stuRes->num_rows > 0) {
        $stuRow = $stuRes->fetch_assoc();
        $studentId = $stuRow['id'];

        $stmt = $conn->prepare("
            SELECT a.date, a.status, s.roll_no, s.class, s.section
            FROM attendance a
            JOIN students s ON a.student_id = s.id
            WHERE a.student_id=?
            ORDER BY a.date DESC
        ");
        $stmt->bind_param("i", $studentId);
    } else {
        $msg = "❌ No student profile found.";
    }
} elseif ($role === 'teacher' || $role === 'admin') {
    $stmt = $conn->prepare("
        SELECT a.date, a.status, s.name, s.roll_no, s.class, s.section
        FROM attendance a
        JOIN students s ON a.student_id = s.id
        ORDER BY a.date DESC
    ");
} else {
    $msg = "❌ Unauthorized access.";
}

$records = [];
if (isset($stmt)) {
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $records[] = $row;
        }
    } else {
        $msg = "ℹ No attendance records found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Attendance</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: "Segoe UI", Arial, sans-serif;
      background: linear-gradient(135deg,#43cea2,#185a9d);
      margin: 0;
      padding: 20px;
      color: #333;
    }
    .container {
      max-width: 900px;
      margin: auto;
      background: #fff;
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0px 8px 20px rgba(0,0,0,0.25);
      animation: fadeIn 0.6s ease-in-out;
    }
    h2 {
      margin-top: 0;
      color: #444;
      text-align: center;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    table th, table td {
      padding: 10px;
      border: 1px solid #ddd;
      text-align: center;
    }
    table th {
      background: #185a9d;
      color: #fff;
    }
    table tr:nth-child(even) {
      background: #f9f9f9;
    }
    .msg {
      margin: 15px 0;
      padding: 10px;
      border-radius: 8px;
      text-align: center;
    }
    .success { background: #e0ffe6; color: #228b22; }
    .info { background: #f0f8ff; color: #004085; }
    .error { background: #ffe0e0; color: #b22222; }
    a.back {
      display: inline-block;
      margin-top: 15px;
      text-decoration: none;
      font-weight: bold;
      color: #185a9d;
    }
    a.back:hover {
      text-decoration: underline;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>📋 Attendance Records</h2>
    <p style="text-align:center;">Welcome, <b><?= htmlspecialchars($name) ?></b> (<?= ucfirst($role) ?>)</p>

    <?php if ($msg): ?>
      <div class="msg <?= strpos($msg,'❌')!==false?'error':(strpos($msg,'ℹ')!==false?'info':'success') ?>">
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <?php if ($records): ?>
      <table>
        <tr>
          <?php if ($role !== 'student'): ?>
            <th>Name</th>
          <?php endif; ?>
          <th>Roll No</th>
          <th>Class</th>
          <th>Section</th>
          <th>Date</th>
          <th>Status</th>
        </tr>
        <?php foreach ($records as $row): ?>
          <tr>
            <?php if ($role !== 'student'): ?>
              <td><?= htmlspecialchars($row['name'] ?? '') ?></td>
            <?php endif; ?>
            <td><?= htmlspecialchars($row['roll_no']) ?></td>
            <td><?= htmlspecialchars($row['class']) ?></td>
            <td><?= htmlspecialchars($row['section']) ?></td>
            <td><?= htmlspecialchars($row['date']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>

    <a href="dashboard.php" class="back">⬅ Back to Dashboard</a>
  </div>
</body>
</html>
