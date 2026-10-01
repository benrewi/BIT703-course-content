<?php
require_once "sessions.php";
require_admin_session(true);
require_once "config/dbconnect.php";
require_once "csrf.php";
require_once "User.php";
require_once "userActions.php";

$users = getUsers($conn);
$totalUsers = count($users);
?>

<h1 class="display-4 fw-semibold">User Management</h1>

<div class="mt-4">
    <button class="btn btn-dark mb-3" onclick="openAddModal()">Add User</button>

    <div class="card shadow">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <input type="text" id="search-input" class="form-control w-25" placeholder="Search..."
                oninput="filterUsers()">
            <select id="access-filter" class="form-select w-auto" onchange="filterUsers()">
                <option value="">All access levels</option>
                <option value="1">Admin</option>
                <option value="2">Manager</option>
                <option value="3">Salesperson</option>
                <option value="4">Customer</option>
            </select>
        </div>
        <table class="table mb-0">
            <thead>
                <tr class="table-secondary">
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Access Level</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="users-table-body">
                <?php foreach ($users as $user): ?>
                    <tr class="user-row" data-access="<?= htmlspecialchars($user->accessLevelName) ?>"
                        data-access-id="<?= (int) $user->accessLevelId ?>"
                        data-first-name="<?= htmlspecialchars($user->firstName) ?>"
                        data-last-name="<?= htmlspecialchars($user->lastName) ?>"
                        data-active="<?= (int) $user->isActive ?>">
                        <td><?= htmlspecialchars($user->userId) ?></td>
                        <td><?= htmlspecialchars($user->firstName . ' ' . $user->lastName) ?></td>
                        <td><?= htmlspecialchars($user->email) ?></td>
                        <td><?= htmlspecialchars($user->accessLevelName) ?></td>
                        <td>
                            <div class="d-flex gap-3 justify-content-end me-2">
                                <button class="btn btn-sm btn-outline-dark" onclick="openEditModal(this)"><i
                                        class="fa fa-pencil"></i></button>
                                <button class="btn btn-sm btn-outline-danger" onclick="openDeleteModal(this)"><i
                                        class="fa fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center border-0">
            <span id="user-count"><?= $totalUsers ?> of <?= $totalUsers ?> users</span>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="inactive-toggle" onchange="filterUsers()">
                <label class="form-check-label" for="inactive-toggle">Show inactive users</label>
            </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" id="editUserModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="edit-user-form">
                    <input type="hidden" name="csrf_token"
                        value="<?= htmlspecialchars(csrf_panel_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="edit-error" class="alert alert-danger d-none"></div>
                        <input type="hidden" id="edit-user-id" name="user_id">
                        <label for="edit-first-name" class="ps-1">First Name:</label>
                        <div class="mb-4">
                            <input class="w-100 form-control" type="text" id="edit-first-name" name="first_name"
                                required>
                            <div class="invalid-feedback">Please enter a first name</div>
                        </div>
                        <label for="edit-last-name" class="ps-1">Last Name:</label>
                        <div class="mb-4">
                            <input class="w-100 form-control" type="text" id="edit-last-name" name="last_name" required>
                            <div class="invalid-feedback">Please enter a last name</div>
                        </div>
                        <label for="edit-email" class="ps-1">Email:</label>
                        <div class="mb-4">
                            <input class="w-100 form-control" type="email" id="edit-email" name="email" required>
                            <div class="invalid-feedback">Please enter a valid email address</div>
                        </div>
                        <label for="edit-access-level" class="ps-1">Access Level:</label>
                        <div class="mb-4">
                            <select class="w-100 form-select" id="edit-access-level" name="access_level_id" required>
                                <option value=""></option>
                                <option value="1">Admin</option>
                                <option value="2">Manager</option>
                                <option value="3">Salesperson</option>
                                <option value="4">Customer</option>
                            </select>
                            <div class="invalid-feedback">Please select an access level</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit-is-active" class="ps-1">Active</label>
                            <input type="checkbox" class="form-check-input ms-5" id="edit-is-active" name="is_active">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-dark me-auto"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal" tabindex="-1" id="deleteUserModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="delete-user-form">
                <input type="hidden" name="csrf_token"
                    value="<?= htmlspecialchars(csrf_panel_token(), ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="delete-error" class="alert alert-danger d-none"></div>
                    <p>Are you sure you want to delete <strong id="delete-user-name"></strong>?</p>
                    <p>Deleting this user will permanently remove all access. Please type DELETE in the
                        field, then select Permanently Delete User to confirm.</p>

                    <input type="hidden" id="delete-user-id" name="user_id">

                    <label for="delete-confirm" class="ps-1">Type DELETE to confirm:</label>
                    <div class="mb-4">
                        <input class="w-100 form-control" type="text" id="delete-confirm" name="confirm_text" required
                            maxlength="6" placeholder="DELETE" autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark me-auto" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Permanently Delete User</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal" tabindex="-1" id="addUserModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="add-user-form">
                <input type="hidden" name="csrf_token"
                    value="<?= htmlspecialchars(csrf_panel_token(), ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="add-error" class="alert alert-danger d-none"></div>
                    <label for="add-first-name" class="ps-1">First Name:</label>
                    <div class="mb-4">
                        <input class="w-100 form-control" type="text" id="add-first-name" name="first_name" required>
                    </div>
                    <label for="add-last-name" class="ps-1">Last Name:</label>
                    <div class="mb-4">
                        <input class="w-100 form-control" type="text" id="add-last-name" name="last_name" required>
                    </div>
                    <label for="add-email" class="ps-1">Email:</label>
                    <div class="mb-4">
                        <input class="w-100 form-control" type="email" id="add-email" name="email" required>
                    </div>
                    <label for="add-password" class="ps-1">Password:</label>
                    <div class="mb-4">
                        <input class="w-100 form-control" type="password" id="add-password" name="password" minlength="6" 
                        maxlength="72" required>
                    </div>
                    <label for="add-access-level" class="ps-1">Access Level:</label>
                    <div class="mb-4">
                        <select class="w-100 form-select" id="add-access-level" name="access_level_id" required>
                            <option value=""></option>
                            <option value="1">Admin</option>
                            <option value="2">Manager</option>
                            <option value="3">Salesperson</option>
                            <option value="4">Customer</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark me-auto" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>