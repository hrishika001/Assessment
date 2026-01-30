<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ================================
   BASIC HELPERS
================================ */

function e($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function is_logged_in(): bool {
    return isset($_SESSION['user']);
}

function redirect(string $to): void {
    header("Location: $to");
    exit;
}


/* ================================
   AUTH + ROLE PROTECTION
================================ */

function require_login(string $base = '../'): void {
    if (!is_logged_in()) {
        header("Location: " . $base . "index.php");
        exit;
    }
}

function require_role(string $role, string $base = '../'): void {
    require_login($base);

    if (($_SESSION['user']['role'] ?? '') !== $role) {
        http_response_code(403);
        die("403 Forbidden");
    }
}

function require_any_role(array $roles, string $base = '../'): void {
    require_login($base);

    $r = $_SESSION['user']['role'] ?? '';
    if (!in_array($r, $roles, true)) {
        http_response_code(403);
        die("403 Forbidden");
    }
}


/* ================================
   DASHBOARD REDIRECT
================================ */

function redirect_dashboard(string $base = ''): void {
    if (!is_logged_in()) return;

    $role = $_SESSION['user']['role'] ?? '';

    if ($role === 'admin') {
        header("Location: {$base}admin/dashboard.php");
        exit;
    }
    if ($role === 'teacher') {
        header("Location: {$base}teacher/dashboard.php");
        exit;
    }
    if ($role === 'student') {
        header("Location: {$base}student/dashboard.php");
        exit;
    }
}


/* ================================
   FLASH MESSAGES
================================ */

function set_flash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash(): void {
    if (empty($_SESSION['flash'])) return;

    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);

    echo '<div class="alert alert-' . e($f['type']) . '">'
        . e($f['msg']) .
        '</div>';
}


/* ================================
   CSRF SECURITY
================================ */

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void {
    $t = $_POST['csrf'] ?? '';
    if (!$t || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(400);
        die("Invalid CSRF Token");
    }
}


/* ================================
   UPLOAD HELPERS (ALL FIXED)
================================ */

function _ensure_uploads_dir(): string {
    $dir = __DIR__ . '/../public/uploads/';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    return $dir;
}

function _safe_ext(string $name, array $allowed, string $fallback): string {
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    return in_array($ext, $allowed, true) ? $ext : $fallback;
}

function _save_upload(?array $file, string $prefix, array $allowed, string $fallback): ?string {

    if (!$file || ($file['error'] ?? 1) !== 0) {
        return null;
    }

    $dir = _ensure_uploads_dir();

    $ext = _safe_ext($file['name'], $allowed, $fallback);

    $newName = $prefix . "_" . time() . "_" . rand(1000, 9999) . "." . $ext;

    $target = $dir . $newName;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        return "uploads/" . $newName;
    }

    return null;
}


/* ================================
   SPECIFIC UPLOAD FUNCTIONS
================================ */

function save_profile_photo($file) {
    return _save_upload($file, "student",
        ['jpg','jpeg','png','webp','gif'], "jpg");
}

function save_teacher_photo($file) {
    return _save_upload($file, "teacher",
        ['jpg','jpeg','png','webp','gif'], "jpg");
}

function save_teacher_id_card($file) {
    return _save_upload($file, "teacher_idcard",
        ['jpg','jpeg','png','webp','gif','pdf'], "pdf");
}

function save_assignment_file($file) {
    return _save_upload($file, "assignment",
        ['pdf','doc','docx','ppt','pptx','zip','rar','jpg','jpeg','png'], "pdf");
}

function save_submission_file($file) {
    return _save_upload($file, "submission",
        ['pdf','doc','docx','ppt','pptx','zip','rar','jpg','jpeg','png'], "pdf");
}
