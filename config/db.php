<?php
// config/db.php
// local host credentials:
$DB_HOST = "localhost";
$DB_NAME = "student_portal";
$DB_USER = "root";
$DB_PASS = "";

//Server Credentials:
//$DB_HOST = "localhost";
//$DB_NAME = "NP03CY4S250021";
//$DB_USER = "NP03CY4S250021";
//$DB_PASS = "RTnWTjiGKY";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    http_response_code(500);
    die("Database connection failed. Please check config/db.php");
}

// Auto create default admin account (safe)
require_once __DIR__ . "/init_admin.php";
ensure_default_admin($conn);
?>
