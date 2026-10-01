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

if (!isset($_POST['user_id'], $_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['access_level_id'])) {
    echo "missing_fields";
    exit;
}

$userId = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
if ($userId === false || $userId < 1) {
    echo "invalid_user_id";
    exit;
}

$firstName = trim($_POST['first_name']);
$lastName = trim($_POST['last_name']);
if ($firstName === '' || $lastName === ''
    || mb_strlen($firstName) > 50 || mb_strlen($lastName) > 50) {
    echo "invalid_name";
    exit;
}

$email = strtolower(trim($_POST['email']));
if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "invalid_email";
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

$isActive = isset($_POST['is_active']) ? 1 : 0;

if ($userId === (int) $_SESSION['user_id']
    && ($accessLevelId !== ROLE_ADMIN || $isActive !== 1)) {
    echo "cannot_change_own_access";
    exit;
}

$checkQuery = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
$checkStmt = mysqli_prepare($conn, $checkQuery);
mysqli_stmt_bind_param($checkStmt, 'si', $email, $userId);
mysqli_stmt_execute($checkStmt);
mysqli_stmt_store_result($checkStmt);

if (mysqli_stmt_num_rows($checkStmt) > 0) {
    echo "duplicate_email";
    exit;
}
mysqli_stmt_close($checkStmt);

$user = new User($userId, $firstName, $lastName, $email, null, $accessLevelId, $isActive);
if (!updateUser($conn, $user)) {
    echo "error";
    exit;
}

echo "success";