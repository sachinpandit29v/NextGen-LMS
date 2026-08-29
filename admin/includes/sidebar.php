<?php
// Determine current page for active link highlighting
if (!isset($current_page)) {
    $current_page = basename($_SERVER['PHP_SELF']);
}

$pages = [
    'admin-panel.php' => ['label' => 'Dashboard', 'icon' => 'fa-chart-line'],
    'user-management.php' => ['label' => 'User Management', 'icon' => 'fa-users'],
    'contact-management.php' => ['label' => 'Contact Management', 'icon' => 'fa-envelope-open-text'],
    'course-management.php' => ['label' => 'Course Management', 'icon' => 'fa-book-open']
];

$admin_name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
if ($admin_name === '') {
    $admin_name = 'Administrator';
}

$initials_source = trim(($user['first_name'] ?? 'A') . ($user['last_name'] ?? 'D'));
$initials = strtoupper(substr(preg_replace('/\s+/', '', $initials_source), 0, 2));
?>
<div class="d-flex admin-layout">
    <aside class="admin-sidebar d-none d-lg-flex flex-column">
        <div class="sidebar-brand">
            <h3>NextGen LMS</h3>
            <p>Admin Control Center</p>
        </div>
        <div class="sidebar-nav flex-grow-1">
            <?php foreach ($pages as $page => $meta): ?>
                <div class="nav-item-custom">
                    <a href="<?php echo $page; ?>" class="nav-link-custom <?php echo $current_page === $page ? 'active' : ''; ?>">
                        <i class="fas <?php echo $meta['icon']; ?>"></i>
                        <span><?php echo $meta['label']; ?></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-3">
                <div class="admin-avatar">
                    <span><?php echo htmlspecialchars($initials); ?></span>
                </div>
                <div class="text-white small">
                    <div class="fw-semibold"><?php echo htmlspecialchars($admin_name); ?></div>
                    <div class="text-white-50" style="font-size: 0.7rem;">Administrator</div>
                </div>
            </div>
        </div>
    </aside>

    <div class="main-content-panel">
        <nav class="top-navbar d-flex justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3 min-w-0">
                <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="min-w-0">
                    <h1 class="page-title mb-0">
                        <?php echo htmlspecialchars($pages[$current_page]['label'] ?? 'Admin Panel'); ?>
                    </h1>
                    <div class="welcome-text text-truncate">Welcome, <?php echo htmlspecialchars($user['first_name'] ?? 'Admin'); ?></div>
                </div>
            </div>
            <form method="post" action="../user/logout.php" class="m-0">
                <button type="submit" class="logout-nav-btn" aria-label="Logout">
                    <i class="fas fa-right-from-bracket"></i>
                    <span>Logout</span>
                </button>
            </form>
        </nav>

        <div class="offcanvas offcanvas-start offcanvas-sidebar" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
            <div class="offcanvas-header border-bottom border-white border-opacity-10">
                <div>
                    <h5 class="offcanvas-title text-white fw-bold mb-0" id="mobileSidebarLabel">NextGen LMS</h5>
                    <p class="text-white-50 small mb-0">Admin Control Center</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0 d-flex flex-column">
                <div class="sidebar-nav flex-grow-1 p-3">
                    <?php foreach ($pages as $page => $meta): ?>
                        <div class="nav-item-custom">
                            <a href="<?php echo $page; ?>" class="nav-link-custom <?php echo $current_page === $page ? 'active' : ''; ?>" data-bs-dismiss="offcanvas">
                                <i class="fas <?php echo $meta['icon']; ?>"></i>
                                <span><?php echo $meta['label']; ?></span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="sidebar-footer">
                    <div class="d-flex align-items-center gap-3">
                        <div class="admin-avatar">
                            <span><?php echo htmlspecialchars($initials); ?></span>
                        </div>
                        <div class="text-white small">
                            <div class="fw-semibold"><?php echo htmlspecialchars($admin_name); ?></div>
                            <div class="text-white-50" style="font-size: 0.7rem;">Administrator</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-content p-3 p-md-4 p-lg-5">
