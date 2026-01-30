<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('teacher','../');
$title='Teacher Dashboard | Student Management Portal';
$desc='Teacher dashboard';
$base_url='../';

$my_id = (int)$_SESSION['user']['id'];
$counts = ['assignments'=>0,'submissions'=>0,'attendance'=>0,'results'=>0];

$stmt=$conn->prepare("SELECT COUNT(*) c FROM assignments WHERE created_by=?");
$stmt->bind_param("i",$my_id); $stmt->execute();
$counts['assignments']=(int)$stmt->get_result()->fetch_assoc()['c'];

$stmt=$conn->prepare("SELECT COUNT(*) c
                      FROM submissions s
                      JOIN assignments a ON a.id=s.assignment_id
                      WHERE a.created_by=?");
$stmt->bind_param("i",$my_id); $stmt->execute();
$counts['submissions']=(int)$stmt->get_result()->fetch_assoc()['c'];

$stmt=$conn->prepare("SELECT COUNT(*) c FROM attendance WHERE marked_by=?");
$stmt->bind_param("i",$my_id); $stmt->execute();
$counts['attendance']=(int)$stmt->get_result()->fetch_assoc()['c'];

$stmt=$conn->prepare("SELECT COUNT(*) c FROM grades WHERE teacher_user_id=?");
$stmt->bind_param("i",$my_id); $stmt->execute();
$counts['results']=(int)$stmt->get_result()->fetch_assoc()['c'];

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Teacher Dashboard</h1>
  <p>Create assignments, view submissions, mark attendance, and publish results.</p>
</section>

<section class="grid">
  <div class="card kpi">
    <div class="kpi-title">Assignments</div>
    <div class="kpi-value"><?php echo e($counts['assignments']); ?></div>
    <div class="kpi-sub">Created by you</div>
  </div>
  <div class="card kpi">
    <div class="kpi-title">Submissions</div>
    <div class="kpi-value"><?php echo e($counts['submissions']); ?></div>
    <div class="kpi-sub">Received so far</div>
  </div>
  <div class="card kpi">
    <div class="kpi-title">Attendance</div>
    <div class="kpi-value"><?php echo e($counts['attendance']); ?></div>
    <div class="kpi-sub">Records marked</div>
  </div>
  <div class="card kpi">
    <div class="kpi-title">Results</div>
    <div class="kpi-value"><?php echo e($counts['results']); ?></div>
    <div class="kpi-sub">Grades published</div>
  </div>
</section>

<section class="grid" style="margin-top:1rem">
  <a class="card option-card" href="students.php">
    <div class="option-icon">➕</div>
    <div class="option-title">Add Student Profile</div>
    <p class="option-desc">Link a student login to a profile (course, specialization, photo).</p>
  </a>
  <a class="card option-card" href="assignments.php">
    <div class="option-icon">📚</div>
    <div class="option-title">Assignments</div>
    <p class="option-desc">Create assignments and check submissions.</p>
  </a>
  <a class="card option-card" href="announcements.php">
    <div class="option-icon">📢</div>
    <div class="option-title">Announcements</div>
    <p class="option-desc">Read latest notices.</p>
  </a>
  <a class="card option-card" href="attendance.php">
    <div class="option-icon">🗓️</div>
    <div class="option-title">Attendance</div>
    <p class="option-desc">Mark attendance for students.</p>
  </a>
</section>

<section class="card pad" style="margin-top:1rem">
  <a class="btn btn-light" href="grades.php">Manage Grades</a>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
