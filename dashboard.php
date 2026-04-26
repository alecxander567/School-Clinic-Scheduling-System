<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/components/Sidebar.php';

requireLogin();

$user     = $auth->getCurrentUser();
$userRole = $user['role'] ?? 'admin';
$userName = $user['name'] ?? 'Admin User';
$currentPage = 'dashboard.php';

$sidebar = new Sidebar($currentPage, $userRole, $userName);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — School Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/dashboard.css">
</head>

<body>

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

        <!-- Top bar -->
        <header class="topbar sticky top-0 z-20">
            <div class="pl-14 pr-5 lg:px-6 py-3 flex items-center justify-between">
                <div>
                    <h1 class="topbar-title text-lg font-semibold">Dashboard</h1>
                    <p class="topbar-subtitle text-xs">Welcome back, <?php echo htmlspecialchars($userName); ?></p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="topbar-date hidden sm:block text-xs px-3 py-1.5 rounded-lg">
                        <?php echo date('D, M j Y'); ?>
                    </span>
                    <!-- Notification bell -->
                    <button class="topbar-notif-btn relative w-8 h-8 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="topbar-notif-dot absolute top-1 right-1 w-1.5 h-1.5 rounded-full"></span>
                    </button>
                    <!-- Avatar -->
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

        <!-- Page content -->
        <main class="px-5 py-6 sm:px-6">

            <!-- Welcome banner -->
            <div class="welcome-banner fade-in d1 rounded-xl p-5 mb-6 flex items-center justify-between">
                <div>
                    <h2 class="welcome-banner-title text-base font-semibold">
                        Good <?php
                                $h = (int)date('H');
                                echo $h < 12 ? 'morning' : ($h < 17 ? 'afternoon' : 'evening');
                                ?>, <?php echo htmlspecialchars($userName); ?>
                    </h2>
                    <p class="welcome-banner-subtitle text-xs mt-0.5">Everything is running smoothly today.</p>
                </div>
                <div class="welcome-banner-pill flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    System online
                </div>
            </div>

            <!-- Stat cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

                <div class="stat-card fade-in d1 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="stat-icon stat-icon--teal w-8 h-8 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="stat-badge stat-badge--teal text-xs px-2 py-0.5 rounded-full">+4%</span>
                    </div>
                    <p class="stat-card-number text-2xl font-semibold">0</p>
                    <p class="stat-card-label text-xs mt-0.5">Total Appointments</p>
                </div>

                <div class="stat-card fade-in d2 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="stat-icon stat-icon--blue w-8 h-8 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <span class="stat-badge stat-badge--blue text-xs px-2 py-0.5 rounded-full">+12</span>
                    </div>
                    <p class="stat-card-number text-2xl font-semibold">0</p>
                    <p class="stat-card-label text-xs mt-0.5">Total Students</p>
                </div>

                <div class="stat-card fade-in d3 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="stat-icon stat-icon--amber w-8 h-8 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                            </svg>
                        </div>
                        <span class="stat-badge stat-badge--amber text-xs px-2 py-0.5 rounded-full">—</span>
                    </div>
                    <p class="stat-card-number text-2xl font-semibold">0</p>
                    <p class="stat-card-label text-xs mt-0.5">Pending Consultations</p>
                </div>

                <div class="stat-card fade-in d4 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="stat-icon stat-icon--red w-8 h-8 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <span class="stat-badge stat-badge--red text-xs px-2 py-0.5 rounded-full">Alert</span>
                    </div>
                    <p class="stat-card-number text-2xl font-semibold">0</p>
                    <p class="stat-card-label text-xs mt-0.5">Low Stock Items</p>
                </div>

            </div>

            <!-- Lower row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 fade-in d5">

                <!-- Today's appointments -->
                <div class="panel-card rounded-xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="panel-card-title text-sm font-semibold">Today's Appointments</h3>
                        <a href="appointments/list.php" class="panel-card-link text-xs font-medium">View all →</a>
                    </div>
                    <div class="flex flex-col gap-2">
                        <p class="panel-card-empty text-xs text-center py-6">No appointments scheduled for today.</p>
                    </div>
                </div>

                <!-- Quick actions -->
                <div class="panel-card rounded-xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="panel-card-title text-sm font-semibold">Quick Actions</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="appointments/new.php" class="qa-tile qa-tile--teal flex items-center gap-2 p-3 rounded-lg">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="text-xs font-medium">New Appointment</span>
                        </a>
                        <a href="students/add.php" class="qa-tile qa-tile--blue flex items-center gap-2 p-3 rounded-lg">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            <span class="text-xs font-medium">Add Student</span>
                        </a>
                        <a href="medical/visits.php" class="qa-tile qa-tile--amber flex items-center gap-2 p-3 rounded-lg">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-xs font-medium">Visit History</span>
                        </a>
                        <a href="reports/daily.php" class="qa-tile qa-tile--gray flex items-center gap-2 p-3 rounded-lg">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span class="text-xs font-medium">Daily Report</span>
                        </a>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script src="./js/sidebar.js"></script>
</body>

</html>