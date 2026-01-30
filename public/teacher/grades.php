<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('teacher','../');
$title='Grades | Teacher';
$desc='Publish grades/results for students';
$base_url='../';
$back_url='dashboard.php';

$me_id=(int)$_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD']==='POST') {
  verify_csrf();
  $student_id=(int)($_POST['student_user_id'] ?? 0);
  $module=trim($_POST['module'] ?? '');
  $marks=(int)($_POST['marks'] ?? 0);
  $remarks=trim($_POST['remarks'] ?? '');

  if ($student_id<=0 || $module==='') set_flash('danger','Student and module are required.');
  elseif ($marks<0 || $marks>100) set_flash('danger','Marks must be 0-100.');
  else {
    $stmt=$conn->prepare("INSERT INTO grades (student_user_id, teacher_user_id, module, marks, remarks)
                          VALUES (?,?,?,?,?)");
    $stmt->bind_param('iisds',$student_id,$me_id,$module,$marks,$remarks);
    // bind_param doesn't accept 'd' for int; we'll use i for marks
    $stmt=$conn->prepare("INSERT INTO grades (student_user_id, teacher_user_id, module, marks, remarks) VALUES (?,?,?,?,?)");
    $stmt->bind_param('iisis',$student_id,$me_id,$module,$marks,$remarks);
    $stmt->execute();
    set_flash('success','Grade published.');
    header('Location: grades.php'); exit;
  }
}

$students = $conn->query("SELECT id,name,email FROM users WHERE role='student' ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$stmt=$conn->prepare("SELECT g.*, su.name student_name, su.email
                      FROM grades g
                      JOIN users su ON su.id=g.student_user_id
                      WHERE g.teacher_user_id=?
                      ORDER BY g.id DESC LIMIT 200");
$stmt->bind_param('i',$me_id);
$stmt->execute();
$rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Grades / Results</h1>
  <p>Publish grades. Students can view them in their Results page.</p>
</section>

<section class="card pad">
  <h2>Publish Grade</h2>
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
    <div style="grid-column:span 6" class="form-row">
      <label>Module</label>
      <input name="module" required placeholder="e.g., Database Systems">
    </div>
    <div style="grid-column:span 3" class="form-row">
      <label>Marks (0-100)</label>
      <input type="number" min="0" max="100" name="marks" required>
    </div>
    <div style="grid-column:span 9" class="form-row">
      <label>Remarks</label>
      <input name="remarks" placeholder="Optional">
    </div>
    <div style="grid-column:span 12">
      <button class="btn btn-primary" type="submit">Publish</button>
    </div>
  </form>
</section>

<section class="card pad" style="margin-top:1rem">
  <h2>My Published Grades</h2>
  <div style="overflow:auto">
    <table class="table">
      <thead><tr><th>Student</th><th>Module</th><th>Marks</th><th>Remarks</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?php echo e($r['student_name']); ?><div class="small"><?php echo e($r['email']); ?></div></td>
            <td><?php echo e($r['module']); ?></td>
            <td><strong><?php echo e($r['marks']); ?></strong></td>
            <td><?php echo e($r['remarks']); ?></td>
            <td><?php echo e($r['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?><tr><td colspan="5">No grades yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
