<div
    class="sidebar-panel position-fixed top-0 start-0 h-100 d-flex flex-column align-items-center text-center text-white bg-black">
    <div class="mt-5 pt-4">
        <h1 class="fs-1 fw-bold mb-0 mt-5 ls-wide">AOTEAROA</h1>
        <p class="fs-5 ls-wider">ADVENTURE GEAR</p>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>

        <nav class="d-grid gap-3 w-100 px-4 mt-5">
            <a href="index.php" class="btn btn-outline-light py-2">Dashboard</a>
            <a href="#users" onClick="showUsers()" class="btn btn-outline-light py-2">Users</a>
        </nav>

         <a href="logout.php" class="btn btn-outline-light py-2 w-75 mt-auto mb-5">Log Out</a>
    <?php else: ?>
        <p class="fs-5 mt-5">Administrator Login</p>
    <?php endif; ?>

</div>