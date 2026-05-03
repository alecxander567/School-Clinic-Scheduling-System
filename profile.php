<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/components/Sidebar.php';
require_once __DIR__ . '/controllers/ProfileController.php';
require_once __DIR__ . '/components/Alert.php';

requireLogin();

$user        = $auth->getCurrentUser();
$userRole    = $user['role'] ?? 'admin';
$userName    = $user['name'] ?? 'Admin User';
$currentPage = 'profile.php';
$userId      = $_SESSION['user_id'] ?? 1;

$profileController = new ProfileController($auth, $pdo);

$message     = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_profile') {
            $result = $profileController->updateProfile(
                $userId,
                $_POST['name']  ?? '',
                $_POST['email'] ?? ''
            );
            $message     = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            if ($result['success']) {
                $_SESSION['user_name'] = $_POST['name'];
            }
        } elseif ($_POST['action'] === 'update_password') {
            $result = $profileController->updatePassword(
                $userId,
                $_POST['current_password'] ?? '',
                $_POST['new_password']     ?? '',
                $_POST['confirm_password'] ?? ''
            );
            $message     = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }
    }
}

$profile  = $profileController->getProfile($userId);
$activity = $profileController->getUserActivity($userId);

$sidebar  = new Sidebar($currentPage, $userRole, $userName);
$initials = strtoupper(substr($profile['name'] ?? $userName, 0, 2));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile — School Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/dashboard.css">
    <link rel="stylesheet" href="./css/profile.css">
</head>

