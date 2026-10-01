<?php
function csrf_generate(int $ttl = 900): string
{
    if (
        empty($_SESSION['csrf_token'])
        || !isset($_SESSION['csrf_time'])
        || (time() - $_SESSION['csrf_time']) > $ttl
    ) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(string $token, int $ttl = 900): bool
{
    if (!isset($_SESSION['csrf_token'], $_SESSION['csrf_time'])) {
        return false;
    }
    if ((time() - $_SESSION['csrf_time']) > $ttl) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_panel_token(): string
{
    if (empty($_SESSION['csrf_panel_token'])) {
        $_SESSION['csrf_panel_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_panel_token'];
}

function csrf_panel_verify(string $token): bool
{
    if (empty($_SESSION['csrf_panel_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_panel_token'], $token);
}
?>