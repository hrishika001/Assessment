<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('student','../');

$title='Assignments | Student';
$desc='View and submit assignments';
$base_url='../';

$me_id = (int)$_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD']==='POST') {
  verify_csrf();
  $assignment_id = (int)($_POST['assignment_id'] ?? 0);
  $comment = trim($_POST['comment'] ?? '');

  if ($assignment_id <= 0) {
    set_flash('danger','Invalid assignment.');
  } else {
    $file_path = save_submission_file($_FILES['file'] ?? null, null);
    if (!$file_path && $comment==='') {
      set_flash('danger','Please attach a file or write a short comment.');
    } else {
      $stmt=$conn->prepare("INSERT INTO submissions (assignment_id, student_user_id, comment, file_path)
                            VALUES (?,?,?,?)
                            ON DUPLICATE KEY UPDATE comment=VALUES(comment), file_path=COALESCE(VALUES(file_path), file_path),
                            submitted_at=CURRENT_TIMESTAMP");
      $stmt->bind_param("iiss", $assignment_id, $me_id, $comment, $file_path);
      $stmt->execute();
      set_flash('success','Submission saved.');
      header('Location: assignments.php'); exit;
    }
  }
}

// list assignments with submission status
$stmt=$conn->prepare("SELECT a.*, u.name teacher_name,
                      s.submitted_at, s.file_path sub_file, s.comment sub_comment
                      FROM assignments a
                      JOIN users u ON u.id=a.created_by
                      LEFT JOIN submissions s
                        ON s.assignment_id=a.id AND s.student_user_id=?
                      ORDER BY a.id DESC");
$stmt->bind_param("i",$me_id);
$stmt->execute();
$rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Assignments</h1>
  <p>Submit before due date. You can re-submit to update your submission.</p>
</section>

<section class="card pad">
  <div style="overflow:auto">
    <table class="table">
      <thead>
        <tr><th>Title</th><th>Module</th><th>Due</th><th>Teacher</th><th>File</th><th>Status</th><th>Submit</th></tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r): 
          $submitted = !empty($r['submitted_at']);
        ?>
          <tr>
            <td><?php echo e($r['title']); ?><div class="small"><?php echo e($r['details']); ?></div></td>
            <td><?php echo e($r['module']); ?></td>
            <td><?php echo e($r['due_date']); ?></td>
            <td><?php echo e($r['teacher_name']); ?></td>
            <td>
              <?php if(!empty($r['file_path'])): ?>
                <a class="btn btn-light" href="../<?php echo e($r['file_path']); ?>" target="_blank" rel="noopener">Open</a>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td>
              <?php if($submitted): ?>
                <span class="chip" style="background:rgba(0,163,108,.18)">Submitted</span>
                <div class="small">On: <?php echo e($r['submitted_at']); ?></div>
              <?php else: ?>
                <span class="chip" style="background:rgba(245,158,11,.20)">Not submitted</span>
              <?php endif; ?>
            </td>
            <td style="min-width:320px">
              <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
                <input type="hidden" name="assignment_id" value="<?php echo e($r['id']); ?>">
                <div class="form-row" style="margin:0">
                  <input type="file" name="file" accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png,.txt">
                </div>
                <div class="form-row" style="margin:0">
                  <input name="comment" placeholder="Comment (optional)" value="<?php echo e($r['sub_comment'] ?? ''); ?>">
                </div>
                <button class="btn btn-primary" type="submit"><?php echo $submitted ? 'Update Submission' : 'Submit'; ?></button>
                <?php if(!empty($r['sub_file'])): ?>
                  <a class="btn btn-light" href="../<?php echo e($r['sub_file']); ?>" target="_blank" rel="noopener">My File</a>
                <?php endif; ?>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?><tr><td colspan="7">No assignments available.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
