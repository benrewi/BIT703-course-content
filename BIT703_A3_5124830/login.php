<?php
require_once "sessions.php";
require_once "csrf.php";

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

require_once "config/dbconnect.php";
$email = $password = "";
$email_err = $password_err = $login_err = "";

if (isset($_GET['reason']) && $_GET['reason'] === 'unauthenticated') {
    $login_err = "Please log in to continue.";
} elseif (isset($_GET['reason']) && $_GET['reason'] === 'timeout') {
    $login_err = "Your session timed out due to inactivity. Please log in again.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!csrf_verify($_POST["csrf_token"] ?? "")) {
        http_response_code(400);
        $login_err = "This form has expired. Please try logging in again.";
    } else {
        unset($_SESSION["csrf_token"], $_SESSION["csrf_time"]);

        if (!isset($_POST["email"]) || empty(trim($_POST["email"]))) {
            $email_err = "Please enter email.";
        } else {
            $email = strtolower(trim($_POST["email"]));
            if (strlen($email) > 254) {
                $email_err = "Email is too long.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email_err = "Please enter a valid email address.";
            }
        }
        if (!isset($_POST["password"]) || empty(trim($_POST["password"]))) {
            $password_err = "Please enter your password.";
        } else {
            $password = $_POST["password"];
            if (strlen($password) > 72) {
                $password_err = "Invalid password. Please re-enter your details.";
            }
        }
        if (empty($email_err) && empty($password_err)) {
            $sql = "SELECT user_id, email, password, access_level_id, is_active FROM users WHERE email = ?";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $param_email);
                $param_email = $email;

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_store_result($stmt);

                    if (mysqli_stmt_num_rows($stmt) == 1) {
                        mysqli_stmt_bind_result($stmt, $id, $email, $hashed_password, $access_level_id, $is_active);
                        if (mysqli_stmt_fetch($stmt)) {
                            if (password_verify($password, $hashed_password)) {
                                if ((int) $access_level_id === ROLE_ADMIN && (int) $is_active === 1) {
                                    session_regenerate_id(true);
                                    $_SESSION["loggedin"] = true;
                                    $_SESSION["user_id"] = $id;
                                    $_SESSION["email"] = $email;
                                    $_SESSION["access_level_id"] = (int) $access_level_id;
                                    $_SESSION["last_activity"] = time();
                                    header("location: index.php");
                                    exit;
                                } else {
                                    $login_err = "Invalid email or password.";
                                }
                            } else {
                                $login_err = "Invalid email or password.";
                            }
                        }
                    } else {
                        $login_err = "Invalid email or password.";
                    }
                } else {
                    $login_err = "Something went wrong. Please try again.";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Login - Aotearoa Adventure Gear</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="style.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;700;800;900&display=swap" rel="stylesheet">
</head>

<body class="custom-background">

    <?php
    include "sidebar.php";
    ?>

    <div class="login-main d-flex justify-content-center align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="card p-4 shadow-lg">
                        <img src="images/logo-full.png" class="card-img-top mx-auto d-block w-50 mt-3"
                            alt="Aotearoa Adventure Gear Logo">
                        <div class="card-body mt-4">
                            <?php $form_err = $login_err ?: ($email_err ?: $password_err); ?>
                            <?php if (!empty($form_err)): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($form_err) ?></div>
                            <?php endif; ?>
                            <form method="post" action="login.php" class="needs-validation" novalidate>
                                <input type="hidden" name="csrf_token"
                                    value="<?= htmlspecialchars(csrf_generate(), ENT_QUOTES, 'UTF-8') ?>">
                                <label for="email" class="ps-1">Email:</label>
                                <div class="mb-5">
                                    <input class="w-100 form-control" type="email" id="email" name="email"
                                        maxlength="254" required>
                                    <div class="invalid-feedback">Please enter a valid email address</div>
                                </div>
                                <label for="password" class="ps-1">Password:</label>
                                <div class="mb-5">
                                    <input class="w-100 form-control" type="password" id="password" name="password"
                                        maxlength="72" required>
                                    <div class="invalid-feedback">Please enter a password</div>
                                </div>
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary text-center w-75 bg-black mt-4">Log
                                        In</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
    <script>
        document.querySelector("form.needs-validation").addEventListener("submit", function (e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                this.classList.add("was-validated");
            }
        });
    </script>
</body>

</html>