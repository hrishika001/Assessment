<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('teacher','../');
$title='Attendance | Teacher';
$desc='Mark and view attendance';
$base_url='../';
$back_url='dashboard.php';

$me_id=(int)$_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD']==='POST') {
  verify_csrf();
  $student_id=(int)($_POST['student_user_id'] ?? 0);
  $module=trim($_POST['module'] ?? '');
  $att_date=$_POST['att_date'] ?? date('Y-m-d');
  $status=$_POST['status'] ?? 'Present';

  if ($student_id<=0 || $module==='') set_flash('danger','Student and module are required.');
  elseif (!in_array($status,['Present','Absent'],true)) set_flash('danger','Invalid status.');
  else {
    $stmt=$conn->prepare("INSERT INTO attendance (student_user_id, marked_by, module, att_date, status)
                          VALUES (?,?,?,?,?)
                          ON DUPLICATE KEY UPDATE status=VALUES(status), marked_by=VALUES(marked_by)");
    $stmt->bind_param('iisss',$student_id,$me_id,$module,$att_date,$status);
    $stmt->execute();
    set_flash('success','Attendance saved.');
    header('Location: attendance.php'); exit;
  }
}

$students = $conn->query("SELECT id,name,email FROM users WHERE role='student' ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$stmt=$conn->prepare("SELECT a.*, u.name student_name, u.email
                      FROM attendance a
                      JOIN users u ON u.id=a.student_user_id
                      WHERE a.marked_by=?
                      ORDER BY a.att_date DESC, a.id DESC LIMIT 200");
$stmt->bind_param('i',$me_id);
$stmt->execute();
$rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Attendance</h1>
  <p>Mark daily attendance by module. Duplicate entries for the same student/module/date will update instead of creating duplicates.</p>
</section>

<section class="card pad">
  <h2>Mark Attendance</h2>
  <form method="post" class="grid">
    <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
    <div style="grid-column:span 6" class="form-row">
      <label>Student</label>
      <select name="student_user_id" required>
        <option value="">Select...</option>
        <?php foreach($students as $s): ?>
          <option value="<?php echo e($s['id']); ?>"><?php echo e($s['name']); ?> (<?php echo e($s['email']); ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="grid-column:span 3" class="form-row">
      <label>Date</label>
      <input type="date" name="att_date" value="<?php echo e(date('Y-m-d')); ?>" required>
    </div>
    <div style="grid-column:span 3" class="form-row">
      <label>Status</label>
      <select name="status" required>
        <option value="Present">Present</option>
        <option value="Absent">Absent</option>
      </select>
    </div>
    <div style="grid-column:span 12" class="form-row">
      <label>Module</label>
      <input name="module" placeholder="e.g., Web Development" required>
    </div>
    <div style="grid-column:span 12">
      <button class="btn btn-primary" type="submit">Save Attendance</button>
    </div>
  </form>
</section>

<section class="card pad" style="margin-top:1rem">
  <h2>My Recent Records</h2>
  <div style="overflow:auto">
    <table class="table">
      <thead><tr><th>Date</th><th>Student</th><th>Module</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?php echo e($r['att_date']); ?></td>
            <td><?php echo e($r['student_name']); ?><div class="small"><?php echo e($r['email']); ?></div></td>
            <td><?php echo e($r['module']); ?></td>
            <td><span class="chip" style="background:<?php echo $r['status']==='Present'?'rgba(0,163,108,.18)':'rgba(225,29,72,.15)'; ?>"><?php echo e($r['status']); ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?><tr><td colspan="4">No attendance records yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
