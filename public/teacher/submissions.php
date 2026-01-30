<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_any_role(['teacher','admin'],'../');
$title='Assignment Submissions | Teacher';
$desc='View and download assignment submissions';
$base_url='../';

$assignment_id = (int)($_GET['assignment_id'] ?? 0);
if ($assignment_id <= 0) { http_response_code(400); die('Missing assignment id'); }

$my_role = $_SESSION['user']['role'];
$my_id = (int)$_SESSION['user']['id'];

// fetch assignment with owner check
$stmt=$conn->prepare("SELECT a.*, u.name teacher_name FROM assignments a JOIN users u ON u.id=a.created_by WHERE a.id=? LIMIT 1");
$stmt->bind_param("i",$assignment_id);
$stmt->execute();
$ass=$stmt->get_result()->fetch_assoc();
if (!$ass) { http_response_code(404); die('Assignment not found'); }
if ($my_role==='teacher' && (int)$ass['created_by'] !== $my_id) { http_response_code(403); die('403 Forbidden'); }

// fetch submissions
$stmt=$conn->prepare("SELECT s.*, u.name student_name, u.email
                      FROM submissions s
                      JOIN users u ON u.id=s.student_user_id
                      WHERE s.assignment_id=?
                      ORDER BY s.submitted_at DESC");
$stmt->bind_param("i",$assignment_id);
$stmt->execute();
$subs=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Submissions</h1>
  <p><strong><?php echo e($ass['title']); ?></strong> • <?php echo e($ass['module']); ?> • Due <?php echo e($ass['due_date']); ?></p>
</section>

<section class="card pad">
  <a class="btn btn-light" href="assignments.php">← Back to assignments</a>
</section>

<section class="card pad" style="margin-top:1rem">
  <h2>Student Submissions</h2>
  <div style="overflow:auto">
    <table class="table">
      <thead><tr><th>Student</th><th>Email</th><th>Comment</th><th>File</th><th>Submitted At</th></tr></thead>
      <tbody>
        <?php foreach($subs as $s): ?>
          <tr>
            <td><?php echo e($s['student_name']); ?></td>
            <td><?php echo e($s['email']); ?></td>
            <td><?php echo e($s['comment'] ?? ''); ?></td>
            <td>
              <?php if(!empty($s['file_path'])): ?>
                <a class="btn btn-light" href="../<?php echo e($s['file_path']); ?>" target="_blank" rel="noopener">Download</a>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td><?php echo e($s['submitted_at']); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$subs): ?><tr><td colspan="5">No submissions yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
