<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

function ensure_dir(string $dir): void { if (!is_dir($dir)) mkdir($dir,0755,true); }

function safe_unlink(?string $rel_path): void {
  if (!$rel_path) return;
  $PUBLIC_ROOT = realpath(__DIR__ . '/../public') ?: (__DIR__ . '/../public');
  $full = $PUBLIC_ROOT . '/' . ltrim($rel_path,'/');if (is_file($full)) @unlink($full);
}

function save_image(?array $file, ?string $old_path, string $rel_dir, string $prefix, int $max_bytes=2097152): ?string {
  if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return $old_path;
  if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) { set_flash('danger','Image upload failed.'); return $old_path; }
  if (($file['size'] ?? 0) > $max_bytes) { set_flash('danger','Image too large (max ' . round($max_bytes/1024/1024,1) . 'MB).'); return $old_path; }

  $tmp=$file['tmp_name'] ?? '';
  if (!$tmp || !is_uploaded_file($tmp)) { set_flash('danger','Invalid upload.'); return $old_path; }

  $mime=mime_content_type($tmp) ?: '';
  $allowed=['image/jpeg'=>'jpg','image/png'=>'png'];
  if (!isset($allowed[$mime])) { set_flash('danger','Only JPG/PNG allowed.'); return $old_path; }

  $PUBLIC_ROOT = realpath(__DIR__ . '/../public') ?: (__DIR__ . '/../public');
  $upload_dir = rtrim($PUBLIC_ROOT,'/') . '/' . trim($rel_dir,'/');
  ensure_dir($upload_dir);
  $filename=$prefix . '_' . bin2hex(random_bytes(10)) . '.' . $allowed[$mime];
  $dest = rtrim($upload_dir,'/') . '/' . $filename;

  if (!move_uploaded_file($tmp,$dest)) { set_flash('danger','Could not save image.'); return $old_path; }
  if ($old_path) safe_unlink($old_path);
  $rel_dir = rtrim($rel_dir,'/') . '/';
  return $rel_dir . $filename;
}

function save_profile_photo(?array $file, ?string $old_path=null): ?string {
  return save_image($file,$old_path,'uploads/profile/','profile',2*1024*1024);
}
function save_teacher_photo(?array $file, ?string $old_path=null): ?string {
  return save_image($file,$old_path,'uploads/teachers/','teacher',2*1024*1024);
}
function save_teacher_id_card(?array $file, ?string $old_path=null): ?string {
  return save_image($file,$old_path,'uploads/teachers_id/','idcard',2*1024*1024);
}

function save_assignment_file(?array $file, ?string $old_path=null): ?string {
  if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return $old_path;
  if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) { set_flash('danger','File upload failed.'); return $old_path; }
  if (($file['size'] ?? 0) > 10*1024*1024) { set_flash('danger','File too large (max 10MB).'); return $old_path; }
  $tmp=$file['tmp_name'] ?? '';
  if (!$tmp || !is_uploaded_file($tmp)) { set_flash('danger','Invalid upload.'); return $old_path; }

  $mime=mime_content_type($tmp) ?: '';
  $allowed=[
    'application/pdf'=>'pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx',
    'application/msword'=>'doc',
    'image/jpeg'=>'jpg',
    'image/png'=>'png'
  ];
  if (!isset($allowed[$mime])) { set_flash('danger','Allowed: PDF/DOC/DOCX/JPG/PNG only.'); return $old_path; }

  $rel_dir='uploads/assignments/'; $PUBLIC_ROOT = realpath(__DIR__ . '/../public') ?: (__DIR__ . '/../public');
  $upload_dir = rtrim($PUBLIC_ROOT,'/') . '/' . trim($rel_dir,'/');
  ensure_dir($upload_dir);
  $filename='assignment_' . bin2hex(random_bytes(10)) . '.' . $allowed[$mime];
  $dest = rtrim($upload_dir,'/') . '/' . $filename;
  if (!move_uploaded_file($tmp,$dest)) { set_flash('danger','Could not save file.'); return $old_path; }
  if ($old_path) safe_unlink($old_path);
  $rel_dir = rtrim($rel_dir,'/') . '/';
  return $rel_dir . $filename;
}

function save_submission_file(?array $file, ?string $old_path=null): ?string {
  if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return $old_path;
  if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) { set_flash('danger','File upload failed.'); return $old_path; }
  if (($file['size'] ?? 0) > 10*1024*1024) { set_flash('danger','File too large (max 10MB).'); return $old_path; }
  $tmp=$file['tmp_name'] ?? '';
  if (!$tmp || !is_uploaded_file($tmp)) { set_flash('danger','Invalid upload.'); return $old_path; }

  $mime=mime_content_type($tmp) ?: '';
  $allowed=[
    'application/pdf'=>'pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx',
    'application/msword'=>'doc',
    'application/zip'=>'zip',
    'image/jpeg'=>'jpg',
    'image/png'=>'png',
    'text/plain'=>'txt'
  ];
  if (!isset($allowed[$mime])) { set_flash('danger','Allowed: PDF/DOC/DOCX/ZIP/JPG/PNG/TXT only.'); return $old_path; }

  $rel_dir='uploads/submissions/'; $PUBLIC_ROOT = realpath(__DIR__ . '/../public') ?: (__DIR__ . '/../public');
  $upload_dir = rtrim($PUBLIC_ROOT,'/') . '/' . trim($rel_dir,'/');
  ensure_dir($upload_dir);
  $filename='sub_' . bin2hex(random_bytes(10)) . '.' . $allowed[$mime];
  $dest = rtrim($upload_dir,'/') . '/' . $filename;
  if (!move_uploaded_file($tmp,$dest)) { set_flash('danger','Could not save file.'); return $old_path; }
  if ($old_path) safe_unlink($old_path);
  $rel_dir = rtrim($rel_dir,'/') . '/';
  return $rel_dir . $filename;
}
?>