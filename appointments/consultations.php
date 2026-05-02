<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../components/Sidebar.php';
require_once __DIR__ . '/../controllers/ConsultationsController.php';

requireLogin();

$user          = $auth->getCurrentUser();
$userRole      = $user['role'] ?? 'admin';
$userName      = $user['name'] ?? 'Admin User';
$currentPage   = 'Consultations';
$currentUserId = $_SESSION['user_id'] ?? 1;

global $pdo;

// Initialize controller
$consultationsController = new ConsultationsController($pdo);

$filterType = isset($_GET['type']) ? $_GET['type'] : 'all';
$filterDateFrom = isset($_GET['date_from']) && !empty($_GET['date_from']) ? $_GET['date_from'] : null;
$filterDateTo = isset($_GET['date_to']) && !empty($_GET['date_to']) ? $_GET['date_to'] : null;
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// If date range is not set, we'll pass null to show ALL appointments
// Get consultations using the controller
$consultations = $consultationsController->getConsultations($filterDateFrom, $filterDateTo, $filterType, $searchQuery);
$statistics = $consultationsController->getStatistics($filterDateFrom, $filterDateTo);


// Initialize sidebar
$sidebar = new Sidebar($currentPage, $userRole, $userName);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultations History — Clinic Scheduler</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/consultations.css">

    <style>
        @keyframes csSlideDown {
            from {
                opacity: 0;
                transform: translateY(-40px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .cs-modal-animate {
            animation: csSlideDown 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
    </style>
</head>

<body>

    <div id="mobileOverlay" class="fixed inset-0 bg-black bg-opacity-40 z-30 hidden lg:hidden"></div>

    <button id="mobileMenuButton" class="mobile-menu-btn lg:hidden fixed top-3.5 left-4 z-50 p-2 rounded-lg shadow-md">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <?php echo $sidebar->render(); ?>

    <div id="mainContent" class="lg:ml-56 transition-all duration-300">

        <!-- ── Topbar ──────────────────────────────────────────── -->
        <header class="topbar sticky top-0 z-20">
            <div class="pl-14 pr-5 lg:px-6 py-3 flex items-center justify-between">
                <div>
                    <h1 class="topbar-title text-lg font-semibold">Consultations History</h1>
                    <p class="topbar-subtitle text-xs">View past consultations and appointments</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="topbar-date hidden sm:block text-xs px-3 py-1.5 rounded-lg">
                        <?php echo date('D, M j Y'); ?>
                    </span>
                    <div class="flex items-center gap-2">
                        <div class="topbar-avatar w-8 h-8 rounded-full flex items-center justify-center text-xs">
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

        <!-- ── Page body ──────────────────────────────────────── -->
        <main class="px-5 py-6 sm:px-6">

            <!-- Stat cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

                <div class="stat-card fade-in d1">
                    <div class="stat-icon stat-icon--teal">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-number"><?php echo number_format($statistics['total_consultations'] ?? 0); ?></p>
                        <p class="stat-label">Total Consultations</p>
                    </div>
                </div>

                <div class="stat-card fade-in d2">
                    <div class="stat-icon stat-icon--blue">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-number"><?php echo number_format($statistics['total_providers'] ?? 0); ?></p>
                        <p class="stat-label">Providers / Doctors</p>
                    </div>
                </div>

                <div class="stat-card fade-in d3">
                    <div class="stat-icon stat-icon--green">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-number"><?php echo number_format($statistics['total_students_served'] ?? 0); ?></p>
                        <p class="stat-label">Students Served</p>
                    </div>
                </div>

                <div class="stat-card fade-in d4">
                    <div class="stat-icon stat-icon--purple">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-number"><?php echo round($statistics['avg_students_per_session'] ?? 0, 1); ?></p>
                        <p class="stat-label">Avg. per Session</p>
                    </div>
                </div>

            </div>

            <!-- Filter panel -->
            <div class="filter-panel mb-6 fade-in d5">
                <form id="filterForm" method="GET" class="flex flex-wrap gap-3 items-end">
                    <input type="hidden" id="hiddenType" name="type" value="<?php echo htmlspecialchars($filterType); ?>">

                    <div class="flex-1 min-w-[140px]">
                        <label class="filter-label">Date From</label>
                        <input type="date" name="date_from" value="<?php echo htmlspecialchars($filterDateFrom); ?>" class="filter-input">
                    </div>

                    <div class="flex-1 min-w-[140px]">
                        <label class="filter-label">Date To</label>
                        <input type="date" name="date_to" value="<?php echo htmlspecialchars($filterDateTo); ?>" class="filter-input">
                    </div>

                    <div class="flex-1 min-w-[200px]">
                        <label class="filter-label">Search</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>"
                            placeholder="Provider, service, or student…" class="filter-input">
                    </div>

                    <!-- Replace the filter buttons section with this -->
                    <div class="flex flex-wrap gap-2 items-center">
                        <button type="submit" class="filter-btn filter-btn--apply">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                            </svg>
                            Apply
                        </button>

                        <?php
                        // Build base query parameters
                        $baseParams = [];
                        if ($filterDateFrom) $baseParams['date_from'] = $filterDateFrom;
                        if ($filterDateTo) $baseParams['date_to'] = $filterDateTo;
                        if ($searchQuery) $baseParams['search'] = $searchQuery;
                        ?>

                        <a href="?type=all&<?php echo http_build_query($baseParams); ?>"
                            class="filter-btn <?php echo $filterType === 'all' ? 'is-active' : ''; ?>">
                            All
                        </a>

                        <a href="?type=doctor&<?php echo http_build_query($baseParams); ?>"
                            class="filter-btn <?php echo $filterType === 'doctor' ? 'is-active' : ''; ?>">
                            Doctors
                        </a>

                        <a href="?type=dentist&<?php echo http_build_query($baseParams); ?>"
                            class="filter-btn <?php echo $filterType === 'dentist' ? 'is-active' : ''; ?>">
                            Dentists
                        </a>

                        <!-- Clear All button - removes all filters -->
                        <a href="?type=all" class="filter-btn filter-btn--clear">Clear All</a>

                        <!-- Show All button - shows all appointments (clears date filter only) -->
                        <a href="?type=<?php echo $filterType; ?>&search=<?php echo urlencode($searchQuery); ?>"
                            class="filter-btn filter-btn--clear">
                            Show All Dates
                        </a>
                    </div>
                </form>
            </div>

            <!-- Consultations list -->
            <?php if (empty($consultations)): ?>
                <div class="empty-state">
                    <svg class="w-14 h-14 mx-auto" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="empty-state-title">No consultations found</p>
                    <p class="empty-state-sub">Try adjusting your filters or date range</p>
                </div>

            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($consultations as $c):
                        $studentList  = !empty($c['student_list']) ? explode('||', $c['student_list']) : [];
                        $providerType = ConsultationsController::getProviderType($c);
                        $shown        = array_slice($studentList, 0, 5);
                        $remaining    = count($studentList) - 5;
                    ?>
                        <div class="consult-card is-<?php echo $providerType; ?>">
                            <div class="consult-card-body">

                                <!-- Header row -->
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap mb-1">
                                            <h3 class="consult-title"><?php echo htmlspecialchars($c['service_name'] ?? 'Consultation'); ?></h3>
                                            <span class="provider-badge provider-badge--<?php echo $providerType; ?>">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="<?php echo $providerType === 'doctor'
                                                                ? 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
                                                                : 'M12 4.5a7.5 7.5 0 00-7.5 7.5c0 3.5 2.5 6.5 6 7.2V21h3v-1.8c3.5-.7 6-3.7 6-7.2 0-4.15-3.35-7.5-7.5-7.5z'; ?>" />
                                                </svg>
                                                <?php echo ucfirst($providerType); ?>
                                            </span>
                                        </div>
                                        <div class="consult-meta">
                                            <span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <?php echo date('F d, Y', strtotime($c['visit_date'])); ?>
                                            </span>
                                            <span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <?php echo date('g:i A', strtotime($c['start_time'])); ?> &ndash; <?php echo date('g:i A', strtotime($c['end_time'])); ?>
                                            </span>
                                            <span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                <?php echo htmlspecialchars($c['provider_name'] ?? 'N/A'); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Students served -->
                                    <div class="students-served flex-shrink-0">
                                        <div class="students-served-count"><?php echo $c['total_students'] ?? 0; ?><span style="font-size:1rem;color:#9ab5aa;">/<?php echo $c['max_students'] ?? 0; ?></span></div>
                                        <div class="students-served-label">Students Served</div>
                                    </div>
                                </div>

                                <!-- Student tags -->
                                <?php if (!empty($shown)): ?>
                                    <div style="margin-top:0.875rem;padding-top:0.75rem;border-top:1px solid #f0f7f4;">
                                        <p style="font-size:0.75rem;font-weight:500;color:#5a8a79;margin-bottom:6px;">Registered Students</p>
                                        <div class="flex flex-wrap gap-1.5">
                                            <?php foreach ($shown as $s): if (empty($s)) continue; ?>
                                                <span class="student-tag"><?php echo htmlspecialchars($s); ?></span>
                                            <?php endforeach; ?>
                                            <?php if ($remaining > 0): ?>
                                                <span class="student-tag student-tag--more">+<?php echo $remaining; ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Notes -->
                                <?php if (!empty($c['appointment_notes'])): ?>
                                    <div class="consult-notes">
                                        <span style="font-weight:500;">Notes:</span>
                                        <?php echo htmlspecialchars($c['appointment_notes']); ?>
                                    </div>
                                <?php endif; ?>

                            </div>

                            <!-- Card footer -->
                            <div class="consult-footer">
                                <button onclick="viewDetails(<?php echo $c['id']; ?>)" class="action-btn action-btn--view">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    View Details
                                </button>
                                <?php if ($userRole === 'admin' || $userRole === 'nurse'): ?>
                                    <button onclick="generateReport(<?php echo $c['id']; ?>, '<?php echo htmlspecialchars($c['service_name']); ?>')"
                                        class="action-btn action-btn--report">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Generate Report
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Summary bar -->
                <div class="summary-bar mt-5">
                    <p>Showing <strong><?php echo count($consultations); ?></strong> consultations</p>
                    <p>Total students served: <strong><?php echo number_format($statistics['total_students_served'] ?? 0); ?></strong></p>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <!-- Details Modal -->
    <div id="detailsModal" class="modal-backdrop">
        <div class="modal">
            <div class="modal-header">
                <h3 id="modalTitle" class="modal-title">Consultation Details</h3>
                <button onclick="closeModal()" class="modal-close" aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="modalBody" class="modal-body"></div>
        </div>
    </div>

    <script src="../js/sidebar.js"></script>
    <script src="../js/consultation.js"></script>
</body>

</html>