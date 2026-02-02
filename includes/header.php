<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$base_url = $base_url ?? './';
$asset_url = $asset_url ?? ($base_url . '../assets/');

$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? null;
$name = $user['name'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo e($title ?? 'Student Management Portal'); ?></title>
  <meta name="description" content="<?php echo e($desc ?? 'Student Management Portal'); ?>">
  <link rel="stylesheet" href="<?php echo e($asset_url); ?>css/style.css?v=4">
</head>
<body>

<!-- Background layer-->
<div class="bg" aria-hidden="true"></div>

<header class="topbar">
  <a class="brand" href="<?php echo e($base_url); ?>index.php">
    <span class="brand-mark">SMP</span>
    <span>
      <div class="brand-title">Student Management Portal</div>
      <div class="brand-sub">University Dashboard • Attendance • Assignments • Grades</div>
    </span>
  </a>

  <div class="nav" id="navMenu">
    <?php if ($role === 'admin'): ?>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>index.php">Home</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>admin/dashboard.php">Dashboard</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>admin/students.php">Students</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>admin/teachers.php">Teachers</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>admin/announcements.php">Announcements</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>admin/attendance.php">Attendance</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>admin/assignments.php">Assignments</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>admin/grades.php">Grades</a>
      <span class="chip">Portal Admin • <?php echo e($name); ?></span>
      <a class="btn btn-light" href="<?php echo e($base_url); ?>logout.php">Logout</a>

    <?php elseif ($role === 'teacher'): ?>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>index.php">Home</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>teacher/dashboard.php">Dashboard</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>teacher/attendance.php">Attendance</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>teacher/assignments.php">Assignments</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>teacher/announcements.php">Announcements</a>
      <span class="chip">Teacher • <?php echo e($name); ?></span>
      <a class="btn btn-light" href="<?php echo e($base_url); ?>logout.php">Logout</a>

    <?php elseif ($role === 'student'): ?>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>index.php">Home</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>student/dashboard.php">Dashboard</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>student/attendance.php">Attendance</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>student/assignments.php">Assignments</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>student/results.php">Results</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>student/announcements.php">Announcements</a>
      <span class="chip">Student • <?php echo e($name); ?></span>
      <a class="btn btn-light" href="<?php echo e($base_url); ?>logout.php">Logout</a>
    <?php else: ?>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>index.php">Home</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>admin/login.php">Admin Login</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>teacher/login.php">Teacher Login</a>
      <a class="btn btn-ghost" href="<?php echo e($base_url); ?>student/login.php">Student Login</a>
    <?php endif; ?>
  </div>
</header>

<main class="container">
