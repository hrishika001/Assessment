<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';$title='Student Management Portal';
$desc='Login portal for Admin, Teacher and Student.';
$base_url = './';

include __DIR__ . '/../includes/header.php';
?>
<div class="landing">
  <div class="landing-bg" aria-hidden="true"></div>

  <div class="landing-wrap">
    <div class="brand">
      <div class="brand-mark" aria-hidden="true">SMP</div>
      <div>
        <div class="brand-title">Student Management Portal</div>
        <div class="brand-sub">University Dashboard • Attendance • Assignments • Grades • Announcements</div>
      </div>
    </div>

    <div class="landing-card">
      <h1>Welcome back</h1>
      <p class="muted">Choose your access type to continue.</p>

      <div class="role-grid">
        <a class="role-tile" href="admin/login.php">
          <div class="role-ico">🛡️</div>
          <div class="role-name">Admin</div>
          <div class="role-desc">Manage teachers, students, announcements, reports.</div>
        </a>

        <a class="role-tile" href="teacher/login.php">
          <div class="role-ico">🧑‍🏫</div>
          <div class="role-name">Teacher</div>
          <div class="role-desc">Mark attendance, create assignments & announcements.</div>
        </a>

        <a class="role-tile" href="student/login.php">
          <div class="role-ico">🎓</div>
          <div class="role-name">Student</div>
          <div class="role-desc">View attendance, assignments, results & updates.</div>
        </a>
      </div>

      <div class="landing-footer">
        <div class="tiny">Tip: Use your registered email and password. If you forget credentials, contact the admin.</div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
