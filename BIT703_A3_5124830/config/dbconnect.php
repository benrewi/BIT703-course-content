<?php
require_once __DIR__ . "/config.php";
$server = DBHOST;
$user = DBUSER;
$password = DBPASSWORD;
$db = DBDATABASE;

try {
    $conn = mysqli_connect($server, $user, $password, $db);
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    echo "Cannot connect to database server";
    exit;
}