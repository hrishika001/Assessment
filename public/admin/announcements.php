<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('admin','../');
$title='Announcements | Admin';
$desc='Create announcements for students and teachers';
$base_url='../';
$back_url='dashboard.php';

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD']==='POST') {
  verify_csrf();
  $form_action = $_POST['action'] ?? '';
  if ($form_action === 'create') {
    $title_in = trim($_POST['title'] ?? '');
    $audience = trim($_POST['audience'] ?? 'all');
    $body = trim($_POST['body'] ?? '');
    $allowed = ['all','students','teachers'];
    if ($title_in==='' || $body==='') set_flash('danger','Title and message are required.');
    elseif (!in_array($audience,$allowed,true)) set_flash('danger','Invalid audience.');
    else {
      $uid = (int)($_SESSION['user']['id'] ?? 0);
      $stmt=$conn->prepare("INSERT INTO announcements (title,body,audience,created_by) VALUES (?,?,?,?)");
      $stmt->bind_param('sssi',$title_in,$body,$audience,$uid);
      $stmt->execute();
      set_flash('success','Announcement posted.');
      header('Location: announcements.php'); exit;
    }
  }

  if ($form_action === 'delete') {
    $del_id = (int)($_POST['id'] ?? 0);
    if ($del_id>0) {
      $stmt=$conn->prepare("DELETE FROM announcements WHERE id=?");
      $stmt->bind_param('i',$del_id);
      $stmt->execute();
      set_flash('success','Announcement deleted.');
      header('Location: announcements.php'); exit;
    }
  }
}

$rows = $conn->query("SELECT a.*, u.name creator
                      FROM announcements a
                      LEFT JOIN users u ON u.id=a.created_by
                      ORDER BY a.id DESC")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Announcements</h1>
  <p>Post notices to students and teachers. They appear on dashboards and in the announcements page.</p>
</section>

<section class="card pad">
  <h2>Post New Announcement</h2>
  <form method="post">
    <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
    <input type="hidden" name="action" value="create">

    <div class="grid">
      <div style="grid-column:span 8" class="form-row">
        <label>Title</label>
        <input name="title" required placeholder="e.g., Exam schedule updated">
      </div>
      <div style="grid-column:span 4" class="form-row">
        <label>Audience</label>
        <select name="audience" required>
          <option value="all">All</option>
          <option value="students">Students</option>
          <option value="teachers">Teachers</option>
        </select>
      </div>
      <div style="grid-column:span 12" class="form-row">
        <label>Message</label>
        <textarea name="body" rows="4" required placeholder="Write the announcement..."></textarea>
      </div>
    </div>

    <button class="btn btn-primary" type="submit">Post Announcement</button>
  </form>
</section>

<section class="card pad" style="margin-top:1rem">
  <h2>All Announcements</h2>
  <div style="overflow:auto">
    <table class="table">
      <thead><tr><th>Title</th><th>Audience</th><th>Posted By</th><th>Date</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td>
              <strong><?php echo e($r['title']); ?></strong>
              <div class="small"><?php echo nl2br(e($r['body'])); ?></div>
            </td>
            <td><?php echo e($r['audience']); ?></td>
            <td><?php echo e($r['creator'] ?? 'System'); ?></td>
            <td><?php echo e($r['created_at']); ?></td>
            <td style="white-space:nowrap">
              <form method="post" onsubmit="return confirm('Delete this announcement?')">
                <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo e($r['id']); ?>">
                <button class="btn btn-danger" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?><tr><td colspan="5">No announcements yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
