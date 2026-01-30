<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('teacher','../');

$title='Announcements | Teacher';
$desc='Read and create announcements';
$base_url='../';
$back_url='dashboard.php';

$uid = (int)($_SESSION['user']['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD']==='POST') {
  verify_csrf();
  $title_in = trim($_POST['title'] ?? '');
  $body = trim($_POST['body'] ?? '');
  $audience = 'students'; // teacher can only post to students
  
  if (empty($title_in) || empty($body)) {
    set_flash('error','Title and message are required.');
    redirect('announcements.php');
  } else {
    $stmt=$conn->prepare("INSERT INTO announcements (title,body,audience,created_by) VALUES (?,?,?,?)");
    $stmt->bind_param('sssi',$title_in,$body,$audience,$uid);
    $stmt->execute();
    set_flash('success','Announcement published.');
    redirect('announcements.php');
  }
}

// Teacher can read: teacher/all + also their own student announcements
$stmt = $conn->prepare("SELECT a.*, u.name creator
                        FROM announcements a
                        LEFT JOIN users u ON u.id=a.created_by
                        WHERE (a.audience IN ('all','teachers') OR a.created_by=?)
                        ORDER BY a.id DESC");
$stmt->bind_param('i',$uid);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Announcements</h1>
  <p>Create notices for students or everyone, and read official updates.</p>
</section>

<section class="card pad">
  <h2 style="margin:0 0 10px">Create announcement</h2>
  <form method="post" class="form">
    <?= csrf_field(); ?>
    <div class="grid2">
      <div class="field">
        <label>Title</label>
        <input name="title" required placeholder="e.g., Class rescheduled, Exam notice..." />
      </div>
      <div class="field">
  <label>Audience</label>
  <input value="Students" disabled />
</div>
    </div>
    <div class="field">
      <label>Message</label>
      <textarea name="body" rows="4" required placeholder="Write your announcement..."></textarea>
    </div>
    <button class="btn">Publish</button>
  </form>
</section>

<section class="card pad">
  <h2 style="margin:0 0 10px">Recent announcements</h2>
  <?php foreach($rows as $r): ?>
    <div class="announce">
      <div class="announce-title"><?php echo e($r['title']); ?></div>
      <div class="announce-meta">
        For: <?php echo e($r['audience']); ?> • By: <?php echo e($r['creator'] ?? 'Unknown'); ?> • <?php echo e($r['created_at']); ?>
        <?php if((int)$r['created_by']===$uid): ?><span class="chip" style="margin-left:8px;background:rgba(59,130,246,.15)">Yours</span><?php endif; ?>
      </div>
      <div class="announce-body"><?php echo nl2br(e($r['body'])); ?></div>
    </div>
  <?php endforeach; ?>
  <?php if(!$rows): ?><p class="muted">No announcements yet.</p><?php endif; ?>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
