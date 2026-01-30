<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/init_admin.php';

ensure_default_admin($conn);

function find_user_by_email(string $email): ?array {
  global $conn;
  $stmt=$conn->prepare('SELECT id,name,email,password,role FROM users WHERE email=? LIMIT 1');
  $stmt->bind_param('s',$email);
  $stmt->execute();
  $u=$stmt->get_result()->fetch_assoc();
  return $u ?: null;
}

function login_user(array $u): void {
  $_SESSION['user'] = [
    'id'=>(int)$u['id'],
    'name'=>(string)$u['name'],
    'email'=>(string)$u['email'],
    'role'=>(string)$u['role'],
  ];
}

function logout_user(): void {
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $p=session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
  }
  session_destroy();
}

function create_user(string $name,string $email,string $password,string $role): int {
  global $conn;
  $hash=password_hash($password,PASSWORD_DEFAULT);
  $stmt=$conn->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)');
  $stmt->bind_param('ssss',$name,$email,$hash,$role);
  $stmt->execute();
  return (int)$conn->insert_id;
}

function update_user_basic(int $id,string $name,string $email): void {
  global $conn;
  $stmt=$conn->prepare('UPDATE users SET name=?, email=? WHERE id=?');
  $stmt->bind_param('ssi',$name,$email,$id);
  $stmt->execute();
}
function update_user_password(int $id,string $new_password): void {
  global $conn;
  $hash=password_hash($new_password,PASSWORD_DEFAULT);
  $stmt=$conn->prepare('UPDATE users SET password=? WHERE id=?');
  $stmt->bind_param('si',$hash,$id);
  $stmt->execute();
}
function delete_user(int $id): void {
  global $conn;
  $stmt=$conn->prepare('DELETE FROM users WHERE id=?');
  $stmt->bind_param('i',$id);
  $stmt->execute();
}

function create_or_update_student_profile(int $user_id,string $course,string $specialization,?string $photo): void {
  global $conn;
  $stmt=$conn->prepare("INSERT INTO students (user_id,course,specialization,photo)
                        VALUES (?,?,?,?)
                        ON DUPLICATE KEY UPDATE course=VALUES(course), specialization=VALUES(specialization),
                        photo=COALESCE(VALUES(photo), photo)");
  $stmt->bind_param('isss',$user_id,$course,$specialization,$photo);
  $stmt->execute();
}
function create_or_update_teacher_profile(int $user_id,string $department, ?string $photo, ?string $id_card): void {
  global $conn;
  $stmt=$conn->prepare("INSERT INTO teachers (user_id,department,photo,id_card)
                        VALUES (?,?,?,?)
                        ON DUPLICATE KEY UPDATE department=VALUES(department),
                        photo=COALESCE(VALUES(photo), photo),
                        id_card=COALESCE(VALUES(id_card), id_card)");
  $stmt->bind_param('isss',$user_id,$department,$photo,$id_card);
  $stmt->execute();
}

function get_student_full(int $user_id): ?array {
  global $conn;
  $stmt=$conn->prepare("SELECT u.id,u.name,u.email,u.created_at, s.course,s.specialization,s.photo
                         FROM users u LEFT JOIN students s ON s.user_id=u.id
                         WHERE u.id=? AND u.role='student' LIMIT 1");
  $stmt->bind_param('i',$user_id);
  $stmt->execute();
  $r=$stmt->get_result()->fetch_assoc();
  return $r ?: null;
}

function get_teacher_full(int $user_id): ?array {
  global $conn;
  $stmt=$conn->prepare("SELECT u.id,u.name,u.email,u.created_at, t.department,t.photo,t.id_card
                         FROM users u LEFT JOIN teachers t ON t.user_id=u.id
                         WHERE u.id=? AND u.role='teacher' LIMIT 1");
  $stmt->bind_param('i',$user_id);
  $stmt->execute();
  $r=$stmt->get_result()->fetch_assoc();
  return $r ?: null;
}
?>