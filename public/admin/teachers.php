<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('admin','../');


$title='Manage Teachers | Admin';
$desc='Create and manage teacher accounts';
$base_url='../';

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $form_action = $_POST['action'] ?? '';
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = (string)($_POST['password'] ?? '');
  $department = trim($_POST['department'] ?? '');
  $photo = null;
  $id_card = null;

  if ($name === '' || $email === '' || $department === '') {
    set_flash('danger','Please fill all required fields.');
  } else {
    if ($form_action === 'create') {
      if ($password === '') { set_flash('danger','Password is required for new teacher.'); }
      else {
        try {
          $user_id = create_user($name, $email, $password, 'teacher');
          $told = get_teacher_full($user_id);
          $photo = save_teacher_photo($_FILES['photo'] ?? null, $told['photo'] ?? null);
          $id_card = save_teacher_id_card($_FILES['id_card'] ?? null, $told['id_card'] ?? null);
          create_or_update_teacher_profile($user_id, $department, $photo, $id_card);
set_flash('success','Teacher created successfully.');
          header('Location: teachers.php'); exit;
        } catch (mysqli_sql_exception $e) {
          set_flash('danger','Could not create teacher. Email might already exist.');
        }
      }
    }

    if ($form_action === 'update') {
      $user_id = (int)($_POST['id'] ?? 0);
      if ($user_id <= 0) { set_flash('danger','Invalid teacher.'); }
      else {
        $t = get_teacher_full($user_id);
        if (!$t) { set_flash('danger','Teacher not found.'); }
        else {
          update_user_basic($user_id, $name, $email);
          if ($password !== '') update_user_password($user_id, $password);
          $photo = save_teacher_photo($_FILES['photo'] ?? null, $t['photo'] ?? null);
          $id_card = save_teacher_id_card($_FILES['id_card'] ?? null, $t['id_card'] ?? null);
          create_or_update_teacher_profile($user_id, $department, $photo, $id_card);
set_flash('success','Teacher updated.');
          header('Location: teachers.php'); exit;
        }
      }
    }
  }
}

if ($action === 'delete' && $id > 0) {
  verify_csrf();
  delete_user($id);
  set_flash('success','Teacher deleted.');
  header('Location: teachers.php'); exit;
}

$q = trim($_GET['q'] ?? '');
$like = '%' . $q . '%';
if ($q !== '') {
  $stmt = $conn->prepare("SELECT u.id,u.name,u.email,u.created_at,t.department,t.photo,t.id_card
                          FROM users u LEFT JOIN teachers t ON t.user_id=u.id
                          WHERE u.role='teacher' AND (u.name LIKE ? OR u.email LIKE ? OR t.department LIKE ?)
                          ORDER BY u.id DESC");
  $stmt->bind_param('sss', $like, $like, $like);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
  $rows = $conn->query("SELECT u.id,u.name,u.email,u.created_at,t.department,t.photo,t.id_card
                        FROM users u LEFT JOIN teachers t ON t.user_id=u.id
                        WHERE u.role='teacher' ORDER BY u.id DESC LIMIT 200")->fetch_all(MYSQLI_ASSOC);
}

$edit = null;
if ($action === 'edit' && $id > 0) $edit = get_teacher_full($id);

include __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Teachers</h1>
  <p>Create teacher login accounts and manage departments.</p>
</section>

<section class="card pad">
  <form method="get" class="grid" style="align-items:end">
    <div style="grid-column:span 8">
      <label for="q">Search</label>
      <input id="q" name="q" placeholder="Search by name, email, department..." value="<?php echo e($q); ?>">
    </div>
    <div style="grid-column:span 4; display:flex; gap:.6rem">
      <button class="btn btn-primary" type="submit">Search</button>
      <a class="btn btn-light" href="teachers.php">Reset</a>
      <a class="btn btn-light" href="teachers.php?action=new">+ New</a>
    </div>
  </form>
</section>

<?php if ($action === 'new' || $edit): ?>
<section class="card pad" style="margin-top:1rem">
  <h2><?php echo $edit ? 'Edit Teacher' : 'Add New Teacher'; ?></h2>
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
        <input name="password" type="text" <?php echo $edit ? '' : 'required'; ?>>
      </div>

      <div style="grid-column:span 6" class="form-row">
        <label>Department <span class=\"req\">*</span></label>
        <input name="department" required value="<?php echo e($edit['department'] ?? ''); ?>" placeholder="e.g., Computing">
      </div>

      <div style="grid-column:span 6" class="form-row">
        <label>Teacher Photo (JPG/PNG)</label>
        <input name="photo" type="file" accept=".jpg,.jpeg,.png" required>
        <?php if (!empty($edit['photo'])): ?>
          <div class="small">Current: <a href="../<?php echo e($edit['photo']); ?>" target="_blank" rel="noopener">view photo</a></div>
        <?php endif; ?>
      </div>

      <div style="grid-column:span 6" class="form-row">
        <label>Teacher ID Card (JPG/PNG)</label>
        <input name="id_card" type="file" accept=".jpg,.jpeg,.png" required>
        <?php if (!empty($edit['id_card'])): ?>
          <div class="small">Current: <a href="../<?php echo e($edit['id_card']); ?>" target="_blank" rel="noopener">view ID card</a></div>
        <?php endif; ?>
      </div>
    </div>

    <div style="display:flex; gap:.6rem; margin-top:1rem">
      <button class="btn btn-primary" type="submit"><?php echo $edit ? 'Save Changes' : 'Create Teacher'; ?></button>
      <a class="btn btn-light" href="teachers.php">Cancel</a>
    </div>
  </form>
</section>
<?php endif; ?>

<section class="card pad" style="margin-top:1rem">
  <h2>Teacher List</h2>
  <div style="overflow:auto">
    <table class="table" aria-label="Teacher table">
      <thead>
        <tr>
          <th>ID</th><th>Photo</th><th>Name</th><th>Email</th><th>Department</th><th>ID Card</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?php echo e($r['id']); ?></td>
            <td><?php if(!empty($r['photo'])): ?><img src="../<?php echo e($r['photo']); ?>" alt="Photo" style="width:38px;height:38px;border-radius:12px;object-fit:cover"><?php else: ?>—<?php endif; ?></td>
            <td><?php echo e($r['name']); ?></td>
            <td><?php echo e($r['email']); ?></td>
            <td><?php echo e($r['department'] ?? ''); ?></td>
            <td><?php if(!empty($r['id_card'])): ?><a class="btn btn-light" href="../<?php echo e($r['id_card']); ?>" target="_blank" rel="noopener">View</a><?php else: ?>—<?php endif; ?></td>
            <td style="white-space:nowrap">
              <a class="btn btn-light" href="teachers.php?action=edit&id=<?php echo e($r['id']); ?>">Edit</a>
              <form method="post" action="teachers.php?action=delete&id=<?php echo e($r['id']); ?>" style="display:inline">
                <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
                <button class="btn btn-danger" data-confirm="Delete this teacher account?" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
          <tr><td colspan="7">No teachers found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