<body class="bg-gray-50">

    <!-- Mobile overlay -->
    <div id="mobileOverlay" class="fixed inset-0 bg-black bg-opacity-40 z-30 hidden lg:hidden"></div>

    <!-- Mobile menu button -->
    <button id="mobileMenuButton"
        class="mobile-menu-btn lg:hidden fixed top-3.5 left-4 z-50 p-2 rounded-lg shadow-md">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <!-- Sidebar -->
    <?php echo $sidebar->render(); ?>

    <!-- Main content -->
    <div id="mainContent" class="lg:ml-56 transition-all duration-300">

        <header class="topbar sticky top-0 z-20">
            <div class="pl-14 pr-5 lg:px-6 py-3 flex items-center justify-between">
                <div>
                    <h1 class="topbar-title text-lg font-semibold">Reports &amp; Analytics</h1>
                    <p class="topbar-subtitle text-xs">Track clinic performance metrics</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="topbar-date hidden sm:block text-xs px-3 py-1.5 rounded-lg"
                        style="background:#f4f8f5; color:#2c5a48;">
                        <?php echo date('D, M j Y'); ?>
                    </span>
                    <!-- Notification bell -->
                    <button class="topbar-notif-btn relative w-8 h-8 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="topbar-notif-dot absolute top-1 right-1 w-1.5 h-1.5 rounded-full"></span>
                    </button>
                    <!-- Avatar + name -->
                    <div class="hidden sm:flex items-center gap-2">
                        <div class="topbar-avatar w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold">
                            <?php echo strtoupper(substr($userName, 0, 2)); ?>
                        </div>
                        <div class="hidden md:block text-right">
                            <p class="topbar-user-name text-xs font-medium"><?php echo htmlspecialchars($userName); ?></p>
                            <p class="topbar-user-email text-xs"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Alerts -->
        <?php if ($message): ?>
            <div class="px-5 pt-5 sm:px-6 max-w-4xl mx-auto" style="margin-left: auto; margin-right: auto; padding-left: 1.25rem; padding-right: 1.25rem; padding-top: 1.25rem;">
                <?php if ($messageType === 'success'): ?>
                    <?php echo Alert::success($message); ?>
                <?php else: ?>
                    <?php echo Alert::error($message); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Page content -->
        <main class="px-5 py-6 sm:px-6">
            <div class="max-w-4xl mx-auto">

                <!-- ── Profile Header Card ──────────────────────── -->
                <div class="profile-header-card rounded-xl p-6 mb-6 fade-in d1">
                    <div class="flex items-center gap-5">
                        <div class="profile-avatar w-16 h-16 rounded-full flex items-center justify-center text-white text-xl font-semibold flex-shrink-0">
                            <?php echo $initials; ?>
                        </div>
                        <div class="min-w-0">
                            <h2 class="user-name text-lg font-semibold leading-tight">
                                <?php echo htmlspecialchars($profile['name'] ?? $userName); ?>
                            </h2>
                            <p class="user-email text-sm mt-0.5">
                                <?php echo htmlspecialchars($profile['email'] ?? ''); ?>
                            </p>
                            <div class="flex items-center gap-3 mt-2 flex-wrap">
                                <span class="role-badge">
                                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <?php echo ucfirst($userRole); ?>
                                </span>
                                <span class="member-since" style="font-size:11px;">
                                    Member since <?php echo date('F j, Y', strtotime($profile['created_at'] ?? 'now')); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Main grid ────────────────────────────────── -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                    <!-- Left column: forms -->
                    <div class="lg:col-span-2 space-y-5">

                        <!-- Edit Profile -->
                        <div class="section-card rounded-xl p-6 fade-in d2">
                            <h3 class="section-card-title mb-5">
                                <span class="title-icon">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </span>
                                Edit Profile
                            </h3>
                            <form method="POST" action="" class="space-y-4">
                                <input type="hidden" name="action" value="update_profile">
                                <div>
                                    <label class="form-label" for="name">Full Name</label>
                                    <input type="text" id="name" name="name" required
                                        class="form-input"
                                        value="<?php echo htmlspecialchars($profile['name'] ?? ''); ?>"
                                        placeholder="Your full name">
                                </div>
                                <div>
                                    <label class="form-label" for="email">Email Address</label>
                                    <input type="email" id="email" name="email" required
                                        class="form-input"
                                        value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>"
                                        placeholder="your@email.com">
                                </div>
                                <div class="pt-1">
                                    <button type="submit" class="btn-primary">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Change Password -->
                        <div class="section-card rounded-xl p-6 fade-in d3">
                            <h3 class="section-card-title mb-5">
                                <span class="title-icon">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </span>
                                Change Password
                            </h3>
                            <form method="POST" action="" class="space-y-4" onsubmit="return validatePasswordForm()">
                                <input type="hidden" name="action" value="update_password">
                                <div>
                                    <label class="form-label" for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" required
                                        class="form-input"
                                        placeholder="Enter current password">
                                </div>
                                <hr class="form-divider">
                                <div>
                                    <label class="form-label" for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" required
                                        class="form-input"
                                        placeholder="Enter new password">
                                    <!-- Strength meter -->
                                    <div class="strength-bar-container">
                                        <div class="strength-bar-segment"></div>
                                        <div class="strength-bar-segment"></div>
                                        <div class="strength-bar-segment"></div>
                                        <div class="strength-bar-segment"></div>
                                    </div>
                                    <p class="strength-label"></p>
                                </div>
                                <div>
                                    <label class="form-label" for="confirm_password">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required
                                        class="form-input"
                                        placeholder="Re-enter new password">
                                    <p class="form-hint">Must be at least 6 characters.</p>
                                </div>
                                <div class="pt-1">
                                    <button type="submit" class="btn-primary">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>

                    <!-- Right column: stats + activity -->
                    <div class="lg:col-span-1 space-y-5">

                        <!-- Account Statistics -->
                        <div class="section-card rounded-xl p-5 fade-in d2">
                            <h3 class="section-card-title mb-4">
                                <span class="title-icon">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </span>
                                Account Statistics
                            </h3>
                            <div>
                                <div class="stat-item">
                                    <span class="stat-item-label">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Account Age
                                    </span>
                                    <span class="stat-item-value"><?php echo $activity['account_age_days']; ?> days</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-item-label">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        Appointments
                                    </span>
                                    <span class="stat-item-value"><?php echo $activity['appointments_created']; ?></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-item-label">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        Role
                                    </span>
                                    <span class="stat-item-value"><?php echo ucfirst($userRole); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="section-card rounded-xl p-5 fade-in d3">
                            <h3 class="section-card-title mb-4">
                                <span class="title-icon">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </span>
                                Recent Activity
                            </h3>
                            <?php if (!empty($activity['recent_activity'])): ?>
                                <div>
                                    <?php foreach ($activity['recent_activity'] as $item): ?>
                                        <div class="activity-item">
                                            <div class="activity-dot"></div>
                                            <div>
                                                <p class="activity-text">
                                                    Appointment for <strong><?php echo htmlspecialchars($item['service_name'] ?? 'Unknown'); ?></strong>
                                                </p>
                                                <p class="activity-date"><?php echo date('M j, Y', strtotime($item['created_at'])); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="activity-empty">No recent activity.</p>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="./js/sidebar.js"></script>
    <script src="./js/profile.js"></script>
</body>

</html>