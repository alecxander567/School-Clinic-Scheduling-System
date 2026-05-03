<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../components/Sidebar.php';

requireLogin();

$user     = $auth->getCurrentUser();
$userRole = $user['role'] ?? 'admin';
$userName = $user['name'] ?? 'Admin User';
$currentPage = 'reports';

global $pdo;

$selectedDate  = $_GET['date']  ?? date('Y-m-d');
$selectedYear  = $_GET['year']  ?? date('Y');
$selectedMonth = $_GET['month'] ?? date('m');
$activeTab     = $_GET['tab']   ?? 'daily';

$dailyStmt = $pdo->prepare("
    SELECT
        a.id, a.visit_date, a.start_time, a.end_time,
        a.max_students, a.registered_students,
        COALESCE(s.service_name, 'Unknown Service') AS service_name,
        COALESCE(p.name, 'Unassigned')              AS provider_name,
        COALESCE(ap.status_name, 'Unknown')         AS status_name,
        COUNT(DISTINCT v.id)         AS actual_students_served,
        COUNT(DISTINCT v.student_id) AS unique_students
    FROM appointments a
    LEFT JOIN services            s  ON a.service_id  = s.id
    LEFT JOIN providers           p  ON a.provider_id = p.id
    LEFT JOIN appointment_statuses ap ON a.status_id  = ap.id
    LEFT JOIN visits              v  ON v.appointment_id = a.id
    WHERE DATE(a.visit_date) = :date AND a.visit_date IS NOT NULL
    GROUP BY a.id
    ORDER BY a.start_time
");
$dailyStmt->execute([':date' => $selectedDate]);
$dailyAppointments = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);

