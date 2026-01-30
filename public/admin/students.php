<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('admin','../');


$title='Manage Students | Admin';
$desc='Create and manage student accounts';
$base_url='../';

$COURSE_MAP = [
  'BIBM' => ['BCA','BIM'],
  'IT'   => ['Cybersecurity','AI'],
];

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

// Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $form_action = $_POST['action'] ?? '';
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = (string)($_POST['password'] ?? '');
  $course = trim($_POST['course'] ?? '');
  $specialization = trim($_POST['specialization'] ?? '');

  if ($name === '' || $email === '' || $course === '' || $specialization === '') {
    set_flash('danger','Please fill all required fields.');
  } elseif (!isset($COURSE_MAP[$course]) || !in_array($specialization, $COURSE_MAP[$course], true)) {
    set_flash('danger','Invalid course/specialization selected.');
  } else {
    if ($form_action === 'create') {
      if ($password === '') { set_flash('danger','Password is required for new student.'); }
      else {
        try {
          $user_id = create_user($name, $email, $password, 'student');
          $photo = save_profile_photo($_FILES['photo'] ?? null, null);
          create_or_update_student_profile($user_id, $course, $specialization, $photo);
          set_flash('success','Student created successfully.');
          header('Location: students.php'); exit;
        } catch (mysqli_sql_exception $e) {
          set_flash('danger','Could not create student. Email might already exist.');
        }
      }
    }

    if ($form_action === 'update') {
      $user_id = (int)($_POST['id'] ?? 0);
      if ($user_id <= 0) { set_flash('danger','Invalid student.'); }
      else {
        $student = get_student_full($user_id);
        if (!$student) { set_flash('danger','Student not found.'); }
        else {
          update_user_basic($user_id, $name, $email);
          if ($password !== '') update_user_password($user_id, $password);
          $photo = save_profile_photo($_FILES['photo'] ?? null, $student['photo'] ?? null);
          create_or_update_student_profile($user_id, $course, $specialization, $photo);
          set_flash('success','Student updated.');
          header('Location: students.php'); exit;
        }
      }
    }
  }
}

// Delete
if ($action === 'delete' && $id > 0) {
  verify_csrf();
  $student = get_student_full($id);
  if ($student && !empty($student['photo'])) safe_unlink($student['photo']);
  delete_user($id);
  set_flash('success','Student deleted.');
  header('Location: students.php'); exit;
}

