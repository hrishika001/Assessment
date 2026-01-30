<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('student','../');
$title='My Results | Student';
$desc='View grades';
$base_url='../';

$me_id=(int)$_SESSION['user']['id'];

$stmt=$conn->prepare("SELECT g.*, tu.name teacher_name
                      FROM grades g
                      JOIN users tu ON tu.id=g.teacher_user_id
                      WHERE g.student_user_id=?
                      ORDER BY g.id DESC");
$stmt->bind_param("i",$me_id);
$stmt->execute();
$rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>My Results</h1>
  <p>Grades published by teachers.</p>
</section>

<section class="card pad">
  <div style="overflow:auto">
    <table class="table">
      <thead><tr><th>Module</th><th>Marks</th><th>Remarks</th><th>Teacher</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?php echo e($r['module']); ?></td>
            <td><strong><?php echo e($r['marks']); ?></strong></td>
            <td><?php echo e($r['remarks']); ?></td>
            <td><?php echo e($r['teacher_name']); ?></td>
            <td><?php echo e($r['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?><tr><td colspan="5">No results yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
