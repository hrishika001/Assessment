<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('admin','../');
$title='Assignments Overview | Admin';
$desc='Admin overview of assignments and submissions';
$base_url='../';
$back_url='dashboard.php';

$rows=$conn->query("SELECT a.*, u.name teacher_name,
                    (SELECT COUNT(*) FROM submissions s WHERE s.assignment_id=a.id) AS sub_count
                    FROM assignments a
                    JOIN users u ON u.id=a.created_by
                    ORDER BY a.id DESC")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Assignments Overview</h1>
  <p>View all assignments created by teachers and see total submissions.</p>
</section>

<section class="card pad">
  <div style="overflow:auto">
    <table class="table">
      <thead><tr><th>Title</th><th>Module</th><th>Due</th><th>Teacher</th><th>File</th><th>Submissions</th></tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><strong><?php echo e($r['title']); ?></strong><div class="small"><?php echo e($r['details']); ?></div></td>
            <td><?php echo e($r['module']); ?></td>
            <td><?php echo e($r['due_date']); ?></td>
            <td><?php echo e($r['teacher_name']); ?></td>
            <td><?php if(!empty($r['file_path'])): ?><a class="btn btn-light" href="../<?php echo e($r['file_path']); ?>" target="_blank" rel="noopener">Open</a><?php else: ?>—<?php endif; ?></td>
            <td>
              <span class="chip"><?php echo e($r['sub_count']); ?></span>
              <a class="btn btn-light" href="../teacher/submissions.php?assignment_id=<?php echo e($r['id']); ?>">View</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?><tr><td colspan="6">No assignments yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
