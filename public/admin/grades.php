<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('admin','../');
$title='Grades Overview | Admin';
$desc='Admin overview of grades/results';
$base_url='../';
$back_url='dashboard.php';

$rows=$conn->query("SELECT g.*, su.name student_name, tu.name teacher_name
                    FROM grades g
                    JOIN users su ON su.id=g.student_user_id
                    JOIN users tu ON tu.id=g.teacher_user_id
                    ORDER BY g.id DESC LIMIT 500")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Grades Overview</h1>
  <p>All published grades from teachers.</p>
</section>

<section class="card pad">
  <div style="overflow:auto">
    <table class="table">
      <thead><tr><th>Student</th><th>Module</th><th>Marks</th><th>Remarks</th><th>Teacher</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?php echo e($r['student_name']); ?></td>
            <td><?php echo e($r['module']); ?></td>
            <td><strong><?php echo e($r['marks']); ?></strong></td>
            <td><?php echo e($r['remarks']); ?></td>
            <td><?php echo e($r['teacher_name']); ?></td>
            <td><?php echo e($r['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?><tr><td colspan="6">No grades yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
