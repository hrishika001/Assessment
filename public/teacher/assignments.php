<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_any_role(['teacher','admin'],'../');

$title='Assignments | Teacher';
$desc='Create assignments and review submissions';
$base_url='../';

$my_role = $_SESSION['user']['role'];
$my_id = (int)$_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD']==='POST') {
  verify_csrf();
  $action = $_POST['action'] ?? '';

  if ($action === 'create' && in_array($my_role, ['teacher','admin'], true)) {
    $title_in = trim($_POST['title'] ?? '');
    $module = trim($_POST['module'] ?? '');
    $due = trim($_POST['due_date'] ?? '');
    $details = trim($_POST['details'] ?? '');

    if ($title_in==='' || $module==='' || $due==='') {
      set_flash('danger','Title, module and due date are required.');
    } else {
      $file_path = save_assignment_file($_FILES['file'] ?? null, null);
      $stmt = $conn->prepare("INSERT INTO assignments (created_by,title,module,due_date,details,file_path) VALUES (?,?,?,?,?,?)");
      $stmt->bind_param("isssss", $my_id, $title_in, $module, $due, $details, $file_path);
      $stmt->execute();
      set_flash('success','Assignment created.');
      header('Location: assignments.php'); exit;
    }
  }

  if ($action === 'delete') {
    $aid = (int)($_POST['id'] ?? 0);
    if ($aid>0) {
      $stmt=$conn->prepare("SELECT file_path FROM assignments WHERE id=? AND created_by=?");
      $stmt->bind_param("ii",$aid,$my_id);
      $stmt->execute();
      $row=$stmt->get_result()->fetch_assoc();
      if ($row && !empty($row['file_path'])) safe_unlink($row['file_path']);

      $stmt=$conn->prepare("DELETE FROM assignments WHERE id=? AND created_by=?");
      $stmt->bind_param("ii",$aid,$my_id);
      $stmt->execute();
      set_flash('success','Assignment deleted.');
      header('Location: assignments.php'); exit;
    }
  }
}

// fetch assignments
if ($my_role === 'admin') {
  $rows = $conn->query("SELECT a.*, u.name teacher_name
                        FROM assignments a JOIN users u ON u.id=a.created_by
                        ORDER BY a.id DESC")->fetch_all(MYSQLI_ASSOC);
} else {
  $stmt=$conn->prepare("SELECT a.*, u.name teacher_name
                        FROM assignments a JOIN users u ON u.id=a.created_by
                        WHERE a.created_by=? ORDER BY a.id DESC");
  $stmt->bind_param("i",$my_id);
  $stmt->execute();
  $rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Assignments</h1>
  <p>Create assignments and open each assignment to view student submissions.</p>
</section>

<?php if ($my_role === 'teacher'): ?>
<section class="card pad">
  <h2>Create Assignment</h2>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
    <input type="hidden" name="action" value="create">
    <div class="grid">
      <div style="grid-column:span 6" class="form-row">
        <label>Title</label>
        <input name="title" required placeholder="e.g., Network Security Report">
      </div>
      <div style="grid-column:span 6" class="form-row">
        <label>Module / Subject</label>
        <input name="module" required placeholder="e.g., Cybersecurity Fundamentals">
      </div>
      <div style="grid-column:span 4" class="form-row">
        <label>Due date</label>
        <input name="due_date" type="date" required>
      </div>
      <div style="grid-column:span 8" class="form-row">
        <label>File (optional)</label>
        <input name="file" type="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
      </div>
      <div style="grid-column:span 12" class="form-row">
        <label>Description</label>
        <textarea name="details" placeholder="Short instructions..."></textarea>
      </div>
    </div>
    <button class="btn btn-primary" type="submit">Create</button>
  </form>
</section>
<?php endif; ?>

<section class="card pad" style="margin-top:1rem">
  <h2>Assignment List</h2>
  <div style="overflow:auto">
    <table class="table">
      <thead>
        <tr><th>ID</th><th>Title</th><th>Module</th><th>Due</th><th>Teacher</th><th>File</th><th>Submissions</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?php echo e($r['id']); ?></td>
            <td><?php echo e($r['title']); ?></td>
            <td><?php echo e($r['module']); ?></td>
            <td><?php echo e($r['due_date']); ?></td>
            <td><?php echo e($r['teacher_name']); ?></td>
            <td>
              <?php if(!empty($r['file_path'])): ?>
                <a class="btn btn-light" href="../<?php echo e($r['file_path']); ?>" target="_blank" rel="noopener">Open</a>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td>
              <a class="btn btn-light" href="submissions.php?assignment_id=<?php echo e($r['id']); ?>">View</a>
            </td>
            <td>
              <?php if ($my_role === 'teacher' && (int)$r['created_by'] === $my_id): ?>
                <form method="post" style="display:inline">
                  <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?php echo e($r['id']); ?>">
                  <button class="btn btn-danger" data-confirm="Delete this assignment?" type="submit">Delete</button>
                </form>
              <?php else: ?>—<?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?><tr><td colspan="8">No assignments yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
