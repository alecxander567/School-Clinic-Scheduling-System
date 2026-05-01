<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AppointmentController.php';
require_once __DIR__ . '/../components/Sidebar.php';
require_once __DIR__ . '/../components/AppointmentModal.php';

session_start();

// Guard: make sure $pdo is actually a PDO object before proceeding.
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die('<p style="font-family:sans-serif;color:#a32d2d;padding:2rem;">
         <strong>Database connection error:</strong> <code>$pdo</code> is not a valid PDO instance.
         Check your <code>config/database.php</code> and make sure it ends with
         <code>return $pdo;</code> (or sets a <code>$pdo</code> variable in global scope).
         </p>');
}

$controller = new AppointmentController($pdo);

$selectedDate  = isset($_GET['date'])  ? $_GET['date']  : date('Y-m-d');
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Validate date format (basic safety)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate))  $selectedDate  = date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}$/',        $selectedMonth)) $selectedMonth = date('Y-m');

[$year, $month] = explode('-', $selectedMonth);
$year        = (int)$year;
$month       = (int)$month;
$firstDay    = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = (int)date('t', $firstDay);

// Appointments for the selected date (right panel list)
$appointments = $controller->getAppointmentsByDate($selectedDate);

// Count per day in month (dot indicators on calendar)
$monthAppointments = [];
for ($d = 1; $d <= $daysInMonth; $d++) {
    $ds       = sprintf('%04d-%02d-%02d', $year, $month, $d);
    $dayAppts = $controller->getAppointmentsByDate($ds);
    if (!empty($dayAppts)) {
        $monthAppointments[$ds] = count($dayAppts);
    }
}

$sidebar = new Sidebar(
    'appointments/calendar.php',
    $_SESSION['user_role'] ?? 'admin',
    $_SESSION['user_name'] ?? 'Admin'
);

$prevMonth = date('Y-m', mktime(0, 0, 0, $month - 1, 1, $year));
$nextMonth = date('Y-m', mktime(0, 0, 0, $month + 1, 1, $year));

function statusBadge(string $status): array
{
    $map = [
        'Scheduled' => ['bg' => '#e6f1fb', 'color' => '#185fa5', 'border' => '#b5d4f4'],
        'Confirmed' => ['bg' => '#e1f5ee', 'color' => '#0f6e56', 'border' => '#9fe1cb'],
        'Completed' => ['bg' => '#f1efe8', 'color' => '#5f5e5a', 'border' => '#d3d1c7'],
        'Cancelled' => ['bg' => '#fcebeb', 'color' => '#a32d2d', 'border' => '#f5b5b5'],
        'Pending'   => ['bg' => '#faeeda', 'color' => '#ba7517', 'border' => '#fac775'],
    ];
    return $map[$status] ?? $map['Scheduled'];
}

$totalToday = count($appointments);
$scheduled  = count(array_filter($appointments, fn($a) => $a['status_name'] === 'Scheduled'));
$confirmed  = count(array_filter($appointments, fn($a) => $a['status_name'] === 'Confirmed'));
$completed  = count(array_filter($appointments, fn($a) => $a['status_name'] === 'Completed'));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Appointment Calendar – Clinic Scheduler</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Shared dashboard styles -->
    <link rel="stylesheet" href="../css/dashboard.css" />
    <link rel="stylesheet" href="../css/calendar.css" />
</head>

