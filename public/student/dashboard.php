<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('student','../');

$title='Student Dashboard | Student Management Portal';
$desc='Student dashboard with ID card, assignments and attendance';
$base_url='../';

$me = get_student_full((int)$_SESSION['user']['id']);

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Student Dashboard</h1>
  <p>Welcome, <?php echo e($_SESSION['user']['name']); ?>. Your profile is read-only.</p>
</section>

<section class="card pad" aria-label="Student ID Card">
  <h2>Student ID Card</h2>
  <div class="grid" style="align-items:center">
    <div style="grid-column:span 4">
      <?php if(!empty($me['photo'])): ?>
        <img src="../<?php echo e($me['photo']); ?>" alt="Student photo" style="width:100%;max-width:220px;aspect-ratio:1/1;border-radius:22px;object-fit:cover;border:1px solid rgba(11,18,32,.12)">
      <?php else: ?>
        <div class="card-soft pad" style="width:220px;aspect-ratio:1/1;display:grid;place-items:center">
          <div class="muted">No photo</div>
        </div>
      <?php endif; ?>
    </div>
    <div style="grid-column:span 8">
      <div class="card-soft pad">
        <div class="grid">
          <div style="grid-column:span 6"><div class="muted">Full Name</div><div style="font-weight:900;font-size:1.1rem"><?php echo e($me['name'] ?? ''); ?></div></div>
          <div style="grid-column:span 6"><div class="muted">Student ID</div><div style="font-weight:950;font-size:1.1rem">S-<?php echo e($me['id'] ?? ''); ?></div></div>
          <div style="grid-column:span 6"><div class="muted">Course</div><div style="font-weight:900"><?php echo e($me['course'] ?? ''); ?></div></div>
          <div style="grid-column:span 6"><div class="muted">Specialization</div><div style="font-weight:900"><?php echo e($me['specialization'] ?? ''); ?></div></div>
          <div style="grid-column:span 12"><div class="muted">Email</div><div style="font-weight:900"><?php echo e($me['email'] ?? ''); ?></div></div>
        </div>
        <div class="small" style="margin-top:.7rem">If anything is incorrect, contact the admin/teacher. Students cannot edit profiles.</div>
      </div>
    </div>
  </div>
</section>

<section class="grid" style="margin-top:1rem" aria-label="Student links">
  <a class="card option-card" href="assignments.php">
    <div class="option-icon">📚</div>
    <div class="option-title">Assignments</div>
    <p class="option-desc">View assignments, submit files, and track submission status.</p>
  </a>
  <a class="card option-card" href="announcements.php">
    <div class="option-icon">📢</div>
    <div class="option-title">Announcements</div>
    <p class="option-desc">Read latest notices from the university.</p>
  </a>
  <a class="card option-card" href="attendance.php">
    <div class="option-icon">🗓️</div>
    <div class="option-title">Attendance</div>
    <p class="option-desc">View your attendance module-wise with totals.</p>
  </a>
  <a class="card option-card" href="results.php">
    <div class="option-icon">✅</div>
    <div class="option-title">Results</div>
    <p class="option-desc">View grades published by your teachers.</p>
  </a>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
