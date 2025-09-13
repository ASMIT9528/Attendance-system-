<?php
session_start();
require '../config.php';

// ✅ Only student can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit;
}

$name = $_SESSION['name'] ?? 'Student';

// ✅ Fetch assignments (adjust table/column names as per your DB)
$result = $conn->query("SELECT title, description, due_date, file_path FROM assignments ORDER BY due_date ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Assignments</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      margin: 0;
      font-family: "Segoe UI", Arial, sans-serif;
      background: linear-gradient(135deg, #ff9a9e, #fecfef);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding: 40px 20px;
    }
    .container {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0px 10px 25px rgba(0,0,0,0.3);
      padding: 25px;
      width: 95%;
      max-width: 800px;
      animation: fadeIn 0.7s ease-in-out;
    }
    h2 {
      text-align: center;
      color: #333;
      margin-bottom: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
      border-radius: 10px;
      overflow: hidden;
    }
    table th, table td {
      padding: 12px;
      text-align: center;
      font-size: 14px;
    }
    table th {
      background: linear-gradient(135deg, #f7971e, #ffd200);
      color: #fff;
    }
    table tr:nth-child(even) {
      background: #f9f9f9;
    }
    table tr:hover {
      background: #f1f1f1;
    }
    .back-btn {
      display: inline-block;
      padding: 10px 20px;
      background: linear-gradient(135deg, #ff512f, #dd2476);
      color: #fff;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      transition: 0.3s;
    }
    .back-btn:hover {
      background: linear-gradient(135deg, #dd2476, #ff512f);
      transform: scale(1.05);
    }
    .download-btn {
      padding: 6px 12px;
      background: linear-gradient(135deg, #36d1dc, #5b86e5);
      color: #fff;
      border-radius: 6px;
      text-decoration: none;
      transition: 0.3s;
    }
    .download-btn:hover {
      background: linear-gradient(135deg, #5b86e5, #36d1dc);
      transform: scale(1.05);
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>📚 Assignments - <?= htmlspecialchars($name) ?></h2>
    <table>
      <tr>
        <th>Title</th>
        <th>Description</th>
        <th>Due Date</th>
        <th>Download</th>
      </tr>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= htmlspecialchars($row['due_date']) ?></td>
            <td>
              <?php if (!empty($row['file_path'])): ?>
                <a class="download-btn" href="../uploads/<?= htmlspecialchars($row['file_path']) ?>" target="_blank">⬇ Download</a>
              <?php else: ?>
                No File
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="4">No assignments found.</td></tr>
      <?php endif; ?>
    </table>
    <a href="dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
  </div>
</body>
</html>
