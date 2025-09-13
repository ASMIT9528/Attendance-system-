<?php
session_start();
require_once '../config.php'; // expects $conn (mysqli)

// Only admin allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Helper: sanitize date input
function sanitize_date($d) {
    $d = trim($d);
    if (!$d) return null;
    $t = strtotime($d);
    return $t ? date('Y-m-d', $t) : null;
}

// default date range: last 30 days
$to = sanitize_date($_GET['to'] ?? date('Y-m-d'));
$from = sanitize_date($_GET['from'] ?? date('Y-m-d', strtotime('-29 days', strtotime($to))));

if (!$from) $from = date('Y-m-d', strtotime('-29 days', strtotime($to)));
if (!$to)   $to   = date('Y-m-d');

// Export CSV?
$export_csv = isset($_GET['export']) && $_GET['export'] === 'csv';

// Fetch per-student attendance summary between dates
$sql = "
SELECT 
  s.id,
  s.roll_no,
  s.name,
  s.class,
  s.section,
  COALESCE(SUM(a.status = 'Present'),0) AS present_count,
  COALESCE(SUM(a.status = 'Absent'),0) AS absent_count,
  COALESCE(SUM(a.status = 'Late'),0) AS late_count,
  COALESCE(COUNT(a.id),0) AS total_records
FROM students s
LEFT JOIN attendance a
  ON s.id = a.student_id
  AND a.date BETWEEN ? AND ?
GROUP BY s.id
ORDER BY s.class, s.section, s.roll_no, s.name
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $from, $to);
$stmt->execute();
$result = $stmt->get_result();

// Overall totals
$totSql = "
SELECT 
  COALESCE(SUM(status='Present'),0) AS total_present,
  COALESCE(SUM(status='Absent'),0)  AS total_absent,
  COALESCE(SUM(status='Late'),0)    AS total_late,
  COALESCE(COUNT(DISTINCT date),0)  AS days_tracked
FROM attendance
WHERE date BETWEEN ? AND ?
";
$totStmt = $conn->prepare($totSql);
$totStmt->bind_param("ss", $from, $to);
$totStmt->execute();
$totRow = $totStmt->get_result()->fetch_assoc();

