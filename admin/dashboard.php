<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin') {
    header("Location: ../index.php");
    exit;
}
$name = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: "Segoe UI", Arial, sans-serif;
      margin: 0;
      background: linear-gradient(135deg, #1d2671, #c33764);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding: 40px 20px;
    }
    .dashboard {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0px 15px 35px rgba(0,0,0,0.3);
      padding: 40px;
      max-width: 1100px;
      width: 100%;
      animation: fadeIn 0.8s ease-in-out;
    }
    h2 {
      text-align: center;
      color: #222;
      font-size: 28px;
      margin-bottom: 10px;
    }
    p {
      text-align: center;
      color: #666;
      margin-bottom: 40px;
      font-size: 16px;
    }
    .menu {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 25px;
    }
    @media (max-width: 900px) {
      .menu {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    @media (max-width: 600px) {
      .menu {
        grid-template-columns: 1fr;
      }
    }
    .card {
      padding: 25px;
      text-align: center;
      border-radius: 15px;
      font-weight: bold;
      font-size: 18px;
      color: #fff;
      text-decoration: none;
      box-shadow: 0px 6px 18px rgba(0,0,0,0.25);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
      transform: translateY(-7px) scale(1.03);
      box-shadow: 0px 12px 28px rgba(0,0,0,0.4);
    }
    /* Unique gradient colors for each card */
    .add-student { background: linear-gradient(135deg, #43cea2, #185a9d); }
    .view-students { background: linear-gradient(135deg, #ff9966, #ff5e62); }
    .manage-teachers { background: linear-gradient(135deg, #7f00ff, #e100ff); }
    .attendance-report { background: linear-gradient(135deg, #56ab2f, #a8e063); }
    .settings { background: linear-gradient(135deg, #11998e, #38ef7d); }
    .logout { background: linear-gradient(135deg, #ff512f, #dd2476); }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="dashboard">
    <h2>Admin Dashboard</h2>
    <p>Welcome <?=htmlspecialchars($name)?></p>
    
    <div class="menu">
      <a href="add_student.php" class="card add-student">➕ Add Student</a>
      <a href="view_students.php" class="card view-students">👨‍🎓 View Students</a>
      <a href="manage_teachers.php" class="card manage-teachers">👩‍🏫 Manage Teachers</a>
      <a href="attendance_report.php" class="card attendance-report">📊 Attendance Report</a>
      <a href="settings.php" class="card settings">⚙ Settings</a>
      <a href="../logout.php" class="card logout">🚪 Logout</a>
    </div>
  </div>
</body>
</html>
