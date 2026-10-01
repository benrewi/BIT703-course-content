<?php
require_once "config/config.php";

ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_samesite', 'Strict');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function end_session(): void
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $p['path'],
            $p['domain'],
            $p['secure'],
            $p['httponly']
        );
    }
    session_destroy();
}

function reject_session(string $reason, bool $isApi): void
{
    if ($isApi) {
        http_response_code(401);
        echo "unauthorized";
        exit;
    }
    header("Location: login.php?reason=" . $reason);
    exit;
}

function require_admin_session(bool $isApi = false): void
{
    header("Cache-Control: no-store");

    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        reject_session("unauthenticated", $isApi);
    }

    if ((int) ($_SESSION["access_level_id"] ?? 0) !== ROLE_ADMIN) {
        end_session();
        reject_session("unauthenticated", $isApi);
    }

    if (
        isset($_SESSION["last_activity"])
        && (time() - $_SESSION["last_activity"]) > SESSION_TIMEOUT
    ) {
        end_session();
        reject_session("timeout", $isApi);
    }

    $_SESSION["last_activity"] = time();
}