<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('student','../');
$title='My Attendance | Student';
$desc='View attendance with totals and percentage';
$base_url='../';

$me_id=(int)$_SESSION['user']['id'];

$stmt=$conn->prepare("SELECT module,
                      SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) present_count,
                      SUM(CASE WHEN status='Absent' THEN 1 ELSE 0 END) absent_count,
                      COUNT(*) total
                      FROM attendance
                      WHERE student_user_id=?
                      GROUP BY module
                      ORDER BY module");
$stmt->bind_param("i",$me_id);
$stmt->execute();
$summary=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt=$conn->prepare("SELECT att_date,module,status,marked_by
                      FROM attendance
                      WHERE student_user_id=?
                      ORDER BY att_date DESC, id DESC
                      LIMIT 120");
$stmt->bind_param("i",$me_id);
$stmt->execute();
$rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>My Attendance</h1>
  <p>Module-wise summary and recent records.</p>
</section>

<section class="card pad">
  <h2>Summary</h2>
  <div style="overflow:auto">
    <table class="table">
      <thead><tr><th>Module</th><th>Present</th><th>Absent</th><th>Total</th><th>Percentage</th></tr></thead>
      <tbody>
        <?php foreach($summary as $s):
          $pct = ($s['total'] > 0) ? round(((int)$s['present_count']/(int)$s['total'])*100, 1) : 0;
        ?>
          <tr>
            <td><?php echo e($s['module']); ?></td>
            <td><?php echo e($s['present_count']); ?></td>
            <td><?php echo e($s['absent_count']); ?></td>
            <td><?php echo e($s['total']); ?></td>
            <td><strong><?php echo e($pct); ?>%</strong></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$summary): ?><tr><td colspan="5">No attendance records yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<section class="card pad" style="margin-top:1rem">
  <h2>Recent Records</h2>
  <div style="overflow:auto">
    <table class="table">
      <thead><tr><th>Date</th><th>Module</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?php echo e($r['att_date']); ?></td>
            <td><?php echo e($r['module']); ?></td>
            <td><?php echo e($r['status']); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?><tr><td colspan="3">No records.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
