<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('teacher','../');


$title='Add Student Profile | Teacher';
$desc='Teacher can complete student profile (login must exist).';
$base_url='../';

$COURSE_MAP = [
  'BIBM' => ['BCA','BIM'],
  'IT'   => ['Cybersecurity','AI'],
];

if ($_SERVER['REQUEST_METHOD']==='POST') {
  verify_csrf();
  $email = trim($_POST['student_email'] ?? '');
  $course = trim($_POST['course'] ?? '');
  $spec = trim($_POST['specialization'] ?? '');
  if ($email==='' || $course==='' || $spec==='') {
    set_flash('danger','Please choose a student and specialization.');
  } elseif (!isset($COURSE_MAP[$course]) || !in_array($spec, $COURSE_MAP[$course], true)) {
    set_flash('danger','Invalid specialization.');
  } else {
    $u = find_user_by_email($email);
    if (!$u || ($u['role'] ?? '') !== 'student') {
      set_flash('danger','Student login not found. Ask admin to create the student account first.');
    } else {
      $student = get_student_full((int)$u['id']);
      $photo = save_profile_photo($_FILES['photo'] ?? null, $student['photo'] ?? null);
      create_or_update_student_profile((int)$u['id'], $course, $spec, $photo);
      set_flash('success','Student profile saved.');
      header('Location: students.php'); exit;
    }
  }
}

// recent students
$rows = $conn->query("SELECT u.id,u.name,u.email,s.course,s.specialization,s.photo
                      FROM users u LEFT JOIN students s ON s.user_id=u.id
                      WHERE u.role='student' ORDER BY u.id DESC LIMIT 30")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Add / Update Student Profile</h1>
  <p><strong>Rule:</strong> student login accounts are created by admin. Teachers can only complete the student profile.</p>
</section>

<section class="card pad">
  <h2>Profile Form</h2>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
    <div class="grid">
      <div style="grid-column:span 6" class="form-row">
        <label>Student email (search)</label>
        <input name="student_email" id="student_email" placeholder="Type email..." list="student_list" required>
        <datalist id="student_list"></datalist>
        <div class="small">Tip: start typing, suggestions will appear (Ajax).</div>
      </div>

      <div style="grid-column:span 6" class="form-row">
        <label>Course</label>
        <select name="course" id="courseSelect" required>
          <option value="">Select...</option>
          <?php foreach(array_keys($COURSE_MAP) as $c): ?>
            <option value="<?php echo e($c); ?>"><?php echo e($c); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="grid-column:span 6" class="form-row">
        <label>Program / Specialization</label>
        <select name="specialization" id="specSelect" required>
          <option value="">Select...</option>
        </select>
          <option value="">Select...</option>
          
        </select>
      </div>

      <div style="grid-column:span 6" class="form-row">
        <label>Photo (optional)</label>
        <input type="file" name="photo" accept=".jpg,.jpeg,.png">
      </div>
    </div>
    <div style="display:flex; gap:.6rem; margin-top:1rem">
      <button class="btn btn-primary" type="submit">Save Profile</button>
      <a class="btn btn-light" href="dashboard.php">Back</a>
    </div>
  </form>
</section>

<section class="card pad" style="margin-top:1rem">
  <h2>Recently Added Students</h2>
  <div style="overflow:auto">
    <table class="table">
      <thead><tr><th>ID</th><th>Photo</th><th>Name</th><th>Email</th><th>Specialization</th></tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?php echo e($r['id']); ?></td>
            <td><?php if(!empty($r['photo'])): ?><img src="../<?php echo e($r['photo']); ?>" alt="Photo" style="width:38px;height:38px;border-radius:12px;object-fit:cover"><?php else: ?>—<?php endif; ?></td>
            <td><?php echo e($r['name']); ?></td>
            <td><?php echo e($r['email']); ?></td>
            <td><?php echo e($r['specialization'] ?? ''); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<script>
// Course -> specialization
(() => {
  const map = <?php echo json_encode($COURSE_MAP); ?>;
  const course = document.getElementById('courseSelect');
  const spec = document.getElementById('specSelect');
  if (!course || !spec) return;
  function fill(selectedCourse) {
    const list = map[selectedCourse] || [];
    spec.innerHTML = '<option value="">Select...</option>' + list.map(x => '<option value="'+x+'">'+x+'</option>').join('');
  }
  course.addEventListener('change', () => fill(course.value));
})();
</script>

<script>
// Ajax student email suggestions
(() => {
  const input = document.getElementById('student_email');
  const list = document.getElementById('student_list');
  if (!input || !list) return;

  let t=null;
  input.addEventListener('input', () => {
    const q = input.value.trim();
    clearTimeout(t);
    t=setTimeout(async () => {
      if (q.length < 2) { list.innerHTML=''; return; }
      const res = await fetch('../ajax/live_search.php?q=' + encodeURIComponent(q));
      const data = await res.json();
      list.innerHTML = (data || []).map(x => '<option value="'+x.email+'">'+x.name+'</option>').join('');
    }, 250);
  });
})();
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