<body>

    <?php echo $sidebar->render(); ?>

    <!-- Mobile sidebar overlay -->
    <div id="overlay"
        class="fixed inset-0 bg-black/40 z-30 hidden lg:hidden"
        onclick="closeSidebar()">
    </div>

    <!-- ── Page wrapper ────────────────────────────────────────── -->
    <div class="lg:ml-56 min-h-screen flex flex-col">

        <!-- ── Top bar ──────────────────────────────────────────── -->
        <header class="topbar sticky top-0 z-20 flex items-center justify-between px-5 py-3">
            <div class="flex items-center gap-3">
                <!-- Mobile hamburger -->
                <button class="mobile-menu-btn lg:hidden w-8 h-8 rounded-lg flex items-center justify-center"
                    onclick="openSidebar()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div>
                    <h1 class="text-sm font-semibold" style="color:var(--color-text-main);">Appointment Calendar</h1>
                    <p class="text-xs" style="color:var(--color-text-muted);">View and manage appointments by date</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Today shortcut -->
                <a href="?date=<?= date('Y-m-d') ?>&month=<?= date('Y-m') ?>"
                    class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium"
                    style="background:var(--tint-teal);color:var(--color-primary-dark);border:1px solid var(--border-teal);">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Today
                </a>

                <!-- Notifications -->
                <button class="w-8 h-8 rounded-lg flex items-center justify-center relative"
                    style="border:1px solid var(--color-border);background:var(--color-surface);">
                    <svg class="w-4 h-4" style="color:var(--color-text-mid);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 rounded-full"
                        style="background:var(--color-primary);"></span>
                </button>

                <!-- Avatar -->
                <div class="topbar-avatar w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold">
                    <?= strtoupper(substr($_SESSION['user_name'] ?? 'Ad', 0, 2)) ?>
                </div>
            </div>
        </header>

        <!-- ── Main content ──────────────────────────────────────── -->
        <main class="flex-1 p-5 space-y-5">

            <!-- ── Stat strip ─────────────────────────────────────── -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 fade-in d1">

                <div class="stat-card p-4 flex items-center gap-3">
                    <div class="w-9 h-9 flex items-center justify-center flex-shrink-0"
                        style="background:var(--tint-teal);border-radius:10px;">
                        <svg class="w-4 h-4" style="color:var(--color-primary);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold" style="color:var(--color-text-main);"><?= $totalToday ?></p>
                        <p class="text-xs" style="color:var(--color-text-muted);">Total Appointments</p>
                    </div>
                </div>

                <div class="stat-card p-4 flex items-center gap-3">
                    <div class="w-9 h-9 flex items-center justify-center flex-shrink-0"
                        style="background:var(--tint-blue);border-radius:10px;">
                        <svg class="w-4 h-4" style="color:var(--accent-blue);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold" style="color:var(--color-text-main);"><?= $scheduled ?></p>
                        <p class="text-xs" style="color:var(--color-text-muted);">Scheduled</p>
                    </div>
                </div>

                <div class="stat-card p-4 flex items-center gap-3">
                    <div class="w-9 h-9 flex items-center justify-center flex-shrink-0"
                        style="background:var(--tint-teal);border-radius:10px;">
                        <svg class="w-4 h-4" style="color:var(--color-primary-dark);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold" style="color:var(--color-text-main);"><?= $confirmed ?></p>
                        <p class="text-xs" style="color:var(--color-text-muted);">Confirmed</p>
                    </div>
                </div>

                <div class="stat-card p-4 flex items-center gap-3">
                    <div class="w-9 h-9 flex items-center justify-center flex-shrink-0"
                        style="background:var(--tint-gray);border-radius:10px;">
                        <svg class="w-4 h-4" style="color:var(--accent-gray);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold" style="color:var(--color-text-main);"><?= $completed ?></p>
                        <p class="text-xs" style="color:var(--color-text-muted);">Completed</p>
                    </div>
                </div>
            </div>

            <!-- ── Calendar + Appointments ────────────────────────── -->
            <div class="grid grid-cols-1 xl:grid-cols-5 gap-5">

                <!-- Calendar panel -->
                <div class="xl:col-span-2 fade-in d2">
                    <div class="panel-card p-5">

                        <!-- Month header -->
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-sm font-semibold" style="color:var(--color-text-main);">
                                <?= date('F Y', $firstDay) ?>
                            </h2>
                            <div class="flex gap-1.5">
                                <a href="?date=<?= htmlspecialchars($selectedDate) ?>&month=<?= $prevMonth ?>"
                                    class="month-nav-btn" title="Previous month">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </a>
                                <a href="?date=<?= htmlspecialchars($selectedDate) ?>&month=<?= $nextMonth ?>"
                                    class="month-nav-btn" title="Next month">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <!-- Day-of-week labels -->
                        <div class="cal-grid mb-2">
                            <?php foreach (['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] as $dow): ?>
                                <div class="text-center"
                                    style="font-size:10px;font-weight:600;color:var(--color-text-muted);padding:4px 0;">
                                    <?= $dow ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Day cells -->
                        <div class="cal-grid">
                            <?php
                            $startDow = (int)date('w', $firstDay);
                            // Leading empty cells
                            for ($e = 0; $e < $startDow; $e++):
                            ?>
                                <div class="cal-day cal-day--empty"></div>
                            <?php endfor; ?>

                            <?php for ($d = 1; $d <= $daysInMonth; $d++):
                                $ds       = sprintf('%04d-%02d-%02d', $year, $month, $d);
                                $isToday  = ($ds === date('Y-m-d'));
                                $isSelect = ($ds === $selectedDate);
                                $hasAppts = isset($monthAppointments[$ds]);
                                $count    = $monthAppointments[$ds] ?? 0;

                                $cls  = 'cal-day';
                                if ($isToday)  $cls .= ' cal-day--today';
                                if ($isSelect) $cls .= ' cal-day--selected';
                                if ($hasAppts) $cls .= ' cal-day--has-appts';
                            ?>
                                <a href="?date=<?= $ds ?>&month=<?= $selectedMonth ?>"
                                    class="<?= $cls ?>">
                                    <span class="cal-day-num"><?= $d ?></span>
                                    <span class="cal-dot"></span>
                                </a>
                            <?php endfor; ?>
                        </div>

                        <!-- Legend -->
                        <div class="flex flex-wrap gap-3 mt-4 pt-4"
                            style="border-top:1px solid var(--color-border);">
                            <div class="flex items-center gap-1.5">
                                <div class="w-3 h-3 rounded-sm" style="background:var(--color-primary);"></div>
                                <span style="font-size:10px;color:var(--color-text-muted);">Selected</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <div class="w-3 h-3 rounded-sm"
                                    style="background:var(--tint-teal);border:1.5px solid var(--color-primary);"></div>
                                <span style="font-size:10px;color:var(--color-text-muted);">Today</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <div class="w-2 h-2 rounded-full" style="background:var(--color-primary);"></div>
                                <span style="font-size:10px;color:var(--color-text-muted);">Has appointments</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appointments list panel -->
                <div class="xl:col-span-3 fade-in d3">
                    <div class="panel-card p-5 h-full">

                        <!-- Panel header -->
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-sm font-semibold" style="color:var(--color-text-main);">
                                    Appointments for
                                    <span style="color:var(--color-primary);">
                                        <?= date('F j, Y', strtotime($selectedDate)) ?>
                                    </span>
                                </h2>
                                <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">
                                    <?= $totalToday ?> appointment<?= $totalToday !== 1 ? 's' : '' ?> found
                                </p>
                            </div>
                            <button onclick="openAppointmentModal()"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium"
                                style="background:var(--color-primary);color:#fff;">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                New
                            </button>
                        </div>

                        <!-- List -->
                        <div class="appts-scroll space-y-2">
                            <?php if (empty($appointments)): ?>
                                <div class="flex flex-col items-center justify-center py-12 gap-3">
                                    <div class="w-14 h-14 rounded-full flex items-center justify-center"
                                        style="background:var(--tint-teal);">
                                        <svg class="w-7 h-7" style="color:var(--color-primary);" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium" style="color:var(--color-text-main);">No appointments</p>
                                    <p class="text-xs text-center" style="color:var(--color-text-soft);">
                                        Nothing scheduled for this date.<br />Pick another day or create a new appointment.
                                    </p>
                                </div>

                            <?php else: ?>
                                <?php foreach ($appointments as $i => $appt):
                                    $badge = statusBadge($appt['status_name']);
                                    $start = date('g:i A', strtotime($appt['start_time']));
                                    $end   = date('g:i A', strtotime($appt['end_time']));
                                ?>
                                    <div class="appt-row"
                                        style="animation:fadeIn 0.3s ease forwards;
                                    animation-delay:<?= $i * 0.05 ?>s;
                                    opacity:0;">
                                        <div class="flex items-start justify-between gap-3">

                                            <!-- Left: time + info -->
                                            <div class="flex items-start gap-3 min-w-0">
                                                <!-- Time block -->
                                                <div class="flex-shrink-0 text-right" style="min-width:62px;">
                                                    <p class="text-xs font-semibold" style="color:var(--color-text-main);"><?= $start ?></p>
                                                    <p class="text-xs" style="color:var(--color-text-muted);"><?= $end ?></p>
                                                </div>

                                                <!-- Timeline dot -->
                                                <div class="flex-shrink-0 flex flex-col items-center" style="padding-top:3px;">
                                                    <div class="w-2 h-2 rounded-full"
                                                        style="background:var(--color-primary);"></div>
                                                    <div style="width:1.5px;flex:1;background:var(--color-border);
                                                    min-height:28px;margin:3px 0;"></div>
                                                </div>

                                                <!-- Details -->
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-xs font-semibold truncate"
                                                        style="color:var(--color-text-main);">
                                                        <?= htmlspecialchars($appt['service_name']) ?>
                                                    </p>

                                                    <?php if (!empty($appt['provider_name'])): ?>
                                                        <p class="text-xs truncate mt-0.5" style="color:var(--color-text-muted);">
                                                            <svg class="w-3 h-3 inline -mt-0.5 mr-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                            </svg>
                                                            <?= htmlspecialchars($appt['provider_name']) ?>
                                                        </p>
                                                    <?php endif; ?>

                                                    <p class="text-xs mt-0.5" style="color:var(--color-text-soft);">
                                                        <svg class="w-3 h-3 inline -mt-0.5 mr-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        Max <?= (int)$appt['max_students'] ?> students
                                                    </p>

                                                    <?php if (!empty($appt['notes'])): ?>
                                                        <p class="text-xs mt-1 truncate"
                                                            style="color:var(--color-text-soft);font-style:italic;">
                                                            "<?= htmlspecialchars($appt['notes']) ?>"
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Right: badge + actions -->
                                            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                                    style="background:<?= $badge['bg'] ?>;
                                                 color:<?= $badge['color'] ?>;
                                                 border:1px solid <?= $badge['border'] ?>;">
                                                    <?= htmlspecialchars($appt['status_name']) ?>
                                                </span>
                                            </div>

                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ── Month overview strip ───────────────────────────── -->
            <div class="panel-card p-5 fade-in d4">
                <h2 class="text-sm font-semibold mb-4" style="color:var(--color-text-main);">
                    Month Overview —
                    <span style="color:var(--color-primary);"><?= date('F Y', $firstDay) ?></span>
                </h2>

                <?php if (empty($monthAppointments)): ?>
                    <p class="text-xs" style="color:var(--color-text-soft);">No appointments scheduled this month.</p>

                <?php else: ?>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($monthAppointments as $ds => $count):
                            $isToday  = ($ds === date('Y-m-d'));
                            $isSelect = ($ds === $selectedDate);
                            $cls = 'month-chip';
                            if ($isSelect) $cls .= ' month-chip--selected';
                            elseif ($isToday) $cls .= ' month-chip--today';
                        ?>
                            <a href="?date=<?= $ds ?>&month=<?= $selectedMonth ?>"
                                class="<?= $cls ?>">
                                <span style="font-size:10px;font-weight:600;"><?= date('M j', strtotime($ds)) ?></span>
                                <span style="font-size:13px;font-weight:700;margin-top:2px;"><?= $count ?></span>
                                <span style="font-size:9px;opacity:0.7;">appt<?= $count !== 1 ? 's' : '' ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <!-- ── Delete confirmation modal ──────────────────────────── -->
    <div id="deleteModal" class="modal-backdrop">
        <div class="modal-box">
            <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4"
                style="background:var(--tint-red);">
                <svg class="w-6 h-6" style="color:var(--accent-red);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-center mb-1" style="color:var(--color-text-main);">
                Delete Appointment
            </h3>
            <p class="text-xs text-center mb-5" style="color:var(--color-text-muted);">
                Are you sure you want to delete this appointment?<br />This action cannot be undone.
            </p>
            <div class="flex gap-2">
                <button onclick="closeDeleteModal()"
                    class="flex-1 py-2 rounded-lg text-xs font-medium"
                    style="background:var(--color-bg);color:var(--color-text-main);border:1px solid var(--color-border);">
                    Cancel
                </button>
                <a id="deleteConfirmBtn"
                    href="#"
                    data-base-url="../appointments/delete.php"
                    class="flex-1 py-2 rounded-lg text-xs font-medium text-center"
                    style="background:var(--accent-red);color:#fff;">
                    Delete
                </a>
            </div>
        </div>
    </div>

    <?php AppointmentModal::render($controller, $_SESSION['user_id'] ?? 1); ?>

    <!-- Calendar JS (external) -->
    <script src="../js/calendar.js"></script>
</body>

</html>