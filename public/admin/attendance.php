<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('admin','../');

$title='Attendance | Admin';
$desc='View attendance by student or as an overall log';
$base_url='../';
$back_url='dashboard.php';

$student_id = (int)($_GET['student_id'] ?? 0);

// Students for filter
$students = $conn->query("SELECT id, name, email FROM users WHERE role='student' ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// Attendance rows (filtered)
$sql = "SELECT a.*, su.name student_name, su.email student_email, tu.name teacher_name
        FROM attendance a
        JOIN users su ON su.id=a.student_user_id
        JOIN users tu ON tu.id=a.marked_by";
$params = [];
$types = "";
if ($student_id > 0) {
  $sql .= " WHERE a.student_user_id = ?";
  $types .= "i";
  $params[] = $student_id;
}
$sql .= " ORDER BY a.att_date DESC, a.id DESC LIMIT 800";

if ($params) {
  $stmt = $conn->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
  $rows = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

// Summary for selected student
$summary = null;
if ($student_id > 0) {
  $stmt = $conn->prepare("SELECT
      SUM(status='Present') AS presents,
      SUM(status='Absent')  AS absents,
      COUNT(*) AS total
    FROM attendance
    WHERE student_user_id=?");
  $stmt->bind_param('i',$student_id);
  $stmt->execute();
  $summary = $stmt->get_result()->fetch_assoc();
}

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Attendance</h1>
  <p><?php echo $student_id>0 ? 'Attendance report for the selected student.' : 'All attendance records marked by teachers.'; ?></p>
</section>

<section class="card pad">
  <form class="filters" method="get" action="">
    <div class="field">
      <label for="student_id">Filter by student</label>
      <select id="student_id" name="student_id">
        <option value="0">All students</option>
        <?php foreach($students as $s): ?>
          <option value="<?php echo (int)$s['id']; ?>" <?php echo $student_id===(int)$s['id']?'selected':''; ?>>
            <?php echo e($s['name']); ?> (<?php echo e($s['email']); ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field" style="align-self:end">
      <button class="btn">Apply</button>
      <?php if($student_id>0): ?>
        <a class="btn ghost" href="attendance.php">Clear</a>
      <?php endif; ?>
    </div>
  </form>

  <?php if($student_id>0 && $summary): ?>
    <div class="stats" style="margin-top:14px">
      <div class="stat">
        <div class="stat-k">Total</div>
        <div class="stat-v"><?php echo (int)$summary['total']; ?></div>
      </div>
      <div class="stat">
        <div class="stat-k">Present</div>
        <div class="stat-v"><?php echo (int)$summary['presents']; ?></div>
      </div>
      <div class="stat">
        <div class="stat-k">Absent</div>
        <div class="stat-v"><?php echo (int)$summary['absents']; ?></div>
      </div>
    </div>
  <?php endif; ?>

  <div style="overflow:auto; margin-top:16px">
    <table class="table">
      <thead><tr><th>Date</th><th>Student</th><th>Module</th><th>Status</th><th>Marked By</th></tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?php echo e($r['att_date']); ?></td>
            <td><?php echo e($r['student_name']); ?><div class="small"><?php echo e($r['student_email']); ?></div></td>
            <td><?php echo e($r['module']); ?></td>
            <td><span class="chip" style="background:<?php echo $r['status']==='Present'?'rgba(0,163,108,.18)':'rgba(225,29,72,.15)'; ?>"><?php echo e($r['status']); ?></span></td>
            <td><?php echo e($r['teacher_name']); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?><tr><td colspan="5">No attendance records found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
