<?php
session_start();
require '../config.php';

// ✅ Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$msg = "";

// ✅ Add Teacher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_teacher'])) {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // secure hash
    $role     = "teacher";

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);
    if ($stmt->execute()) {
        $msg = "✅ Teacher added successfully!";
    } else {
        $msg = "❌ Error: " . $conn->error;
    }
}

// ✅ Delete Teacher
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id=$id AND role='teacher'");
    $msg = "🗑️ Teacher deleted successfully!";
}

// ✅ Fetch all teachers
$result = $conn->query("SELECT id, name, email FROM users WHERE role='teacher' ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Teachers</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      margin: 0;
      font-family: "Segoe UI", Arial, sans-serif;
      background: linear-gradient(135deg, #1d976c, #93f9b9);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding: 40px 20px;
    }
    .container {
      background: #fff;
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0px 10px 25px rgba(0,0,0,0.3);
      width: 95%;
      max-width: 900px;
      animation: fadeIn 0.7s ease-in-out;
    }
    h2 {
      text-align: center;
      color: #333;
      margin-bottom: 20px;
    }
    .msg {
      text-align: center;
      padding: 10px;
      margin-bottom: 20px;
      border-radius: 8px;
      font-weight: bold;
      color: #fff;
      background: linear-gradient(135deg, #36d1dc, #5b86e5);
    }
    form {
      display: grid;
      grid-template-columns: repeat(auto-fit,minmax(180px,1fr));
      gap: 15px;
      margin-bottom: 25px;
    }
    input {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    button {
      grid-column: span 2;
      padding: 12px;
      border: none;
      border-radius: 8px;
      background: linear-gradient(135deg, #ff512f, #dd2476);
      color: #fff;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }
    button:hover {
      transform: scale(1.05);
      background: linear-gradient(135deg, #dd2476, #ff512f);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      border-radius: 10px;
      overflow: hidden;
    }
    th, td {
      padding: 12px;
      text-align: center;
      font-size: 14px;
    }
    th {
      background: linear-gradient(135deg, #1e3c72, #2a5298);
      color: #fff;
    }
    tr:nth-child(even) { background: #f9f9f9; }
    tr:hover { background: #f1f1f1; }
    .delete-btn {
      padding: 6px 12px;
      background: linear-gradient(135deg, #ff416c, #ff4b2b);
      color: #fff;
      border-radius: 6px;
      text-decoration: none;
      transition: 0.3s;
    }
    .delete-btn:hover {
      background: linear-gradient(135deg, #ff4b2b, #ff416c);
      transform: scale(1.05);
    }
    .back-btn {
      display: inline-block;
      margin-top: 15px;
      padding: 10px 20px;
      background: linear-gradient(135deg, #36d1dc, #5b86e5);
      color: #fff;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      transition: 0.3s;
    }
    .back-btn:hover {
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
    <h2>👨‍🏫 Manage Teachers</h2>

    <?php if ($msg): ?>
      <p class="msg"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <!-- ✅ Add Teacher Form -->
    <form method="post">
      <input type="text" name="name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit" name="add_teacher">➕ Add Teacher</button>
    </form>

    <!-- ✅ Teacher List -->
    <table>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Action</th>
      </tr>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td>
              <a class="delete-btn" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this teacher?');">🗑 Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="4">No teachers found.</td></tr>
      <?php endif; ?>
    </table>

    <a href="dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
  </div>
</body>
</html>