$serviceStmt = $pdo->prepare("
    SELECT
        COALESCE(s.service_name, 'Unknown Service') AS service_name,
        COUNT(DISTINCT a.id)         AS appointment_count,
        COUNT(DISTINCT v.student_id) AS students_served
    FROM appointments a
    LEFT JOIN services s ON a.service_id = s.id
    LEFT JOIN visits   v ON v.appointment_id = a.id
    WHERE DATE(a.visit_date) = :date AND a.visit_date IS NOT NULL
    GROUP BY s.id, s.service_name
    ORDER BY appointment_count DESC
");
$serviceStmt->execute([':date' => $selectedDate]);
$serviceBreakdown = $serviceStmt->fetchAll(PDO::FETCH_ASSOC);

$hourlyStmt = $pdo->prepare("
    SELECT HOUR(a.start_time) AS hour, COUNT(*) AS appointment_count
    FROM appointments a
    WHERE DATE(a.visit_date) = :date
      AND a.start_time IS NOT NULL
      AND a.visit_date IS NOT NULL
    GROUP BY HOUR(a.start_time)
    ORDER BY hour
");
$hourlyStmt->execute([':date' => $selectedDate]);
$hourlyDistribution = array_fill_keys(range(8, 17), 0);
foreach ($hourlyStmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $hourlyDistribution[(int)$r['hour']] = (int)$r['appointment_count'];
}

$providerStmt = $pdo->prepare("
    SELECT
        COALESCE(p.name, 'Unassigned') AS provider_name,
        COUNT(DISTINCT a.id)         AS appointments_scheduled,
        COUNT(DISTINCT v.id)         AS appointments_completed,
        COUNT(DISTINCT v.student_id) AS students_served
    FROM appointments a
    LEFT JOIN providers p ON a.provider_id = p.id
    LEFT JOIN visits    v ON v.appointment_id = a.id
    WHERE DATE(a.visit_date) = :date AND a.visit_date IS NOT NULL
    GROUP BY p.id, p.name
    ORDER BY students_served DESC
");
$providerStmt->execute([':date' => $selectedDate]);
$providerPerformance = $providerStmt->fetchAll(PDO::FETCH_ASSOC);

$monthlyStmt = $pdo->prepare("
    SELECT
        DATE(a.visit_date)           AS date,
        COUNT(DISTINCT a.id)         AS total_appointments,
        COUNT(DISTINCT v.id)         AS total_visits,
        COUNT(DISTINCT v.student_id) AS unique_students
    FROM appointments a
    LEFT JOIN visits v ON v.appointment_id = a.id
    WHERE YEAR(a.visit_date) = :year AND MONTH(a.visit_date) = :month
      AND a.visit_date IS NOT NULL
    GROUP BY DATE(a.visit_date)
    ORDER BY date
");
$monthlyStmt->execute([':year' => $selectedYear, ':month' => $selectedMonth]);
$monthlyData = $monthlyStmt->fetchAll(PDO::FETCH_ASSOC);

$monthlyTotalsStmt = $pdo->prepare("
    SELECT
        COUNT(DISTINCT a.id)         AS total_appointments,
        COUNT(DISTINCT v.id)         AS total_visits,
        COUNT(DISTINCT v.student_id) AS unique_students,
        COUNT(DISTINCT p.id)         AS active_providers
    FROM appointments a
    LEFT JOIN visits    v ON v.appointment_id = a.id
    LEFT JOIN providers p ON a.provider_id    = p.id
    WHERE YEAR(a.visit_date) = :year AND MONTH(a.visit_date) = :month
      AND a.visit_date IS NOT NULL
");
$monthlyTotalsStmt->execute([':year' => $selectedYear, ':month' => $selectedMonth]);
$monthlyTotals = $monthlyTotalsStmt->fetch(PDO::FETCH_ASSOC);

$monthlyServiceStmt = $pdo->prepare("
    SELECT
        COALESCE(s.service_name, 'Unknown Service') AS service_name,
        COUNT(DISTINCT a.id)         AS appointment_count,
        COUNT(DISTINCT v.student_id) AS students_served
    FROM appointments a
    LEFT JOIN services s ON a.service_id = s.id
    LEFT JOIN visits   v ON v.appointment_id = a.id
    WHERE YEAR(a.visit_date) = :year AND MONTH(a.visit_date) = :month
      AND a.visit_date IS NOT NULL
    GROUP BY s.id, s.service_name
    ORDER BY appointment_count DESC
    LIMIT 5
");
$monthlyServiceStmt->execute([':year' => $selectedYear, ':month' => $selectedMonth]);
$monthlyServicePopularity = $monthlyServiceStmt->fetchAll(PDO::FETCH_ASSOC);

$totalStudents = array_sum(array_column($dailyAppointments, 'unique_students'));
$completed     = count(array_filter($dailyAppointments, fn($a) => strtolower($a['status_name']) === 'completed'));
$completionRate = count($dailyAppointments) > 0 ? round($completed / count($dailyAppointments) * 100) : 0;

$sidebar = new Sidebar($currentPage, $userRole, $userName);

function statusBadge(string $status): string
{
    $map = [
        'completed' => 'green',
        'confirmed' => 'blue',
        'pending'   => 'amber',
        'cancelled' => 'rose',
    ];
    $cls = $map[strtolower($status)] ?? 'gray';
    return "<span class=\"badge badge-{$cls}\">" . htmlspecialchars(ucfirst($status)) . "</span>";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports — School Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/reports.css">
</head>

<body>

    <!-- Mobile overlay -->
    <div id="mobileOverlay" class="fixed inset-0 bg-black bg-opacity-40 z-30 hidden lg:hidden"></div>

    <!-- Mobile menu button -->
    <button id="mobileMenuButton" class="mobile-menu-btn lg:hidden fixed top-3.5 left-4 z-50 p-2 rounded-lg">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <?php echo $sidebar->render(); ?>

    <div id="mainContent" class="lg:ml-56 transition-all duration-300">

        <!-- Top bar -->
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

        <!-- Page content -->
        <main class="px-5 py-6 sm:px-6">

            <!-- Tab Navigation -->
            <div class="tab-nav fade-up">
                <a href="?tab=daily&date=<?php echo urlencode($selectedDate); ?>"
                    class="tab-btn <?php echo $activeTab === 'daily' ? 'active' : ''; ?>">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Daily Report
                </a>
                <a href="?tab=monthly&year=<?php echo urlencode($selectedYear); ?>&month=<?php echo urlencode($selectedMonth); ?>"
                    class="tab-btn <?php echo $activeTab === 'monthly' ? 'active' : ''; ?>">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Monthly Report
                </a>
            </div>

            <?php if ($activeTab === 'daily'): ?>
                <!-- Filter -->
                <form method="GET" class="filter-card fade-up fade-up-1">
                    <input type="hidden" name="tab" value="daily">
                    <div>
                        <label class="filter-label">Select Date</label>
                        <input type="date" name="date" value="<?php echo htmlspecialchars($selectedDate); ?>"
                            class="filter-input">
                    </div>
                    <button type="submit" class="btn-primary">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Generate
                    </button>
                </form>

                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-card green fade-up fade-up-1">
                        <div class="stat-header">
                            <span class="stat-label">Total Appointments</span>
                            <div class="stat-icon green">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo count($dailyAppointments); ?></div>
                        <div class="stat-sub">for <?php echo date('M j, Y', strtotime($selectedDate)); ?></div>
                    </div>

                    <div class="stat-card blue fade-up fade-up-2">
                        <div class="stat-header">
                            <span class="stat-label">Students Served</span>
                            <div class="stat-icon blue">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo $totalStudents; ?></div>
                        <div class="stat-sub">unique students today</div>
                    </div>

                    <div class="stat-card amber fade-up fade-up-3">
                        <div class="stat-header">
                            <span class="stat-label">Service Types</span>
                            <div class="stat-icon amber">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo count($serviceBreakdown); ?></div>
                        <div class="stat-sub">active today</div>
                    </div>

                    <div class="stat-card purple fade-up fade-up-4">
                        <div class="stat-header">
                            <span class="stat-label">Completion Rate</span>
                            <div class="stat-icon purple">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo $completionRate; ?>%</div>
                        <div class="stat-sub"><?php echo $completed; ?> of <?php echo count($dailyAppointments); ?> completed</div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="charts-grid">
                    <div class="chart-card fade-up fade-up-1">
                        <div class="chart-title">
                            <span class="chart-title-dot"></span>
                            Service Breakdown
                        </div>
                        <div class="chart-container">
                            <canvas id="serviceChart"
                                data-labels='<?php echo json_encode(array_column($serviceBreakdown, 'service_name')); ?>'
                                data-values='<?php echo json_encode(array_map('intval', array_column($serviceBreakdown, 'appointment_count'))); ?>'></canvas>
                        </div>
                    </div>

                    <div class="chart-card fade-up fade-up-2">
                        <div class="chart-title">
                            <span class="chart-title-dot" style="background:#3b82f6;"></span>
                            Hourly Distribution
                        </div>
                        <div class="chart-container">
                            <canvas id="hourlyChart"
                                data-hours='<?php echo json_encode(array_keys($hourlyDistribution)); ?>'
                                data-counts='<?php echo json_encode(array_values($hourlyDistribution)); ?>'></canvas>
                        </div>
                    </div>
                </div>

                <!-- Appointments Table -->
                <div class="table-card fade-up">
                    <div class="table-header">
                        <span class="table-title">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            Appointment Details
                        </span>
                        <span class="table-count"><?php echo count($dailyAppointments); ?> records</span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Service</th>
                                    <th>Provider</th>
                                    <th>Status</th>
                                    <th>Students</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($dailyAppointments)): ?>
                                    <tr class="empty-row">
                                        <td colspan="5">No appointments found for <?php echo date('F j, Y', strtotime($selectedDate)); ?></td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($dailyAppointments as $apt): ?>
                                        <tr>
                                            <td class="mono">
                                                <?php
                                                echo ($apt['start_time'] && $apt['end_time'])
                                                    ? date('g:i A', strtotime($apt['start_time'])) . ' – ' . date('g:i A', strtotime($apt['end_time']))
                                                    : '—';
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($apt['service_name']); ?></td>
                                            <td><?php echo htmlspecialchars($apt['provider_name']); ?></td>
                                            <td><?php echo statusBadge($apt['status_name']); ?></td>
                                            <td class="mono"><?php echo $apt['unique_students']; ?> / <?php echo $apt['max_students'] ?: '∞'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Provider Performance Table -->
                <?php if (!empty($providerPerformance)): ?>
                    <div class="table-card fade-up">
                        <div class="table-header">
                            <span class="table-title">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Provider Performance
                            </span>
                            <span class="table-count"><?php echo count($providerPerformance); ?> providers</span>
                        </div>
                        <div style="overflow-x:auto;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Provider</th>
                                        <th>Scheduled</th>
                                        <th>Completed</th>
                                        <th>Students Served</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($providerPerformance as $p): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($p['provider_name']); ?></td>
                                            <td class="mono"><?php echo $p['appointments_scheduled']; ?></td>
                                            <td class="mono"><?php echo $p['appointments_completed']; ?></td>
                                            <td class="mono"><?php echo $p['students_served']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Filter -->
                <form method="GET" class="filter-card fade-up fade-up-1">
                    <input type="hidden" name="tab" value="monthly">
                    <div>
                        <label class="filter-label">Select Month</label>
                        <input type="month" name="month"
                            value="<?php echo $selectedYear . '-' . str_pad($selectedMonth, 2, '0', STR_PAD_LEFT); ?>"
                            class="filter-input">
                    </div>
                    <button type="submit" class="btn-primary">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Generate
                    </button>
                </form>

                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-card green fade-up fade-up-1">
                        <div class="stat-header">
                            <span class="stat-label">Total Appointments</span>
                            <div class="stat-icon green">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo $monthlyTotals['total_appointments'] ?? 0; ?></div>
                        <div class="stat-sub">this month</div>
                    </div>

                    <div class="stat-card blue fade-up fade-up-2">
                        <div class="stat-header">
                            <span class="stat-label">Total Visits</span>
                            <div class="stat-icon blue">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo $monthlyTotals['total_visits'] ?? 0; ?></div>
                        <div class="stat-sub">completed visits</div>
                    </div>

                    <div class="stat-card amber fade-up fade-up-3">
                        <div class="stat-header">
                            <span class="stat-label">Unique Students</span>
                            <div class="stat-icon amber">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo $monthlyTotals['unique_students'] ?? 0; ?></div>
                        <div class="stat-sub">served this month</div>
                    </div>

                    <div class="stat-card purple fade-up fade-up-4">
                        <div class="stat-header">
                            <span class="stat-label">Active Providers</span>
                            <div class="stat-icon purple">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo $monthlyTotals['active_providers'] ?? 0; ?></div>
                        <div class="stat-sub">with appointments</div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="charts-grid">
                    <div class="chart-card fade-up fade-up-1">
                        <div class="chart-title">
                            <span class="chart-title-dot"></span>
                            Daily Trends — <?php echo date('F Y', strtotime("$selectedYear-$selectedMonth-01")); ?>
                        </div>
                        <div class="chart-container">
                            <canvas id="dailyTrendChart"
                                data-dates='<?php echo json_encode(array_column($monthlyData, 'date')); ?>'
                                data-appointments='<?php echo json_encode(array_map('intval', array_column($monthlyData, 'total_appointments'))); ?>'
                                data-visits='<?php echo json_encode(array_map('intval', array_column($monthlyData, 'total_visits'))); ?>'></canvas>
                        </div>
                    </div>

                    <div class="chart-card fade-up fade-up-2">
                        <div class="chart-title">
                            <span class="chart-title-dot" style="background:#f59e0b;"></span>
                            Top Services
                        </div>
                        <div class="chart-container">
                            <canvas id="monthlyServiceChart"
                                data-names='<?php echo json_encode(array_column($monthlyServicePopularity, 'service_name')); ?>'
                                data-counts='<?php echo json_encode(array_map('intval', array_column($monthlyServicePopularity, 'appointment_count'))); ?>'></canvas>
                        </div>
                    </div>
                </div>

                <!-- Monthly Breakdown Table -->
                <div class="table-card fade-up">
                    <div class="table-header">
                        <span class="table-title">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            Daily Breakdown
                        </span>
                        <span class="table-count"><?php echo count($monthlyData); ?> days</span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Appointments</th>
                                    <th>Visits</th>
                                    <th>Unique Students</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($monthlyData)): ?>
                                    <tr class="empty-row">
                                        <td colspan="4">No data for <?php echo date('F Y', strtotime("$selectedYear-$selectedMonth-01")); ?></td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($monthlyData as $row): ?>
                                        <tr>
                                            <td><?php echo date('F j, Y', strtotime($row['date'])); ?></td>
                                            <td class="mono"><?php echo $row['total_appointments']; ?></td>
                                            <td class="mono"><?php echo $row['total_visits']; ?></td>
                                            <td class="mono"><?php echo $row['unique_students']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php endif; ?>

        </main>
    </div>

    <script src="../js/sidebar.js"></script>
    <script src="../js/reports.js"></script>
</body>

</html>