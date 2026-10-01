<?php
function getUsers($conn): array
{
    $users = [];
    try {
        $result = mysqli_query(
            $conn,
            "SELECT 
            u.user_id, u.first_name, u.last_name, u.email, u.access_level_id, a.access_level_name, u.is_active 
            FROM users u 
            LEFT JOIN access_levels a 
            ON u.access_level_id = a.access_level_id 
            ORDER BY u.user_id ASC"
        );
        if (!$result) {
            error_log("getUsers failed: " . mysqli_error($conn));
            return $users;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = new User(
                $row['user_id'],
                $row['first_name'],
                $row['last_name'],
                $row['email'],
                null,
                $row['access_level_id'],
                $row['is_active'],
                $row['access_level_name']
            );
        }
    } catch (mysqli_sql_exception $e) {
        error_log("getUsers exception: " . $e->getMessage());
    }
    return $users;
}

function addUser($conn, User $user): bool
{
    $query = "INSERT INTO 
    users (first_name, last_name, email, password, access_level_id, is_active) 
    VALUES (?,?,?,?,?,?)";
    try {
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            error_log("addUser prepare failed: " . mysqli_error($conn));
            return false;
        }
        $hashedPassword = password_hash($user->password, PASSWORD_DEFAULT);
        mysqli_stmt_bind_param(
            $stmt,
            'ssssii',
            $user->firstName,
            $user->lastName,
            $user->email,
            $hashedPassword,
            $user->accessLevelId,
            $user->isActive
        );
        $ok = mysqli_stmt_execute($stmt);
        if (!$ok) {
            error_log("addUser failed: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        return $ok;
    } catch (mysqli_sql_exception $e) {
        error_log("addUser exception: " . $e->getMessage());
        return false;
    }
}

function updateUser($conn, User $user): bool
{
    $query = "UPDATE users 
    SET first_name = ?, last_name = ?, email = ?, access_level_id = ?, is_active = ? 
    WHERE user_id = ?";
    try {
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            error_log("updateUser prepare failed: " . mysqli_error($conn));
            return false;
        }
        mysqli_stmt_bind_param(
            $stmt,
            'sssiii',
            $user->firstName,
            $user->lastName,
            $user->email,
            $user->accessLevelId,
            $user->isActive,
            $user->userId
        );
        $ok = mysqli_stmt_execute($stmt);
        if (!$ok) {
            error_log("updateUser failed: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        return $ok;
    } catch (mysqli_sql_exception $e) {
        error_log("updateUser exception: " . $e->getMessage());
        return false;
    }
}

function deleteUser($conn, $userId): bool
{
    $query = "DELETE FROM users WHERE user_id = ?";
    try {
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            error_log("deleteUser prepare failed: " . mysqli_error($conn));
            return false;
        }
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        $ok = mysqli_stmt_execute($stmt);
        if (!$ok) {
            error_log("deleteUser failed: " . mysqli_stmt_error($stmt));
        }
        $deleted = $ok && mysqli_stmt_affected_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        return $deleted;
    } catch (mysqli_sql_exception $e) {
        error_log("deleteUser exception: " . $e->getMessage());
        return false;
    }
}

function logAudit($conn, $action, $targetName, $details): bool
{
    $query = "INSERT INTO audit_log (action_user_name, action, target_user_name, details) VALUES (?, ?, ?, ?)";
    try {
        $adminId = $_SESSION['user_id'];
        $nameStmt = mysqli_prepare($conn, "SELECT first_name, last_name FROM users WHERE user_id = ?");
        mysqli_stmt_bind_param($nameStmt, 'i', $adminId);
        mysqli_stmt_execute($nameStmt);
        mysqli_stmt_bind_result($nameStmt, $adminFirst, $adminLast);
        mysqli_stmt_fetch($nameStmt);
        mysqli_stmt_close($nameStmt);
        $adminName = $adminFirst . " " . $adminLast;

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssss', $adminName, $action, $targetName, $details);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    } catch (mysqli_sql_exception $e) {
        error_log("logAudit exception: " . $e->getMessage());
        return false;
    }
}
?>

