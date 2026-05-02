<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../components/Sidebar.php';
require_once __DIR__ . '/../controllers/ScheduleController.php';

requireLogin();

$user          = $auth->getCurrentUser();
$userRole      = $user['role'] ?? 'admin';
$userName      = $user['name'] ?? 'Admin User';
$currentPage   = 'Schedule';
$currentUserId = $_SESSION['user_id'] ?? 1;

global $pdo;
$scheduleController = new ScheduleController($pdo);

$selectedAppointmentId = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : null;
$selectedDate          = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

$qrResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_qr'])) {
    $appointmentId = (int)$_POST['appointment_id'];
    $qrResult = $scheduleController->generateQRCode($appointmentId);
}

$appointments       = $scheduleController->getAllAppointments();
$registeredStudents = [];
$selectedAppointment = null;

if ($selectedAppointmentId) {
    $registeredStudents  = $scheduleController->getRegisteredStudents($selectedAppointmentId);
    $selectedAppointment = $scheduleController->getAppointmentById($selectedAppointmentId);
}

$statistics = $scheduleController->getStatistics();
$sidebar    = new Sidebar($currentPage, $userRole, $userName);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Management — Clinic Scheduler</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/schedule.css">
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
                    <h1 class="topbar-title text-lg font-semibold">Schedule Management</h1>
                    <p class="topbar-subtitle text-xs">Manage appointments and view registered students</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="topbar-date hidden sm:block text-xs px-3 py-1.5 rounded-lg">
                        <?php echo date('D, M j Y'); ?>
                    </span>
                    <div class="flex items-center gap-2">
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

        <!-- ── Page body ──────────────────────────────────────── -->
        <main class="px-5 py-6 sm:px-6">

            <!-- Stat cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

                <div class="stat-card fade-in d1 flex items-center gap-3">
                    <div class="stat-card-icon stat-card-icon--teal">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-card-number text-2xl font-semibold"><?php echo $statistics['total_appointments'] ?? 0; ?></p>
                        <p class="stat-card-label text-xs">Total Appointments</p>
                    </div>
                </div>

                <div class="stat-card fade-in d2 flex items-center gap-3">
                    <div class="stat-card-icon stat-card-icon--blue">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-card-number text-2xl font-semibold"><?php echo $statistics['upcoming_appointments'] ?? 0; ?></p>
                        <p class="stat-card-label text-xs">Upcoming</p>
                    </div>
                </div>

                <div class="stat-card fade-in d3 flex items-center gap-3">
                    <div class="stat-card-icon stat-card-icon--red">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-card-number text-2xl font-semibold"><?php echo $statistics['fully_booked'] ?? 0; ?></p>
                        <p class="stat-card-label text-xs">Fully Booked</p>
                    </div>
                </div>

                <div class="stat-card fade-in d4 flex items-center gap-3">
                    <div class="stat-card-icon stat-card-icon--green">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-card-number text-2xl font-semibold"><?php echo $statistics['total_registered_students'] ?? 0; ?></p>
                        <p class="stat-card-label text-xs">Total Registered</p>
                    </div>
                </div>

            </div>

            <!-- Flash messages -->
            <?php if ($qrResult && isset($qrResult['url'])): ?>
                <div class="alert alert--success mb-5 fade-in">
                    <div class="flex items-center gap-2 flex-wrap min-w-0">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>QR code generated! Share this link:</span>
                        <code><?php echo htmlspecialchars($qrResult['url']); ?></code>
                    </div>
                    <button class="alert-close" aria-label="Close">×</button>
                </div>
            <?php elseif ($qrResult && isset($qrResult['error'])): ?>
                <div class="alert alert--error mb-5 fade-in">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span><?php echo htmlspecialchars($qrResult['error']); ?></span>
                    </div>
                    <button class="alert-close" aria-label="Close">×</button>
                </div>
            <?php endif; ?>

            <!-- ── Two-column layout ──────────────────────────────── -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 fade-in d5">

                <!-- LEFT: Appointment list -->
                <div class="lg:col-span-1">
                    <div class="panel sticky top-24">
                        <div class="panel-header">
                            <div>
                                <p class="panel-header-title">Appointments</p>
                                <p class="panel-header-sub">Click to view registered students</p>
                            </div>
                        </div>

                        <div class="appt-list">
                            <?php if (count($appointments) > 0): ?>
                                <?php foreach ($appointments as $appt): ?>
                                    <?php
                                    $s = $appt['appointment_status'];
                                    $badgeClass = match ($s) {
                                        'Available'   => 'badge--available',
                                        'Full'        => 'badge--full',
                                        'In Progress' => 'badge--progress',
                                        default       => 'badge--completed',
                                    };
                                    $filled = $appt['registered_students'] ?? 0;
                                    $max    = $appt['max_students'] ?? 1;
                                    $isFull = $filled >= $max;
                                    ?>
                                    <a href="?appointment_id=<?php echo $appt['id']; ?>&date=<?php echo $appt['visit_date']; ?>"
                                        class="appt-item <?php echo ($selectedAppointmentId == $appt['id']) ? 'is-active' : ''; ?>">
                                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                                            <div style="min-width:0;">
                                                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:3px;">
                                                    <span class="appt-item-service"><?php echo htmlspecialchars($appt['service_name']); ?></span>
                                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo $s; ?></span>
                                                </div>
                                                <div class="appt-item-meta">
                                                    <div><?php echo date('F d, Y', strtotime($appt['visit_date'])); ?></div>
                                                    <div><?php echo date('g:i A', strtotime($appt['start_time'])); ?> &ndash; <?php echo date('g:i A', strtotime($appt['end_time'])); ?></div>
                                                    <?php if ($appt['provider_name']): ?>
                                                        <div><?php echo htmlspecialchars($appt['provider_name']); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="appt-item-count <?php echo $isFull ? 'appt-item-count--full' : 'appt-item-count--ok'; ?>">
                                                    <?php echo $filled; ?>/<?php echo $max; ?>
                                                </div>
                                                <div style="font-size:0.6875rem;color:#9ab5aa;text-align:right;">slots</div>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="empty-state-title">No appointments found</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Registered students -->
                <div class="lg:col-span-2">
                    <div class="panel">
                        <div class="panel-header">
                            <div style="min-width:0;">
                                <p class="panel-header-title">
                                    Registered Students
                                    <?php if ($selectedAppointment): ?>
                                        <span style="font-weight:400;color:#7aaa96;font-size:0.8125rem;">
                                            &mdash; <?php echo htmlspecialchars($selectedAppointment['service_name']); ?>
                                            &middot; <?php echo date('M d, Y', strtotime($selectedAppointment['visit_date'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </p>
                                <p class="panel-header-sub">Priority numbers are assigned by registration order</p>
                            </div>

                            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                                <?php if ($selectedAppointmentId && (!$selectedAppointment || !$selectedAppointment['qr_code_token'])): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $selectedAppointmentId; ?>">
                                        <button type="submit" name="generate_qr" class="btn btn--primary">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 3.5V16M3 3h6v6H3V3zm12 0h6v6h-6V3zM3 15h6v6H3v-6z" />
                                            </svg>
                                            Generate QR
                                        </button>
                                    </form>
                                <?php elseif ($selectedAppointment && $selectedAppointment['qr_code_token']): ?>
                                    <button onclick="copyToClipboard('<?php echo htmlspecialchars($selectedAppointment['qr_code_url']); ?>')"
                                        class="btn btn--secondary">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        Copy QR Link
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!$selectedAppointmentId): ?>
                            <div class="empty-state" style="padding:4rem 1.5rem;">
                                <svg class="w-14 h-14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="empty-state-title">No appointment selected</p>
                                <p class="empty-state-sub">Click an appointment from the left panel to view its students</p>
                            </div>

                        <?php elseif (count($registeredStudents) === 0): ?>
                            <div class="empty-state" style="padding:4rem 1.5rem;">
                                <svg class="w-14 h-14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <p class="empty-state-title">No students registered yet</p>
                                <p class="empty-state-sub">Share the QR code to let students register</p>
                            </div>

                        <?php else: ?>
                            <!-- Search bar -->
                            <div style="padding:0.75rem 1.125rem;border-bottom:1px solid #f0f7f4;background:#f9fcfa;">
                                <div style="position:relative;max-width:320px;">
                                    <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ab5aa;"
                                        class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <input id="studentSearch" type="text" placeholder="Search students…"
                                        style="width:100%;padding:0.4375rem 0.75rem 0.4375rem 2.25rem;
                           border:1px solid #ddeee7;border-radius:8px;font-size:0.8125rem;
                           color:#1a2e25;background:#fff;outline:none;
                           transition:border-color 0.15s,box-shadow 0.15s;"
                                        onfocus="this.style.borderColor='#2d8a6e';this.style.boxShadow='0 0 0 3px rgba(45,138,110,0.12)'"
                                        onblur="this.style.borderColor='#ddeee7';this.style.boxShadow='none'">
                                </div>
                            </div>

                            <div style="overflow-x:auto;">
                                <table class="students-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Student</th>
                                            <th>Course &amp; Year</th>
                                            <th>Contact</th>
                                            <th>Registered On</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($registeredStudents as $student): ?>
                                            <tr>
                                                <td>
                                                    <div class="priority-chip"><?php echo $student['priority_number'] ?? '—'; ?></div>
                                                </td>
                                                <td>
                                                    <div style="font-weight:500;">
                                                        <?php echo htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?: 'N/A'); ?>
                                                    </div>
                                                    <div class="sub"><?php echo htmlspecialchars($student['student_number'] ?? 'N/A'); ?></div>
                                                </td>
                                                <td>
                                                    <div><?php echo htmlspecialchars($student['course'] ?? 'N/A'); ?></div>
                                                    <div class="sub">Year <?php echo htmlspecialchars($student['year_level'] ?? 'N/A'); ?></div>
                                                </td>
                                                <td>
                                                    <div><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></div>
                                                    <?php if (!empty($student['contact_number'])): ?>
                                                        <div class="sub"><?php echo htmlspecialchars($student['contact_number']); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    echo isset($student['registration_date']) && !empty($student['registration_date'])
                                                        ? date('M d, Y · g:i A', strtotime($student['registration_date']))
                                                        : 'N/A';
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Summary bar -->
                            <?php
                            $filled = count($registeredStudents);
                            $max    = (int)($selectedAppointment['max_students'] ?? 0);
                            $pct    = $max > 0 ? min(100, round(($filled / $max) * 100)) : 0;
                            ?>
                            <div class="table-summary">
                                <p class="table-summary-text">
                                    <strong><?php echo $filled; ?></strong> of <?php echo $max; ?> slots filled
                                </p>
                                <div class="capacity-bar-wrap">
                                    <div class="capacity-bar">
                                        <div class="capacity-bar-fill <?php echo $pct >= 100 ? 'capacity-bar-fill--full' : ''; ?>"
                                            data-pct="<?php echo $pct; ?>" style="width:0%"></div>
                                    </div>
                                    <span class="capacity-label"><?php echo $pct; ?>%</span>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </main>
    </div>

    <script src="../js/sidebar.js"></script>
    <script src="../js/schedule.js"></script>
</body>

</html>