<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('student','../');
$title='Announcements | Student';
$desc='Read university announcements';
$base_url='../';
$back_url='dashboard.php';

$rows = $conn->query("SELECT a.*, u.name creator
                      FROM announcements a
                      LEFT JOIN users u ON u.id=a.created_by
                      WHERE a.audience IN ('all','students')
                      ORDER BY a.id DESC")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Announcements</h1>
  <p>Stay updated with notices from the university.</p>
</section>

<section class="card pad">
  <?php foreach($rows as $r): ?>
    <div class="announce">
      <div class="announce-title"><?php echo e($r['title']); ?></div>
      <div class="announce-meta">For: <?php echo e($r['audience']); ?> • By: <?php echo e($r['creator'] ?? 'System'); ?> • <?php echo e($r['created_at']); ?></div>
      <div class="announce-body"><?php echo nl2br(e($r['body'])); ?></div>
    </div>
  <?php endforeach; ?>
  <?php if(!$rows): ?>No announcements yet.<?php endif; ?>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
