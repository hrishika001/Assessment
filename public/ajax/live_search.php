<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
// ajax/live_search.php
declare(strict_types=1);

require_any_role(['admin','teacher'],'../'); // only staff can search students
header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
if ($q === '' || strlen($q) < 2) { echo json_encode([]); exit; }

$like = '%' . $q . '%';
$stmt = $conn->prepare("SELECT id,name,email
                        FROM users
                        WHERE role='student' AND (name LIKE ? OR email LIKE ?)
                        ORDER BY id DESC
                        LIMIT 10");
$stmt->bind_param('ss', $like, $like);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode($rows);
?>