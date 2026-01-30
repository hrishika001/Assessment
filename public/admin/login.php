<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
$expected_role = 'admin';
$title = 'Admin Login | Student Management Portal';
$desc  = 'Admin login page';
$base_url = '../';

if (is_logged_in()) redirect_dashboard('../');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $email = trim($_POST['email'] ?? '');
  $pass  = (string)($_POST['password'] ?? '');

  if ($email === '' || $pass === '') {
    set_flash('danger', 'Please enter your email and password.');
  } else {
    $u = find_user_by_email($email);
    if (!$u || !password_verify($pass, (string)$u['password'])) {
      set_flash('danger', 'Invalid login details.');
    } elseif (($u['role'] ?? '') !== $expected_role) {
      set_flash('warning', 'You are not allowed to log in from this page.');
    } else {
      login_user($u);
      set_flash('success', 'Welcome back!');
      redirect_dashboard('../');
    }
  }
}

include __DIR__ . '/../../includes/header.php';
?>
<section class="center-wrap">
  <div class="card auth-card">
    <div class="auth-header">
      <h1>Admin Login</h1>
      <p class="muted">Use your portal account to continue.</p>
    </div>
    <div class="auth-body">
      <form method="post" autocomplete="on">
        <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
        <div class="form-row">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" required placeholder="name@example.com" value="<?php echo e($_POST['email'] ?? ''); ?>">
        </div>

        <div class="form-row">
          <label for="password">Password</label>
          <div class="input-group">
            <input id="password" name="password" type="password" required placeholder="Enter password">
            <button class="btn btn-light" type="button" data-toggle-password="password">Show</button>
          </div>
        </div>

        <div class="form-row">
          <button class="btn btn-primary" type="submit">Login</button>
          <div class="small">No signup: accounts are created by the admin.</div>
        </div>
      </form>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
