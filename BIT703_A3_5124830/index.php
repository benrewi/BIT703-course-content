<?php
require_once "sessions.php";
require_admin_session();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Aotearoa Adventure Gear</title>
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
    include_once "./config/dbconnect.php";

    $activeUsers = 0;
    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE is_active = '1'");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $activeUsers = $row["total"];
    }
    ?>

    <div id="page-content" class="page-content mt-5">
        <h1 class="display-4 fw-semibold">Dashboard</h1>
        <div class="row mt-5">
            <div class="col-md-3">
                <div class="card p-3 text-center shadow">
                    <h5 class="card-title">Active Users</h5>
                    <p class="display-4"><?php echo $activeUsers; ?></p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 text-center shadow">
                    <h5 class="card-title">Total Orders</h5>
                    <p class="display-4">56</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 text-center shadow">
                    <h5 class="card-title">Active Categories</h5>
                    <p class="display-4">8</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 text-center shadow">
                    <h5 class="card-title">Total Products</h5>
                    <p class="display-4">25</p>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <div class="card p-4 shadow">
                <h5 class="card-title fs-3 fw-semibold">User Access Management</h5>
                <p class="fs-6 mt-2">Create new accounts, update user access roles, or manage active system users across
                    Admin, Manager, Salesperson, and Customer levels.</p>
                <div>
                    <a href="#users" onClick="showUsers()" class="btn btn-dark px-4 mt-3 shadow">Go to User
                        Management</a>
                </div>
            </div>
        </div>
    </div>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="successToast" class="toast align-items-center text-bg-dark border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="successToastBody"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>


    <script>
        async function showUsers() {
            const response = await fetch("users.php");
            const html = await response.text();
            if (html.trim() === "unauthorized") {
                window.location.href = "login.php?reason=timeout";
                return;
            }
            document.getElementById("page-content").innerHTML = html;
            filterUsers();
        }
        function filterUsers() {
            const searchTerm = document.getElementById("search-input").value.toLowerCase();
            const accessLevel = document.getElementById("access-filter").value;
            const showInactive = document.getElementById("inactive-toggle").checked;
            const tbody = document.getElementById("users-table-body");
            const rows = Array.from(tbody.querySelectorAll(".user-row"));

            let visibleCount = 0;
            const activeRows = [];
            const inactiveRows = []

            rows.forEach(row => {
                const matchesSearch = row.textContent.toLowerCase().includes(searchTerm);
                const matchesAccess = accessLevel === "" || row.dataset.accessId === accessLevel;
                const isActive = row.dataset.active == "1";
                const matchesActive = isActive || showInactive;

                const shouldShow = matchesSearch && matchesAccess && matchesActive;
                row.style.display = shouldShow ? "" : "none";
                row.classList.toggle("table-secondary", shouldShow && !isActive);
                if (shouldShow) visibleCount++;
                (isActive ? activeRows : inactiveRows).push(row);
            });
            activeRows.concat(inactiveRows).forEach(row => tbody.appendChild(row));

            const totalUserCountView = showInactive
                ? rows.length
                : rows.filter(row => row.dataset.active == "1").length;
            document.getElementById("user-count").textContent = `${visibleCount} of ${totalUserCountView} users`;
        }

        function openAddModal() {
            document.getElementById("add-user-form").reset();
            document.getElementById("add-error").classList.add("d-none");
            new bootstrap.Modal(document.getElementById("addUserModal")).show();
        }

        async function submitAddUser(form) {
            const formData = new FormData(form);
            const response = await fetch("addUserAction.php", {
                method: "POST",
                body: formData
            });
            const result = await response.text();

            if (result == "success") {
                bootstrap.Modal.getInstance(document.getElementById("addUserModal")).hide();
                showUsers();
                showToast("User added successfully.");
                return;
            }

            if (result == "duplicate_email") {
                const errorBox = document.getElementById("add-error");
                errorBox.textContent = "That email address is already in use by another user.";
                errorBox.classList.remove("d-none");
                return;
            }

            if (result == "invalid_name") {
                const errorBox = document.getElementById("add-error");
                errorBox.textContent = "Please enter a first and last name of up to 50 characters.";
                errorBox.classList.remove("d-none");
                return;
            }

            if (result == "invalid_email") {
                const errorBox = document.getElementById("add-error");
                errorBox.textContent = "Please enter a valid email address of up to 254 characters.";
                errorBox.classList.remove("d-none");
                return;
            }

            if (result == "invalid_password") {
                const errorBox = document.getElementById("add-error");
                errorBox.textContent = "The password must be between 6 and 72 characters.";
                errorBox.classList.remove("d-none");
                return;
            }

            if (result == "invalid_access_level") {
                const errorBox = document.getElementById("add-error");
                errorBox.textContent = "Please choose a valid access level.";
                errorBox.classList.remove("d-none");
                return;
            }

            if (result == "invalid_token") {
                const errorBox = document.getElementById("add-error");
                errorBox.textContent = "Your session has expired. Please reload the page and try again.";
                errorBox.classList.remove("d-none");
                return;
            }

            if (result == "unauthorized") {
                window.location.href = "login.php";
                return;
            }

            const errorBox = document.getElementById("add-error");
            errorBox.textContent = "Something went wrong adding this user. Please try again.";
            errorBox.classList.remove("d-none");
        }

        function openEditModal(button) {
            const row = button.closest("tr");
            document.getElementById("edit-user-id").value = row.cells[0].textContent.trim();
            document.getElementById("edit-first-name").value = row.dataset.firstName;
            document.getElementById("edit-last-name").value = row.dataset.lastName;
            document.getElementById("edit-email").value = row.cells[2].textContent.trim();
            document.getElementById("edit-access-level").value = row.dataset.accessId;
            document.getElementById("edit-is-active").checked = row.dataset.active === "1";

            document.getElementById("edit-error").classList.add("d-none");

            new bootstrap.Modal(document.getElementById("editUserModal")).show();
        }

        async function submitEditUser(form) {
            const formData = new FormData(form);
            const response = await fetch("updateUserAction.php", {
                method: "POST",
                body: formData
            });
            const result = await response.text();

            if (result == "success") {
                bootstrap.Modal.getInstance(document.getElementById("editUserModal")).hide();
                showUsers();
                showToast("User details updated successfully.");
                return;
            }

            if (result == "duplicate_email") {
                const errorBox = document.getElementById("edit-error");
                errorBox.textContent = "The email address is already used by another user.";
                errorBox.classList.remove("d-none");
                return;
            }

            if (result == "invalid_name") {
                const errorBox = document.getElementById("edit-error");
                errorBox.textContent = "Please enter a first and last name of up to 50 characters.";
                errorBox.classList.remove("d-none");
                return;
            }

            if (result == "invalid_email") {
                const errorBox = document.getElementById("edit-error");
                errorBox.textContent = "Please enter a valid email address of up to 254 characters.";
                errorBox.classList.remove("d-none");
                return;
            }

            if (result == "invalid_access_level") {
                const errorBox = document.getElementById("edit-error");
                errorBox.textContent = "Please choose a valid access level.";
                errorBox.classList.remove("d-none");
                return;
            }

            if (result == "invalid_user_id") {
                const errorBox = document.getElementById("edit-error");
                errorBox.textContent = "This user could not be found. Please reload the page and try again.";
                errorBox.classList.remove("d-none");
                return;
            }

            if (result == "cannot_change_own_access") {
                const errorBox = document.getElementById("edit-error");
                errorBox.textContent = "You can't remove your own Admin access or deactivate your own account.";
                errorBox.classList.remove("d-none");
                return;
            }

            if (result == "invalid_token") {
                const errorBox = document.getElementById("edit-error");
                errorBox.textContent = "Your session has expired. Please reload the page and try again.";
                errorBox.classList.remove("d-none");
                return;
            }

            if (result == "unauthorized") {
                window.location.href = "login.php";
                return;
            }

            const errorBox = document.getElementById("edit-error");
            errorBox.textContent = "Something went wrong updating this user. Please try again.";
            errorBox.classList.remove("d-none");
        }

        function showToast(message) {
            document.getElementById("successToastBody").textContent = message;
            const toast = new bootstrap.Toast(document.getElementById("successToast"));
            toast.show();
        }

        function openDeleteModal(button) {
            const row = button.closest("tr");
            const form = document.getElementById("delete-user-form");

            document.getElementById("delete-user-id").value = row.cells[0].textContent.trim();
            document.getElementById("delete-user-name").textContent = row.cells[1].textContent.trim();
            document.getElementById("delete-confirm").value = "";

            document.getElementById("delete-error").classList.add("d-none");

            new bootstrap.Modal(document.getElementById("deleteUserModal")).show();
        }
        document.addEventListener("submit", function (e) {
            if (e.target.id == "add-user-form") {
                e.preventDefault();
                const form = e.target;
                if (!form.checkValidity()) {
                    form.classList.add("was-validated");
                    return;
                }
                submitAddUser(form);
            }
            if (e.target.id == "edit-user-form") {
                e.preventDefault();
                submitEditUser(e.target);
            }
            if (e.target.id == "delete-user-form") {
                e.preventDefault();
                submitDeleteUser(e.target);
            }
        });

        async function submitDeleteUser(form) {
            const typedText = document.getElementById("delete-confirm").value.trim();
            const errorBox = document.getElementById("delete-error");

            if (typedText !== "DELETE") {
                errorBox.textContent = "Please type DELETE exactly as shown to confirm.";
                errorBox.classList.remove("d-none");
                return;
            }

            const formData = new FormData(form);
            const response = await fetch("deleteUserAction.php", {
                method: "POST",
                body: formData
            });
            const result = await response.text();

            if (result == "success") {
                bootstrap.Modal.getInstance(document.getElementById("deleteUserModal")).hide();
                showUsers();
                showToast("User deleted successfully.");
                return;
            }

            if (result == "unauthorized") {
                window.location.href = "login.php?reason=timeout";
                return;
            }

            if (result == "cannot_delete_self") {
                errorBox.textContent = "You can't delete your own admin account. Please ask another administrator to remove your access.";
                errorBox.classList.remove("d-none");
                return;
            }

            if (result == "invalid_token") {
                errorBox.textContent = "Your session has expired. Please reload the page and try again.";
                errorBox.classList.remove("d-none");
                return;
            }

            errorBox.textContent = "Something went wrong deleting this user. Please try again.";
            errorBox.classList.remove("d-none");
        }
    </script>
</body>

</html>