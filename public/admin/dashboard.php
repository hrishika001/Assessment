<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('admin','../');
$title='Admin Dashboard | Student Management Portal';
$desc='Admin dashboard overview';
$base_url='../';

$counts = [
  'students' => (int)$conn->query("SELECT COUNT(*) c FROM users WHERE role='student'")->fetch_assoc()['c'],
  'teachers' => (int)$conn->query("SELECT COUNT(*) c FROM users WHERE role='teacher'")->fetch_assoc()['c'],
  'attendance' => (int)$conn->query("SELECT COUNT(*) c FROM attendance")->fetch_assoc()['c'],
  'assignments' => (int)$conn->query("SELECT COUNT(*) c FROM assignments")->fetch_assoc()['c'],
  'announcements' => (int)$conn->query("SELECT COUNT(*) c FROM announcements")->fetch_assoc()['c'],
  'grades' => (int)$conn->query("SELECT COUNT(*) c FROM grades")->fetch_assoc()['c'],
];

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Admin Dashboard</h1>
  <p>Manage students, teachers, announcements, attendance, assignments and grades.</p>
</section>


<section class="dash">
  <div class="dash-group">
    <h2 class="dash-h2">Overview</h2>
    <div class="overview4" aria-label="Overview statistics">
      <div class="card stat">
        <div class="stat-label">Total Users</div>
        <div class="stat-value"><?php echo e($counts['students'] + $counts['teachers'] + 1); ?></div>
        <div class="stat-sub">Students + Teachers + Admin</div>
      </div>
      <div class="card stat">
        <div class="stat-label">Students</div>
        <div class="stat-value"><?php echo e($counts['students']); ?></div>
        <div class="stat-sub">Active student accounts</div>
      </div>
      <div class="card stat">
        <div class="stat-label">Teachers</div>
        <div class="stat-value"><?php echo e($counts['teachers']); ?></div>
        <div class="stat-sub">Active teacher accounts</div>
      </div>
      <div class="card stat">
        <div class="stat-label">Announcements</div>
        <div class="stat-value"><?php echo e($counts['announcements']); ?></div>
        <div class="stat-sub">Notices posted</div>
      </div>
    </div>
  </div>

  <div class="dash-group">
    <h2 class="dash-h2">Accounts Management</h2>
    <div class="accounts2">
      <div class="card feature">
        <div class="feature-head">
          <div>
            <h3>Students</h3>
            <p>Create student login accounts and manage profiles (photo, course, specialization).</p>
          </div>
          <span class="chip"><?php echo e($counts['students']); ?></span>
        </div>
        <div class="feature-actions">
          <a class="btn" href="students.php">Manage Students</a>
          <a class="btn btn-ghost" href="students.php#add">Add New</a>
        </div>
      </div>

      <div class="card feature">
        <div class="feature-head">
          <div>
            <h3>Teachers</h3>
            <p>Create teacher logins, upload photo & ID card, and manage departments.</p>
          </div>
          <span class="chip"><?php echo e($counts['teachers']); ?></span>
        </div>
        <div class="feature-actions">
          <a class="btn" href="teachers.php">Manage Teachers</a>
          <a class="btn btn-ghost" href="teachers.php#add">Add New</a>
        </div>
      </div>
    </div>
  </div>

  <div class="dash-group">
    <h2 class="dash-h2">Academic & Operations</h2>
    <div class="moduleList" aria-label="Modules">
      <a class="moduleRow" href="attendance.php">
        <div class="moduleTitle">
          <span class="dot dot-green"></span>
          <div>
            <div class="moduleName">Attendance</div>
            <div class="moduleDesc">View and manage attendance records.</div>
          </div>
        </div>
        <div class="moduleMeta">
          <span class="badge"><?php echo e($counts['attendance']); ?> records</span>
          <span class="go">Open →</span>
        </div>
      </a>

      <a class="moduleRow" href="assignments.php">
        <div class="moduleTitle">
          <span class="dot dot-blue"></span>
          <div>
            <div class="moduleName">Assignments</div>
            <div class="moduleDesc">Track teacher assignments and student submissions.</div>
          </div>
        </div>
        <div class="moduleMeta">
          <span class="badge"><?php echo e($counts['assignments']); ?> created</span>
          <span class="go">Open →</span>
        </div>
      </a>

      <a class="moduleRow" href="grades.php">
        <div class="moduleTitle">
          <span class="dot dot-amber"></span>
          <div>
            <div class="moduleName">Grades</div>
            <div class="moduleDesc">Publish results and view grade entries.</div>
          </div>
        </div>
        <div class="moduleMeta">
          <span class="badge"><?php echo e($counts['grades']); ?> published</span>
          <span class="go">Open →</span>
        </div>
      </a>

      <a class="moduleRow" href="announcements.php">
        <div class="moduleTitle">
          <span class="dot dot-purple"></span>
          <div>
            <div class="moduleName">Announcements</div>
            <div class="moduleDesc">Post important notices for teachers and students.</div>
          </div>
        </div>
        <div class="moduleMeta">
          <span class="badge"><?php echo e($counts['announcements']); ?> total</span>
          <span class="go">Open →</span>
        </div>
      </a>
    </div>
  </div>
</section>
<section class="grid" style="margin-top:1rem" aria-label="Admin actions">
  <a class="card option-card" href="students.php">
    <div class="option-icon">🎓</div>
    <div class="option-title">Students</div>
    <p class="option-desc">Create student logins, upload photo, choose course and program.</p>
  </a>
  <a class="card option-card" href="teachers.php">
    <div class="option-icon">👩‍🏫</div>
    <div class="option-title">Teachers</div>
    <p class="option-desc">Create teacher accounts, upload teacher photo and ID card.</p>
  </a>
  <a class="card option-card" href="announcements.php">
    <div class="option-icon">📢</div>
    <div class="option-title">Announcements</div>
    <p class="option-desc">Post notices for students and teachers.</p>
  </a>
  <a class="card option-card" href="attendance.php">
    <div class="option-icon">🗓️</div>
    <div class="option-title">Attendance</div>
    <p class="option-desc">View all attendance records.</p>
  </a>
  <a class="card option-card" href="assignments.php">
    <div class="option-icon">📚</div>
    <div class="option-title">Assignments</div>
    <p class="option-desc">Overview of assignments and submissions.</p>
  </a>
  <a class="card option-card" href="grades.php">
    <div class="option-icon">🏆</div>
    <div class="option-title">Grades</div>
    <p class="option-desc">View all published grades.</p>
  </a>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
