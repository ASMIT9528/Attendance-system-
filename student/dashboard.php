<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit;
}
$name = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    body {
        margin: 0;
        font-family: "Segoe UI", Arial, sans-serif;
        background: linear-gradient(135deg, #ff9a9e, #fecfef);
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .dashboard {
        background: #fff;
        padding: 40px 30px;
        border-radius: 15px;
        width: 100%;
        max-width: 360px;
        text-align: center;
        box-shadow: 0px 12px 30px rgba(0,0,0,0.25);
        animation: fadeIn 0.7s ease-in-out;
    }
    .dashboard h2 {
        color: #333;
        margin-bottom: 10px;
        font-size: 24px;
    }
    .dashboard p {
        font-size: 15px;
        color: #555;
        margin-bottom: 25px;
    }
    .dashboard strong {
        color: #222;
    }
    .menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .menu li {
        margin: 15px 0;
    }
    .menu a {
        display: block;
        padding: 12px;
        border-radius: 10px;
        background: linear-gradient(135deg, #36d1dc, #5b86e5);
        color: #fff;
        text-decoration: none;
        font-weight: bold;
        transition: 0.3s;
    }
    .menu a:hover {
        background: linear-gradient(135deg, #5b86e5, #36d1dc);
        transform: scale(1.05);
    }
    .menu .logout a {
        background: linear-gradient(135deg, #ff512f, #dd2476);
    }
    .menu .logout a:hover {
        background: linear-gradient(135deg, #dd2476, #ff512f);
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @media (max-width: 480px) {
        .dashboard {
            padding: 25px 20px;
            width: 90%;
        }
    }
</style>
</head>
<body>
<div class="dashboard">
    <h2>Student Dashboard</h2>
    <p>Welcome, <strong><?= htmlspecialchars($name) ?></strong></p>
    <ul class="menu">
        <li><a href="view_attendance.php">📖 View Attendance</a></li>
        <li class="logout"><a href="../logout.php">🚪 Logout</a></li>
    </ul>
</div>
</body>
</html>
