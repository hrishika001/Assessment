<?php
// Auto-creates the default admin if not present.
// (Credentials are written only in README.md, never shown in UI.)
function ensure_default_admin(mysqli $conn): void {
  $email = 'admin@portal.com';
  $stmt = $conn->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
  $stmt->bind_param('s', $email);
  $stmt->execute();
  if ($stmt->get_result()->fetch_assoc()) return;

  $name='Portal Admin';
  $role='admin';
  $hash=password_hash('admin123', PASSWORD_DEFAULT);

  $stmt = $conn->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)');
  $stmt->bind_param('ssss', $name, $email, $hash, $role);
  $stmt->execute();
}
?>