<?php
// Initialize session safely if it has not already started.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/../config/app.php';

if (!function_exists('esc')) {
    function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('normalizeRole')) {
    function normalizeRole(string $role): string
    {
        $normalized = strtolower(trim($role));
        return $normalized === '' ? 'user' : $normalized;
    }
}

if (!function_exists('getCurrentPage')) {
    function getCurrentPage(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($uri, PHP_URL_PATH);
        return strtolower(trim(basename($path)));
    }
}

if (!function_exists('shouldShowGuestSignup')) {
    function shouldShowGuestSignup(): bool
    {
        return getCurrentPage() === 'courses.php';
    }
}

if (!function_exists('getActiveNavbarItem')) {
    function getActiveNavbarItem(array $items): ?string
    {
        foreach ($items as $item) {
            if (!empty($item['show']) && isActiveLink($item['url'])) {
                return strtolower(trim($item['label']));
            }
        }
        return null;
    }
}

if (!function_exists('isActiveLink')) {
    function isActiveLink(string $url): bool
    {
        $currentPage = getCurrentPage();
        $linkPage = strtolower(trim(basename(parse_url($url, PHP_URL_PATH))));

        if ($currentPage === $linkPage) {
            return true;
        }

        // Check for home anchors while on the home page
        if ($currentPage === 'home.php' && $linkPage === 'home.php') {
            // Extract anchor from both current URL and link
            $currentUri = $_SERVER['REQUEST_URI'] ?? '';
            $currentAnchor = parse_url($currentUri, PHP_URL_FRAGMENT) ?: '';
            $linkAnchor = parse_url($url, PHP_URL_FRAGMENT) ?: '';
            
            // If the link has an anchor, compare anchors; otherwise just match the page
            if (!empty($linkAnchor)) {
                return $currentAnchor === $linkAnchor;
            }
            return !empty($currentAnchor) === false; // Home link (no anchor) is active if no anchor in URL
        }
        
        return false;
    }
}

$loggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userName = '';
$userRole = 'user';

// Validate session by checking if user exists in database
if ($loggedIn) {
    // Include database config if not already included
    if (!function_exists('getUserById')) {
        require_once __DIR__ . '/../config/database.php';
    }
    
    $userData = getUserById($_SESSION['user_id']);
    if ($userData) {
        $userName = esc($userData['first_name'] . ' ' . $userData['last_name']);
        $userRole = normalizeRole($userData['role']);
        $loggedIn = true;
    } else {
        // Invalid session, clear it
        session_unset();
        session_destroy();
        $loggedIn = false;
    }
}

$displayName = $userName !== '' ? $userName : 'User';

$normalLinks = [
    ['label' => 'Courses', 'url' => APP_USER_PATH . '/courses.php', 'show' => $userRole !== 'admin'],
    ['label' => 'About', 'url' => APP_USER_PATH . '/about.php', 'show' => true],
    ['label' => 'Contact', 'url' => APP_USER_PATH . '/contact.php', 'show' => true],
];

$guestLinks = [
    ['label' => 'Home', 'url' => APP_USER_PATH . '/home.php#homeAlt', 'show' => true],
    ['label' => 'Courses', 'url' => APP_USER_PATH . '/courses.php', 'show' => true],
    ['label' => 'About', 'url' => APP_USER_PATH . '/home.php#aboutAlt', 'show' => true],
    ['label' => 'Contact', 'url' => APP_USER_PATH . '/home.php#contactAlt', 'show' => true],
];

$navItems = $loggedIn
    ? array_merge(
        [['label' => 'Dashboard', 'url' => APP_USER_PATH . '/dashboard.php', 'show' => $userRole !== 'admin']],
        $normalLinks,
        [['label' => 'Admin Panel', 'url' => APP_ADMIN_PATH . '/admin-panel.php', 'show' => $userRole === 'admin']]
    )
    : $guestLinks;

// Determine which navbar item is currently active
$activeNavbarItem = getActiveNavbarItem($navItems);

// Show Sign Up button only when "Courses" is the active navbar item and user is not logged in
$showGuestSignup = !$loggedIn && $activeNavbarItem === 'courses';
?>
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo esc(APP_USER_PATH . '/home.php'); ?>">NextGen</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#altNavbar" aria-controls="altNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="altNavbar">
            <?php if (!isset($hide_nav_links) || !$hide_nav_links): ?>
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-1">
                    <?php foreach ($navItems as $item): ?>
                        <?php if (!empty($item['show'])): ?>
                            <?php $active = isActiveLink($item['url']); ?>
                            <li class="nav-item">
                                <a
                                    class="nav-link<?php echo $active ? ' active' : ''; ?>"
                                    href="<?php echo esc($item['url']); ?>"
                                    <?php echo $active ? 'aria-current="page"' : ''; ?>
                                >
                                    <?php echo esc($item['label']); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="d-flex gap-2 align-items-center <?php echo (isset($hide_nav_links) && $hide_nav_links) ? 'ms-auto' : ''; ?>">
                <?php if ($loggedIn): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-accent dropdown-toggle d-flex align-items-center" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true">
                            <i class="fas fa-user-circle fa-lg me-2"></i>
                            <span class="d-none d-md-inline"><?php echo esc($displayName); ?></span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="profileDropdown">
                            <li>
                                <a class="dropdown-item" href="<?php echo esc(APP_USER_PATH . '/profile.php'); ?>">
                                    <i class="fas fa-user me-2"></i><?php echo esc('Profile'); ?>
                                </a>
                            </li>
                            <?php if ($userRole === 'admin'): ?>
                                <li>
                                    <a class="dropdown-item" href="<?php echo esc(APP_ADMIN_PATH . '/admin-panel.php'); ?>">
                                        <i class="fas fa-cog me-2"></i><?php echo esc('Admin Panel'); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="post" action="<?php echo esc(APP_LOGOUT_ACTION); ?>" class="m-0">
                                    <button type="submit" class="dropdown-item d-flex align-items-center" aria-label="Logout">
                                        <i class="fas fa-sign-out-alt me-2"></i><?php echo esc('Logout'); ?>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo esc(APP_USER_PATH . '/login.php'); ?>" class="btn btn-outline-accent">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