// If CSV export requested, send CSV and exit
if ($export_csv) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=attendance_report_' . $from . '_to_' . $to . '.csv');
    $out = fopen('php://output', 'w');
    // header row
    fputcsv($out, ['ID','Roll No','Name','Class','Section','Present','Absent','Late','Total Records']);
    if ($result->num_rows > 0) {
        while ($r = $result->fetch_assoc()) {
            fputcsv($out, [
                $r['id'],
                $r['roll_no'],
                $r['name'],
                $r['class'],
                $r['section'],
                $r['present_count'],
                $r['absent_count'],
                $r['late_count'],
                $r['total_records']
            ]);
        }
    }
    // totals row
    fputcsv($out, []);
    fputcsv($out, ['Totals','','','', "Days tracked: {$totRow['days_tracked']}", "Present: {$totRow['total_present']}", "Absent: {$totRow['total_absent']}", "Late: {$totRow['total_late']}"]);
    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Attendance Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{
      --card-bg: #fff;
      --accent-1: linear-gradient(135deg,#667eea,#764ba2);
      --accent-2: linear-gradient(135deg,#36d1dc,#5b86e5);
      --accent-3: linear-gradient(135deg,#ff9966,#ff5e62);
      --muted: #666;
    }
    *{box-sizing:border-box}
    body{
      margin:0;
      font-family: "Segoe UI", Roboto, Arial, sans-serif;
      background: linear-gradient(135deg,#f6d365 0%, #fda085 100%);
      padding: 28px;
      color:#222;
    }
    .wrap{
      max-width:1100px;
      margin:0 auto;
    }
    .card{
      background:var(--card-bg);
      border-radius:12px;
      padding:18px;
      box-shadow:0 8px 30px rgba(0,0,0,0.12);
      margin-bottom:18px;
    }
    h1{
      margin:0 0 6px 0;
      font-size:22px;
    }
    .sub{ color:var(--muted); margin-bottom:12px; }
    .controls{
      display:flex;
      gap:12px;
      flex-wrap:wrap;
      align-items:center;
      margin-bottom:14px;
    }
    input[type="date"]{
      padding:10px 12px;
      border-radius:8px;
      border:1px solid #ccc;
      font-size:14px;
    }
    .btn{
      display:inline-block;
      padding:10px 14px;
      border-radius:8px;
      color:#fff;
      text-decoration:none;
      font-weight:600;
      cursor:pointer;
      border:none;
    }
    .btn-primary{ background:var(--accent-1); }
    .btn-accent{ background:var(--accent-2); }
    .btn-danger{ background:var(--accent-3); }
    .small{ font-size:13px; padding:8px 10px; border-radius:8px; background:#f4f4f4; color:#333; border:1px solid #e6e6e6; }
    .summary{
      display:flex;
      gap:12px;
      flex-wrap:wrap;
      margin-top:8px;
    }
    .stat{
      min-width:160px;
      background:linear-gradient(180deg, rgba(255,255,255,0.9), rgba(255,255,255,0.85));
      border-radius:10px;
      padding:12px;
      text-align:center;
      box-shadow:0 6px 18px rgba(0,0,0,0.06);
    }
    .stat h3{ margin:0; font-size:18px; color:#333; }
    .stat p{ margin:6px 0 0 0; color:var(--muted); }
    /* table */
    .table-wrap{ overflow:auto; margin-top:12px; border-radius:8px; }
    table{
      width:100%;
      border-collapse:collapse;
      min-width:800px;
    }
    th,td{
      padding:12px 14px;
      text-align:center;
      border-bottom:1px solid #eee;
      font-size:14px;
    }
    th{
      background:linear-gradient(90deg,#2b5876,#4e4376);
      color:#fff;
      position:sticky;
      top:0;
    }
    tr:nth-child(even){ background:#fbfbfb; }
    tr:hover{ background:#f1f7ff; }
    .label{ display:inline-block; padding:6px 10px; border-radius:999px; color:#fff; font-weight:700; font-size:13px; }
    .lbl-present{ background:linear-gradient(90deg,#11998e,#38ef7d); }
    .lbl-absent{ background:linear-gradient(90deg,#ff416c,#ff4b2b); }
    .lbl-late{ background:linear-gradient(90deg,#fbc02d,#f57c00); color:#000; }
    .actions { display:flex; gap:8px; justify-content:center; }
    .back { margin-top:12px; display:inline-block; color:#333; text-decoration:none; font-weight:600; }
    @media(max-width:720px){
      th,td{ padding:10px 8px; font-size:13px; }
      .stat{ min-width:120px; }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>📊 Attendance Report</h1>
      <div class="sub">Report for: <strong><?= htmlspecialchars($from) ?></strong> to <strong><?= htmlspecialchars($to) ?></strong></div>

      <form method="get" style="margin-bottom:6px;">
        <div class="controls">
          <label class="small">From: <input type="date" name="from" value="<?= htmlspecialchars($from) ?>"></label>
          <label class="small">To: <input type="date" name="to" value="<?= htmlspecialchars($to) ?>"></label>
          <button type="submit" class="btn btn-primary">🔎 Filter</button>
          <a href="?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>&export=csv" class="btn btn-accent">⬇ Export CSV</a>
          <a href="dashboard.php" class="back">⬅ Back to Dashboard</a>
        </div>
      </form>

      <div class="summary">
        <div class="stat">
          <h3><?= number_format((int)$totRow['days_tracked']) ?></h3>
          <p>Days Tracked</p>
        </div>
        <div class="stat">
          <h3><?= number_format((int)$totRow['total_present']) ?></h3>
          <p>Total Present</p>
        </div>
        <div class="stat">
          <h3><?= number_format((int)$totRow['total_absent']) ?></h3>
          <p>Total Absent</p>
        </div>
        <div class="stat">
          <h3><?= number_format((int)$totRow['total_late']) ?></h3>
          <p>Total Late</p>
        </div>
      </div>

    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Roll No</th>
              <th>Name</th>
              <th>Class</th>
              <th>Section</th>
              <th>Present</th>
              <th>Absent</th>
              <th>Late</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && $result->num_rows > 0): $i = 0; ?>
              <?php while ($row = $result->fetch_assoc()): $i++; ?>
                <tr>
                  <td><?= $i ?></td>
                  <td><?= htmlspecialchars($row['roll_no']) ?></td>
                  <td style="text-align:left;padding-left:14px;"><?= htmlspecialchars($row['name']) ?></td>
                  <td><?= htmlspecialchars($row['class']) ?></td>
                  <td><?= htmlspecialchars($row['section']) ?></td>
                  <td><span class="label lbl-present"><?= (int)$row['present_count'] ?></span></td>
                  <td><span class="label lbl-absent"><?= (int)$row['absent_count'] ?></span></td>
                  <td><span class="label lbl-late"><?= (int)$row['late_count'] ?></span></td>
                  <td><?= (int)$row['total_records'] ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="9">No attendance records found for this period.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</body>
</html>
