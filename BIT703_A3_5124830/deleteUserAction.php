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

if (!isset($_POST['user_id'])) {
    echo "missing_fields";
    exit;
}

$userId = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
if ($userId === false || $userId < 1) {
    echo "invalid_user_id";
    exit;
}

if ($userId === (int) $_SESSION['user_id']) {
    echo "cannot_delete_self";
    exit;
}
if (!deleteUser($conn, $userId)) {
    echo "error";
    exit;
}

echo "success";