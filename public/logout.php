<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
logout_user();
header('Location: index.php');
exit;
?>
$base_url = './';
$asset_url = $base_url . '../assets/';
include __DIR__ . '/../includes/header.php';
<?php include __DIR__ . '/../includes/footer.php';
?>
