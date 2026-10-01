<?php
require_once "sessions.php";
require_admin_session(true);
require_once "csrf.php";
require_once "config/dbconnect.php";
require_once "User.php";
require_once "userActions.php";

if (!csrf_panel_verify($_POST['csrf_token'] ?? '')) {
    http_response_code(400);
    echo "invalid_token";
    exit;
}

if (!isset($_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['password'], $_POST['access_level_id'])) {
    echo "missing_fields";
    exit;
}

$firstName = trim($_POST['first_name']);
$lastName = trim($_POST['last_name']);
if (
    $firstName === '' || $lastName === ''
    || mb_strlen($firstName) > 50 || mb_strlen($lastName) > 50
) {
    echo "invalid_name";
    exit;
}

$email = strtolower(trim($_POST['email']));
if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "invalid_email";
    exit;
}

$password = $_POST['password'];
if (strlen($password) < 6 || strlen($password) > 72) {
    echo "invalid_password";
    exit;
}

$accessLevelId = filter_var(
    $_POST['access_level_id'],
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1, 'max_range' => 4]]
);
if ($accessLevelId === false) {
    echo "invalid_access_level";
    exit;
}

$isActive = 1;

$checkQuery = "SELECT user_id FROM users WHERE email = ?";
$checkStmt = mysqli_prepare($conn, $checkQuery);
mysqli_stmt_bind_param($checkStmt, 's', $email);
mysqli_stmt_execute($checkStmt);
mysqli_stmt_store_result($checkStmt);

if (mysqli_stmt_num_rows($checkStmt) > 0) {
    echo "duplicate_email";
    exit;
}
mysqli_stmt_close($checkStmt);

$user = new User(null, $firstName, $lastName, $email, $password, $accessLevelId, $isActive);
if (!addUser($conn, $user)) {
    echo "error";
    exit;
}

echo "success";