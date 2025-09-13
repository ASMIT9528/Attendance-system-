<?php
session_start();
require '../config.php';

// ✅ Only teacher can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../index.php");
    exit;
}

$msg = "";

// ✅ When form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance'])) {
    $date = date('Y-m-d'); // today's date

    foreach ($_POST['attendance'] as $student_id => $status) {
        // Check if attendance already exists for this student and date
        $check = $conn->prepare("SELECT id FROM attendance WHERE student_id=? AND date=?");
        $check->bind_param("is", $student_id, $date);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            // Update existing record
            $stmt = $conn->prepare("UPDATE attendance SET status=? WHERE student_id=? AND date=?");
            $stmt->bind_param("sis", $status, $student_id, $date);
        } else {
            // Insert new record
            $stmt = $conn->prepare("INSERT INTO attendance (student_id, date, status) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $student_id, $date, $status);
        }
        $stmt->execute();
    }
    $msg = "✅ Attendance saved for " . $date;
}

// ✅ Fetch students
$students = $conn->query("SELECT * FROM students ORDER BY class, section, roll_no");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mark Attendance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #89f7fe, #66a6ff);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 40px 15px;
        }
        .container {
            background: #fff;
            padding: 25px 30px;
            border-radius: 15px;
            width: 100%;
            max-width: 900px;
            box-shadow: 0px 10px 25px rgba(0,0,0,0.15);
            animation: fadeIn 0.6s ease-in-out;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #444;
        }
        .msg {
            background: #e0ffe0;
            color: #2e7d32;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background: #667eea;
            color: #fff;
            padding: 12px;
            text-align: center;
        }
        table td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        select {
            padding: 6px;
            border-radius: 6px;
            border: 1px solid #ccc;
            background: #f9f9f9;
            font-size: 14px;
        }
        select:focus {
            outline: none;
            border-color: #667eea;
            background: #eef2ff;
        }
        button {
            display: block;
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: linear-gradient(135deg, #764ba2, #667eea);
            transform: scale(1.05);
        }
        .back-link {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            font-weight: bold;
            color: #444;
            transition: 0.3s;
        }
        .back-link:hover {
            color: #667eea;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media(max-width: 600px) {
            table th, table td {
                font-size: 12px;
                padding: 6px;
            }
            button {
                font-size: 14px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📋 Mark Attendance</h2>
        <?php if ($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>

        <form method="post">
            <table>
                <tr>
                    <th>Roll No</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Status</th>
                </tr>
                <?php while ($row = $students->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['roll_no']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['class']) ?></td>
                    <td><?= htmlspecialchars($row['section']) ?></td>
                    <td>
                        <select name="attendance[<?= $row['id'] ?>]">
                            <option value="Present">✅ Present</option>
                            <option value="Absent">❌ Absent</option>
                            <option value="Late">⏰ Late</option>
                        </select>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
            <button type="submit">💾 Save Attendance</button>
        </form>

        <p style="text-align:center;">
            <a href="dashboard.php" class="back-link">⬅ Back to Dashboard</a>
        </p>
    </div>
</body>
</html>