// Fetch list
$q = trim($_GET['q'] ?? '');
$like = '%' . $q . '%';
if ($q !== '') {
  $stmt = $conn->prepare("SELECT u.id,u.name,u.email,u.created_at,s.course,s.specialization,s.photo
                          FROM users u LEFT JOIN students s ON s.user_id=u.id
                          WHERE u.role='student' AND (u.name LIKE ? OR u.email LIKE ? OR s.specialization LIKE ?)
                          ORDER BY u.id DESC");
  $stmt->bind_param('sss', $like, $like, $like);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
  $rows = $conn->query("SELECT u.id,u.name,u.email,u.created_at,s.course,s.specialization,s.photo
                        FROM users u LEFT JOIN students s ON s.user_id=u.id
                        WHERE u.role='student' ORDER BY u.id DESC LIMIT 200")->fetch_all(MYSQLI_ASSOC);
}

// Edit data if needed
$edit = null;
if ($action === 'edit' && $id > 0) $edit = get_student_full($id);

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Students</h1>
  <p>Create student login accounts (admin only) and manage their profiles.</p>
</section>

<section class="card pad">
  <form method="get" class="grid" style="align-items:end">
    <div style="grid-column:span 8">
      <label for="q">Search</label>
      <input id="q" name="q" placeholder="Search by name, email, specialization..." value="<?php echo e($q); ?>">
    </div>
    <div style="grid-column:span 4; display:flex; gap:.6rem">
      <button class="btn btn-primary" type="submit">Search</button>
      <a class="btn btn-light" href="students.php">Reset</a>
      <a class="btn btn-light" href="students.php?action=new">+ New</a>
    </div>
  </form>
</section>

<?php if ($action === 'new' || $edit): ?>
<section class="card pad" style="margin-top:1rem">
  <h2><?php echo $edit ? 'Edit Student' : 'Add New Student'; ?></h2>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
    <input type="hidden" name="action" value="<?php echo $edit ? 'update' : 'create'; ?>">
    <?php if ($edit): ?><input type="hidden" name="id" value="<?php echo e($edit['id']); ?>"><?php endif; ?>

    <div class="grid">
      <div style="grid-column:span 6" class="form-row">
        <label>Name <span class=\"req\">*</span></label>
        <input name="name" required value="<?php echo e($edit['name'] ?? ''); ?>">
      </div>
      <div style="grid-column:span 6" class="form-row">
        <label>Email <span class=\"req\">*</span></label>
        <input name="email" type="email" required value="<?php echo e($edit['email'] ?? ''); ?>">
      </div>

      <div style="grid-column:span 6" class="form-row">
        <label>Password <?php echo $edit ? '(leave blank to keep current)' : ''; ?></label>
        <input name="password" type="text" <?php echo $edit ? '' : 'required'; ?> placeholder="<?php echo $edit ? 'Optional' : 'Set a password'; ?>">
        <div class="small">Passwords are stored securely using password_hash().</div>
      </div>

      <div style="grid-column:span 6" class="form-row">
        <label>Course <span class=\"req\">*</span></label>
        <select name="course" id="courseSelect" required>
          <option value="">Select...</option>
          <?php foreach(array_keys($COURSE_MAP) as $c): ?>
            <option value="<?php echo e($c); ?>" <?php echo (($edit['course'] ?? '') === $c) ? 'selected' : ''; ?>><?php echo e($c); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="grid-column:span 6" class="form-row">
        <label>Program / Specialization</label>
        <select name="specialization" id="specSelect" required>
          <option value="">Select...</option>
          <?php
          $currentCourse = $edit['course'] ?? '';
          $specs = $currentCourse && isset($COURSE_MAP[$currentCourse]) ? $COURSE_MAP[$currentCourse] : [];
          foreach ($specs as $sp):
        ?>
            <option value="<?php echo e($sp); ?>" <?php echo (($edit['specialization'] ?? '') === $sp) ? 'selected' : ''; ?>><?php echo e($sp); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="grid-column:span 6" class="form-row">
        <label>Photo (JPG/PNG)</label>
        <input name="photo" type="file" accept=".jpg,.jpeg,.png" required>
        <?php if (!empty($edit['photo'])): ?>
          <div class="small">Current: <a href="../<?php echo e($edit['photo']); ?>" target="_blank" rel="noopener">view photo</a></div>
        <?php endif; ?>
      </div>
    </div>

    <div style="display:flex; gap:.6rem; margin-top:1rem">
      <button class="btn btn-primary" type="submit"><?php echo $edit ? 'Save Changes' : 'Create Student'; ?></button>
      <a class="btn btn-light" href="students.php">Cancel</a>
    </div>
  </form>
</section>
<?php endif; ?>

<section class="card pad" style="margin-top:1rem">
  <h2>Student List</h2>
  <div style="overflow:auto">
    <table class="table" aria-label="Student table">
      <thead>
        <tr>
          <th>ID</th><th>Photo</th><th>Name</th><th>Email</th><th>Course</th><th>Specialization</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?php echo e($r['id']); ?></td>
            <td><?php if (!empty($r['photo'])): ?><img src="../<?php echo e($r['photo']); ?>" alt="Photo" style="width:38px;height:38px;border-radius:12px;object-fit:cover"><?php else: ?>—<?php endif; ?></td>
            <td><?php echo e($r['name']); ?></td>
            <td><?php echo e($r['email']); ?></td>
            <td><?php echo e($r['course'] ?? ''); ?></td>
            <td><?php echo e($r['specialization'] ?? ''); ?></td>
            <td style="white-space:nowrap">
              <a class="btn btn-light" href="students.php?action=edit&id=<?php echo e($r['id']); ?>">Edit</a>
              <form method="post" action="students.php?action=delete&id=<?php echo e($r['id']); ?>" style="display:inline">
                <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
                <button class="btn btn-danger" data-confirm="Delete this student account?" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
          <tr><td colspan="7">No students found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>


<script>
(() => {
  const map = <?php echo json_encode($COURSE_MAP); ?>;
  const course = document.getElementById('courseSelect');
  const spec = document.getElementById('specSelect');
  if (!course || !spec) return;

  function fillSpecs(selectedCourse, selectedSpec='') {
    const list = map[selectedCourse] || [];
    spec.innerHTML = '<option value="">Select...</option>' + list.map(x => {
      const sel = (x === selectedSpec) ? ' selected' : '';
      return '<option value="'+x+'"'+sel+'>'+x+'</option>';
    }).join('');
  }

  course.addEventListener('change', () => fillSpecs(course.value, ''));

  // on edit: prefill
  fillSpecs(course.value, <?php echo json_encode($edit['specialization'] ?? ''); ?>);
})();
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>